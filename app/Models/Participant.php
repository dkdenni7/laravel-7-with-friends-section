<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Participant extends Authenticatable
{
    public $timestamps = false;
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message_id','thread_id', 'sender_id', 'receiver_id', 'event_id', 'delivered_status', 'is_read', 'created_at', 'updated_at'];

}
