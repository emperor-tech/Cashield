<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mt-8">
    <h1 class="text-2xl font-bold mb-4 text-blue-900 dark:text-blue-200">Report Details</h1>
    <div class="mb-4">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2">Campus: {{ $report->campus }}</span>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 mr-2">Location: {{ $report->location }}</span>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-{{ $report->severity == 'high' ? 'red' : ($report->severity == 'medium' ? 'yellow' : 'green') }}-100 dark:bg-{{ $report->severity == 'high' ? 'red' : ($report->severity == 'medium' ? 'yellow' : 'green') }}-900 text-{{ $report->severity == 'high' ? 'red' : ($report->severity == 'medium' ? 'yellow' : 'green') }}-800 dark:text-{{ $report->severity == 'high' ? 'red' : ($report->severity == 'medium' ? 'yellow' : 'green') }}-200">Severity: {{ ucfirst($report->severity) }}</span>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">Status: {{ ucfirst($report->status) }}</span>
    </div>
    <div class="mb-6 text-gray-700 dark:text-gray-200">
        <strong>Description:</strong> {{ $report->description }}
    </div>
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-2 text-blue-800 dark:text-blue-200">Live Chat</h2>
        <div x-data="chatComponent({{ $report->id }}, {{ auth()->id() }}, '{{ auth()->user()->name }}')" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 shadow mb-2">
            <div class="border rounded p-4 bg-white dark:bg-gray-800 max-h-64 overflow-y-auto mb-2" id="chat-messages">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="mb-2">
                        <span class="font-semibold text-blue-700 dark:text-blue-300" x-text="msg.user.name"></span>:
                        <span x-text="msg.message"></span>
                        <span class="text-xs text-gray-400 ml-2" x-text="msg.created_at"></span>
                    </div>
                </template>
            </div>
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input x-model="newMessage" type="text" class="flex-1 border rounded p-2" placeholder="Type your message..." required>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Send</button>
            </form>
        </div>
    </div>
    <div class="mt-12">
        <h2 class="text-xl font-bold mb-2 text-blue-800 dark:text-blue-200">Incident Timeline</h2>
        <ol class="border-l-2 border-blue-600 pl-4">
            @foreach($report->timelineEvents() as $event)
                <li class="mb-6">
                    <div class="flex items-center mb-1">
                        <span class="font-semibold text-blue-700 dark:text-blue-300 mr-2">{{ $event['user'] }}</span>
                        <span class="text-xs text-gray-400">{{ $event['created_at']->diffForHumans() }}</span>
                    </div>
                    <div class="ml-2">
                        @if($event['type'] === 'comment')
                            <span class="text-gray-700 dark:text-gray-200">💬 {{ $event['message'] }}</span>
                        @elseif($event['type'] === 'chat')
                            <span class="text-blue-600 dark:text-blue-300">🗨️ {{ $event['message'] }}</span>
                        @elseif($event['type'] === 'status')
                            <span class="text-green-600 dark:text-green-300">🔄 {{ $event['message'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
<script>
function chatComponent(reportId, userId, userName) {
    return {
        messages: [],
        newMessage: '',
        fetchMessages() {
            fetch(`/reports/${reportId}/chat`).then(r => r.json()).then(data => { this.messages = data; this.scrollToBottom(); });
        },
        sendMessage() {
            if (!this.newMessage.trim()) return;
            fetch(`/reports/${reportId}/chat`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ message: this.newMessage })
            }).then(r => r.json()).then(msg => {
                this.messages.push(msg);
                this.newMessage = '';
                this.scrollToBottom();
            });
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        init() {
            this.fetchMessages();
            window.Echo.join(`report-chat.${reportId}`)
                .here(() => {})
                .joining(() => {})
                .leaving(() => {})
                .listen('ChatMessageSent', (e) => {
                    this.messages.push(e);
                    this.scrollToBottom();
                });
        }
    }
}
</script> 