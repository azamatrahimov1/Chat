<?php

namespace App\Livewire\Chat;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Message extends Component
{
    public $chats;        // Collection of chats the user belongs to
    public $activeChat;   // Chat model or null
    public $messages;     // Collection of messages for active chat
    public $newMessage = '';

    public function mount()
    {
        try {
            $this->chats = Auth::user()->chats()->with('users')->latest('chats.created_at')->get();
        } catch (\Throwable $e) {
            // DB table missing yoki boshqa xatolik — bo'sh koleksiya
            $this->chats = collect();
        }

        $this->activeChat = null;
        $this->messages = collect();
    }

    public function selectChat($chatId)
    {
        try {
            $chat = Chat::with('users')->find($chatId);
            $this->activeChat = $chat;
            $this->messages = $chat
                ? $chat->messages()->with('user')->latest()->take(100)->get()->reverse()->values()
                : collect();

            // front-endga signal: scroll qilinsin
            $this->dispatchBrowserEvent('chat-selected');
        } catch (\Throwable $e) {
            $this->activeChat = null;
            $this->messages = collect();
        }
    }

    public function sendMessage()
    {
        $text = trim($this->newMessage);
        if (!$this->activeChat || $text === '') return;

        $msg = Message::create([
            'chat_id' => $this->activeChat->id,
            'user_id' => Auth::id(),
            'content' => $text,
        ]);

        $this->newMessage = '';

        // qo'shilgan xabarni kolleksiyaga qo'shamiz
        $this->messages->push($msg->load('user'));

        // frontendga signal
        $this->dispatchBrowserEvent('message-sent');
    }

    public function render()
    {
        return view('livewire.chat.message');
    }
}
