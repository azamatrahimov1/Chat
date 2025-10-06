<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(3)->create();

        $chat = Chat::query()->create(['name' => 'General', 'is_group' => true]);
        $chat->users()->attach($users->pluck('id'));

        foreach ($users as $user) {
            Message::query()->create([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
                'content' => "Hello from {$user->name}!",
            ]);
        }
    }
}
