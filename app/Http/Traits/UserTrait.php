<?php
namespace App\Http\Traits;

use Image;
use App\Models\Friend;
use Auth;

trait UserTrait
{
    private function userImageVersions($name)
    {
        $main_dir = storage_path() . '/app/public/user';
        $thumb_dir = storage_path() . '/app/public/user/thumb';

        if (!file_exists($thumb_dir)) {
            mkdir($thumb_dir, 0777);
            chmod($thumb_dir, 0777);
        }

        if (file_exists($main_dir . '/' . $name)) {
            chmod($main_dir . '/' . $name, 0777);
            Image::make($main_dir . '/' . $name)->resize(110, 110)->save($thumb_dir . '/' . $name);
            chmod($thumb_dir . '/' . $name, 0777);
        }
        return $name;
    }

    private function getVerificationCode($length = 12)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /* function to send a verification email */

    private function sendVerificationMail($register_data)
    {
        $email = trim($register_data["email"]);
        $admin_email = Config::get('variable.ADMIN_EMAIL');
        $frontend_url = Config::get('variable.FRONTEND_URL');
        $user = User::where('email', $email)->first();
        Mail::send('user.register', ['data' => array("verification_token" => $register_data["verification_token"], "email" => $email,
            "frontend_url" => $frontend_url, "name" => $user->name)], function ($message) use ($email, $admin_email) {
            $message->from($admin_email, 'INAR');
            $message->to(trim($email), 'INAR')->subject('INAR : Verify Account');
        });
    }


      # get friends basis on user id 
    public function userFriends($user_id)
    {
     $getFriends = Friend::where('owner_user_id',$user_id)
                   ->whereHas('friend', function($query) {
                    $query->where('status',1);
                  })
                   ->doesnthave('blockMyusers')
                   ->doesnthave('blockMeusers')
                   ->where('status',1)
                  ->pluck('friend_id');
      
        // $collection = collect($getFriends)->map(function ($name) {
        //     return $name["friend_id"];
        // });
        // $friends_id = $collection->toArray();  

       // dd($getFriends);
        return   $getFriends ;    
    }



}
