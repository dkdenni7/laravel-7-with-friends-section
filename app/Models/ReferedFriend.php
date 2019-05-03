<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ReferedFriend extends Model
{

    protected $fillable = ['sender_id', 'refer_email', 'content','created_at'];

    public $timestamps = false;


    public function user()
    {
    	return $this->belongsTo(User::class);
    }
  
}