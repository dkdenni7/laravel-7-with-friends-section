<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Interfaces\ChatInterface;
use App\Models\Message;
use App\User;
use App\Models\BlockedUser;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

class ChatsController extends Controller implements ChatInterface
{
    use CommonTrait;

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     *
     *  @SWG\Get(
     *   path="/getInbox",
     *   summary="Get inbox information",
     *   consumes={"multipart/form-data"},
     *   produces={"application/json"},
     *   tags={"Chat"},
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     description = "Enter Authorization Token",
     *     type="string"
     *   ),
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     required=true,
     *     description = "page",
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="Success"),
     *   @SWG\Response(response=400, description="Failed"),
     *   @SWG\Response(response=405, description="Undocumented data"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     */

    /**
     *  Function :  Get inbox of login user
     * headers : Accept : application/json
     * @return response (status, message, success/failure)
     */

    public function getInbox(Request $request)
    {
        $requested_data = $request->all();

        $messages = Message::whereIn('id', function ($query) use ($requested_data) {
            $query->selectRaw('max(`id`)')
                ->from('messages')
                ->where('sender_id', '=', Auth::user()->id)
                ->orWhere('receiver_id', '=', Auth::user()->id)
                ->groupBy('thread_id');
        })->orderBy('created_at', 'desc')
            ->with(['senderData' => function ($query) {
                $query->select('id', 'fname', 'image', 'status');
            }, 'receiverData' => function ($query) {
                $query->select('id', 'fname', 'image', 'status');
            }])
            ->withCount(['participant' => function ($query) use ($requested_data) {
                $query->where('receiver_id', Auth::user()->id)->where('is_read', 0);
            }]);


            if(isset($request->q) && !empty($request->q))
            {
                $messages = $messages->whereHas('senderData' , function ($query) use($request){
                $query->select('id', 'fname', 'image', 'status')->where('fname', 'Like', '%' . $request->q . '%');
                });
            }

        $page_record = \Config::get('variable.page_per_record');
        $messages = $messages->paginate($page_record)->toArray();

        if ($messages) {
            $data['status'] = 200;
            $data['message'] = 'Inbox fetched successfully.';
            $data['user_id'] = Auth::user()->id;
            $data['data'] = $messages;
            return Response::json($data);
        } else {
            $data['status'] = 200;
            $data['message'] = 'No records found.';
            $data['user_id'] = Auth::user()->id;
            return Response::json($data);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     *
     *  @SWG\Get(
     *   path="/getChat",
     *   summary="Get chat with a user",
     *   consumes={"multipart/form-data"},
     *   produces={"application/json"},
     *   tags={"Chat"},
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     description = "Enter Authorization Token",
     *     type="string"
     *   ),
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     required=true,
     *     description = "page",
     *     type="number"
     *   ),
     * @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     required=true,
     *     description = "user id",
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="Success"),
     *   @SWG\Response(response=400, description="Failed"),
     *   @SWG\Response(response=405, description="Undocumented data"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     */

    /**
     *  Function :  Get inbox of login user
     * headers : Accept : application/json
     * @return response (status, message, success/failure)
     */
    public function getChat(Request $request)
    {
        $requested_data = $request->all();
        $page_record = \Config::get('variable.page_per_record');

        #check is user is blocked by logged in user or vice versa
        $is_blocked = BlockedUser::where(['blocked_by' => Auth::user()->id, 'blocked_to' => $requested_data['user_id']])->
            orWhere(function ($query) use ($requested_data) {
            $query->where(['blocked_by' => $requested_data['user_id'], 'blocked_to' => Auth::user()->id]);
        })->count();

        #check is user is blocked by logged in user
        $is_blockedby_login_user = BlockedUser::where(['blocked_by' => Auth::user()->id, 'blocked_to' => $requested_data['user_id']])->count();

        #check is logged in user blocked by user
        $is_blockedby_other = BlockedUser::where(['blocked_by' => $requested_data['user_id'], 'blocked_to' => Auth::user()->id])->count();

        $messages = Message::select('id', 'thread_id', 'sender_id', 'receiver_id', 'message', 'event_id', 'created_at')
            ->where(function ($query) use ($requested_data) {
                $query->where('sender_id', Auth::user()->id)->where('receiver_id', $requested_data['user_id']);
                $query->orWhere('sender_id', $requested_data['user_id'])->where('receiver_id', Auth::user()->id);
            })->with([
            'senderData' => function ($query) {
                $query->select('id', 'fname', 'image', 'status');
            },
            'receiverData' => function ($query) {
                $query->select('id', 'fname', 'image', 'status');
            },
        ])->where('type', '!=', 'GROUP')->orderBy('created_at', 'desc')->paginate($page_record)->toArray();

        if ($messages) {
            $data['status'] = 200;
            $data['message'] = 'Chat fetched successfully.';
            $data['user_id'] = Auth::user()->id;
            $data['data'] = $messages;
            if ($is_blocked > 0) {
                $response["is_blocked"] = true;
            } else {
                $response["is_blocked"] = false;
            }

            if ($is_blockedby_login_user > 0) {
                $response["is_blockedby_me"] = true;
            } else {
                $response["is_blockedby_me"] = false;
            }

            if ($is_blockedby_other > 0) {
                $response["is_blockedby_other"] = true;
            } else {
                $response["is_blockedby_other"] = false;
            }
            return Response::json($data);
        } else {
            $data['status'] = 200;
            $data['message'] = 'No message found.';
            $data['user_id'] = Auth::user()->id;
            if ($is_blocked > 0) {
                $response["is_blocked"] = true;
            } else {
                $response["is_blocked"] = false;
            }

            if ($is_blockedby_login_user > 0) {
                $response["is_blockedby_me"] = true;
            } else {
                $response["is_blockedby_me"] = false;
            }

            if ($is_blockedby_other > 0) {
                $response["is_blockedby_other"] = true;
            } else {
                $response["is_blockedby_other"] = false;
            }
            return Response::json($data);
        }
    }

}
