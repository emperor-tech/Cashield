<?php
namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\ChatMessageSent;

class ChatController extends Controller
{
    public function fetch(Report $report)
    {
        $messages = $report->chatMessages()->with('user')->latest()->take(50)->get()->reverse()->values();
        return response()->json($messages);
    }

    public function send(Request $request, Report $report)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        $msg = $report->chatMessages()->create([
            'user_id' => Auth::id(),
            'message' => $data['message'],
        ]);
        $msg->load('user');
        broadcast(new ChatMessageSent($msg))->toOthers();
        return response()->json($msg);
    }
} 