<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface ChatInterface
{
    public function getInbox(Request $request);

    public function getChat(Request $request);

}
