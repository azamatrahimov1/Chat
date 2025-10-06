<div class="space-y-6">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account messages') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex h-screen">
        {{-- Chap panel: Chatlar ro‘yxati --}}
        <div class="w-1/3 border-r overflow-y-auto p-4">
            <h2 class="text-lg font-semibold mb-3">Chats</h2>
            @foreach($chats as $chat)
                <div
                    wire:click="selectChat({{ $chat->id }})"
                    class="cursor-pointer p-2 rounded {{ $activeChat && $activeChat->id === $chat->id ? 'bg-blue-100' : 'hover:bg-gray-100' }}">
                    {{ $chat->name ?? 'No name chat' }}
                </div>
            @endforeach
        </div>

        {{-- O‘ng panel: Chat xabarlari --}}
        <div class="flex-1 flex flex-col">
            @if($activeChat)
                <div class="flex justify-between items-center border-b p-3 bg-gray-50">
                    <h3 class="font-semibold">{{ $activeChat->name ?? 'Chat' }}</h3>

                    <button
                        wire:click="$dispatch('openChatInfo', { id: {{ $activeChat->id }} })"
                        class="text-blue-600 text-sm"
                    >
                        Info
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @foreach($messages as $message)
                        <div class="p-2 rounded {{ $message->user_id === auth()->id() ? 'bg-blue-100 text-right' : 'bg-gray-100' }}">
                            <strong>{{ $message->user->name }}</strong>: {{ $message->content }}
                        </div>
                    @endforeach
                </div>

                <div class="p-3 border-t flex">
                    <input
                        type="text"
                        wire:model="newMessage"
                        wire:keydown.enter="sendMessage"
                        class="flex-1 border rounded p-2"
                        placeholder="Write message..."
                    >
                    <button wire:click="sendMessage" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded">Send</button>
                </div>
            @else
                <div class="flex items-center justify-center flex-1 text-gray-500">
                    Chatni tanlang 👈
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Browser events mapping --}}
<script>
    // Dispatch Livewire events to window for Alpine scroller
    document.addEventListener('livewire:load', function () {
        Livewire.on('message-sent', () => {
            window.dispatchEvent(new Event('message-sent'));
        });
        Livewire.on('chat-selected', () => {
            window.dispatchEvent(new Event('chat-selected'));
        });
    });

    // Also map Livewire dispatched browser events
    window.addEventListener('message-sent', () => {
        // noop — the x-init listener will pick this up and scroll
    });
    window.addEventListener('chat-selected', () => {
        // noop — used to scroll to bottom
    });
</script>
