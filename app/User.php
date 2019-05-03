<?php

namespace App;

use Config;
use App\Models\BlockedUser;
use App\Models\Notification;
use App\Models\Friend;
use App\Models\ProJobInvite;
use App\Models\ReferedFriend;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Auth;

class User extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
      protected $fillable = [ 'role_id', 'name', 'fname', 'lname', 'otp', 'slug', 'email', 'secondary_email', 'password', 'image', 'country_code', 'mobile_no', 'verify_token', 'forgot_password_token', 'auth_token', 'status', 'remember_token', 'email_notification', 'push_notification', 'lat', 'lng', 'address', 'device_type', 'device_id', 'social_id', 'signup_type', 'current_login', 'last_login', 'socket_id', 'notifications', 'created_at', 'updated_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function AauthAcessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
    

    // path of documet
    public function getImageAttribute($value)
    {   
       $result = substr($value, 0, 4);
      
       if($result == 'http')  # if image from facebook
       {
         return $value;
       }

       $server_url = Config::get('variable.SERVER_URL_User_Profile');
        // if (!empty($value) && file_exists($server_url . '/storage/user/thumb/' . $value)) {
        //     return $server_url . '/storage/user/thumb/' . $value;
        // } else {
        //     return $server_url . '/images/user-default.png';
        // }
        return $server_url . '/storage/user/thumb/' . $value;


    }




    
     # function for relation with notification model
    public function un_read_noti() {
        return $this->hasMany(Notification::class,'receiver_id','id')->where('is_read',0)->where('status',1);
    }

    # my blocks
    public function blockMyusers()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_to', 'id')->where('blocked_by',Auth::user()->id);
    }

    # blocks tp me 
    public function blockMeusers()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_by', 'id')->where('blocked_to',Auth::user()->id);
    }

     public function friends()
    {
        return $this->hasMany(Friend::class, 'owner_user_id', 'id')->where('status',1);
    }
    public function rcfriends()
    {
        return $this->belongsTo(Friend::class, 'id', 'owner_user_id');
    }

      public function blocked()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_by', 'id');
    }

    # send request 
    public function sendRequestUsers()
    {
        return $this->hasMany(Friend::class, 'owner_user_id', 'id')->where('friend_id',Auth::user()->id);
    }

    # receieve request
    public function receiveRequestUsers()
    {
        return $this->hasMany(Friend::class, 'friend_id', 'id')->where('owner_user_id',Auth::user()->id);
    }

     public function inviteSentJob()
    {
        return $this->hasOne(ProJobInvite::class, 'user_id', 'id');
    }

    public function invitedUsers()
    {
        return $this->hasOne(ReferedFriend::class, 'refer_email', 'email')->where('sender_id',Auth::user()->id);
    }


    
}
