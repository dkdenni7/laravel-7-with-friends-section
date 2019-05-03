<?php

namespace App\Models;

use Config;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Message extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens;

   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sender_id', 'thread_id','receiver_id','event_id', 'message','type','status','created_at', 'updated_at'];

    public function senderData() {
        return $this->hasOne('App\User', 'id', 'sender_id');
    }

    public function receiverData() {
        return $this->hasOne('App\User', 'id', 'receiver_id');
    }
    
    
    public function messageData() {
        return $this->hasOne('App\Models\Participant', 'message_id', 'id');
    }
    
    
    public function participant() {
        return $this->belongsTo('App\Models\Participant', 'thread_id', 'thread_id');
    }

       
}
