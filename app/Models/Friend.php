<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Friend extends Model
{

    protected $fillable = ['owner_user_id', 'friend_id', 'status', 'created_at', 'updated_at'];

    public $timestamps = false;


	public function owner()
	{
		return $this->belongsTo(User::class,'owner_user_id','id')->select('id','name','image','status');
	
	}
	public function friend()
	{
		return $this->belongsTo(User::class,'friend_id','id')->select('id','name','image','status')->where('status',1);
	}


	# my blocks
    public function blockMyusers()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_to', 'friend_id')->where('blocked_by',Auth::user()->id);
    }

    # blocks tp me 
    public function blockMeusers()
    {
        return $this->hasMany(BlockedUser::class, 'blocked_by', 'friend_id')->where('blocked_to',Auth::user()->id);
    }
	



}
