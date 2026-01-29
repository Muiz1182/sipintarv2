<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Mengambil riwayat pesan antara dua user
     * Sekaligus menandai pesan tersebut telah dibaca.
     */
    public function getMessages($receiverId)
    {
        $authId = Auth::id();

        // 1. Ambil riwayat percakapan
        $messages = Message::where(function($q) use ($authId, $receiverId) {
            $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($authId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        // 2. Tandai pesan dari lawan bicara sebagai 'sudah dibaca' (is_read = true)
        Message::where('sender_id', $receiverId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Menyimpan pesan baru
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    /**
     * Menghitung total pesan yang belum dibaca untuk user yang sedang login
     */
    public function getUnreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
                        ->where('is_read', false)
                        ->count();

        return response()->json(['unread_count' => $count]);
    }
}