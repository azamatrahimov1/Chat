<div class="space-y-6">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account messages') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div
        x-data="{ showSidebar: true }"
        x-init="() => { if (window.innerWidth < 768) showSidebar = false }"
        x-on:resize.window="showSidebar = window.innerWidth >= 768"
        class="h-[80vh] flex bg-white dark:bg-slate-900 border rounded-lg overflow-hidden shadow-sm transition-colors duration-300"
    >
        <!-- Sidebar: Chats -->
        <aside
            x-show="showSidebar"
            x-transition
            class="w-full md:w-80 border-r bg-slate-50 dark:bg-slate-800 md:block"
        >
            <div class="px-4 py-3 flex items-center justify-between">
                <flux:subheading size="sm" class="dark:text-slate-200">{{ __('Your Chats') }}</flux:subheading>
                <flux:button size="sm" wire:click.prevent="$emit('openCreateChatModal')">+ {{ __('New') }}</flux:button>
            </div>

            <div class="px-3 overflow-y-auto h-[calc(80vh-60px)]">
                @forelse($chats as $chat)
                    @php
                        $other = $chat->is_group ? null : $chat->users->where('id', '!=', auth()->id())->first();
                        $title = $chat->is_group ? ($chat->name ?: __('Group')) : ($other?->name ?? __('Unknown'));
                    @endphp
                    <button
                        wire:click="selectChat({{ $chat->id }})"
                        x-on:click="if (window.innerWidth < 768) showSidebar = false"
                        class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700 transition
                        {{ $activeChat && $activeChat->id === $chat->id ? 'bg-white dark:bg-slate-700 shadow-sm' : '' }}">
                        <flux:avatar size="sm" :name="$title" />
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="font-medium text-sm dark:text-slate-100">{{ $title }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $chat->messages()->latest()->first()?->created_at?->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ optional($chat->messages()->latest()->first())->content ?? __('No messages yet') }}
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        {{ __('No chats yet. Click "New" to create one.') }}
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Chat window -->
        <main class="flex-1 flex flex-col dark:bg-slate-900">
            @if($activeChat)
                <header class="px-4 py-3 border-b dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button x-on:click="showSidebar = true" class="md:hidden text-gray-500 dark:text-gray-300">
                            ← {{ __('Back') }}
                        </button>
                        <flux:avatar :name="$activeChat->is_group ? ($activeChat->name ?: 'Group') : ($activeChat->users->where('id','!=',auth()->id())->first()?->name ?? 'User')" />
                        <div>
                            <div class="font-semibold dark:text-slate-100">
                                {{ $activeChat->is_group ? ($activeChat->name ?: __('Group')) : ($activeChat->users->where('id','!=',auth()->id())->first()?->name ?? __('User')) }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $activeChat->is_group ? count($activeChat->users) . ' members' : __('Private chat') }}
                            </div>
                        </div>
                    </div>
                </header>

                <div id="messagesContainer" wire:ignore
                     x-data x-ref="scroller"
                     x-init="() => {
                    const el = $refs.scroller;
                    el.scrollTop = el.scrollHeight;
                    Livewire.on('message-sent', () => setTimeout(()=> el.scrollTop = el.scrollHeight, 50));
                    Livewire.on('chat-selected', () => setTimeout(()=> el.scrollTop = el.scrollHeight, 50));
                }"
                     class="flex-1 p-6 overflow-y-auto bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-800"
                >
                    @foreach($messages as $msg)
                        <div class="mb-3 flex {{ $msg->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%]">
                                <div class="inline-flex items-end gap-2">
                                    @if($msg->user_id !== auth()->id())
                                        <flux:avatar size="xs" :name="$msg->user->name" />
                                    @endif
                                    <div class="px-4 py-2 rounded-2xl
                                            {{ $msg->user_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-700 border dark:border-slate-600' }}">
                                        <div class="text-sm leading-relaxed break-words">{{ $msg->content }}</div>
                                        <div class="text-[10px] mt-1 text-gray-400 dark:text-gray-300">
                                            {{ $msg->created_at->format('H:i, d M') }} • {{ $msg->user->name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t dark:border-slate-700 bg-white dark:bg-slate-800">
                    <div class="flex gap-3">
                        <input wire:model.defer="newMessage"
                               wire:keydown.enter="sendMessage"
                               type="text"
                               placeholder="{{ __('Type a message...') }}"
                               class="flex-1 rounded-full border dark:border-slate-600 bg-transparent px-4 py-2 text-slate-800 dark:text-slate-100 focus:ring focus:outline-none"
                        />
                        <flux:button wire:click="sendMessage" class="shrink-0">{{ __('Send') }}</flux:button>
                    </div>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-gray-400 dark:text-gray-300">
                    <div class="max-w-md text-center">
                        <p class="mb-4">{{ __('Select a chat to start messaging') }}</p>
                        <flux:button wire:click="$emit('openCreateChatModal')">{{ __('Create chat') }}</flux:button>
                    </div>
                </div>
            @endif
        </main>
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
