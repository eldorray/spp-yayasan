<script setup lang="ts">
import { Bot, RotateCcw, Send, Square, X } from 'lucide-vue-next';
import { ref, nextTick, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type ChatMessage = {
    role: 'user' | 'assistant';
    content: string;
};

const CHAT_STORAGE_KEY = 'spp-ai-chat-messages';

const isOpen = ref(false);
const chatMessages = ref<ChatMessage[]>([]);
const chatInput = ref('');
const chatLoading = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
let abortController: AbortController | null = null;

onMounted(() => {
    const saved = localStorage.getItem(CHAT_STORAGE_KEY);
    if (saved) {
        try {
            chatMessages.value = JSON.parse(saved);
        } catch {
            chatMessages.value = [];
        }
    }
});

watch(chatMessages, (msgs) => {
    localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(msgs));
}, { deep: true });

function toggle() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        nextTick(() => scrollToBottom());
    }
}

function resetChat() {
    chatMessages.value = [];
    localStorage.removeItem(CHAT_STORAGE_KEY);
}

async function sendMessage() {
    const question = chatInput.value.trim();
    if (!question || chatLoading.value) return;

    chatMessages.value.push({ role: 'user', content: question });
    chatInput.value = '';
    chatLoading.value = true;

    await nextTick();
    scrollToBottom();

    abortController = new AbortController();

    try {
        const response = await fetch('/reports/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ question, history: chatMessages.value.slice(-6) }),
            signal: abortController.signal,
        });

        const data = await response.json();
        chatMessages.value.push({ role: 'assistant', content: data.answer ?? 'Maaf, tidak dapat memproses pertanyaan.' });
    } catch (e: any) {
        if (e.name === 'AbortError') {
            chatMessages.value.push({ role: 'assistant', content: 'Permintaan dibatalkan.' });
        } else {
            chatMessages.value.push({ role: 'assistant', content: 'Terjadi kesalahan koneksi. Silakan coba lagi.' });
        }
    } finally {
        chatLoading.value = false;
        abortController = null;
        await nextTick();
        scrollToBottom();
    }
}

function cancelRequest() {
    if (abortController) {
        abortController.abort();
    }
}

function scrollToBottom() {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
}

function renderMarkdown(text: string): string {
    let html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const lines = html.split('\n');
    let result: string[] = [];
    let inTable = false;
    let tableRows: string[] = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (line.startsWith('|') && line.endsWith('|')) {
            if (/^\|[\s\-:]+\|/.test(line) && line.includes('---')) continue;
            if (!inTable) { inTable = true; tableRows = []; }
            tableRows.push(line);
        } else {
            if (inTable) { result.push(buildTable(tableRows)); inTable = false; tableRows = []; }
            result.push(line || '');
        }
    }
    if (inTable) result.push(buildTable(tableRows));

    html = result.join('\n');
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/^[\-\*]\s+(.+)$/gm, '<li>$1</li>');
    html = html.replace(/\n\n/g, '</p><p class="mt-2">');
    html = html.replace(/\n/g, '<br>');

    return `<p>${html}</p>`;
}

function buildTable(rows: string[]): string {
    if (rows.length === 0) return '';
    let t = '<div class="my-2 overflow-x-auto rounded border"><table class="w-full text-xs">';
    rows.forEach((row, idx) => {
        const cells = row.split('|').filter((c) => c.trim() !== '');
        const tag = idx === 0 ? 'th' : 'td';
        const rc = idx === 0 ? 'bg-muted/50 font-medium' : idx % 2 === 0 ? 'bg-background' : '';
        t += `<tr class="${rc}">`;
        cells.forEach((cell) => {
            const cc = tag === 'th' ? 'px-2 py-1.5 text-left font-semibold border-b' : 'px-2 py-1 border-b border-muted';
            t += `<${tag} class="${cc}">${cell.trim()}</${tag}>`;
        });
        t += '</tr>';
    });
    t += '</table></div>';
    return t;
}

function sendQuickQuestion(q: string) {
    chatInput.value = q;
    sendMessage();
}
</script>

<template>
    <!-- Floating Trigger Button -->
    <button
        class="fixed bottom-6 right-6 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border border-neutral-200/60 dark:border-zinc-800/60 shadow-lg text-primary transition-all duration-300 hover:scale-105 hover:shadow-xl hover:bg-white dark:hover:bg-zinc-900 active:scale-95 cursor-pointer"
        @click="toggle"
    >
        <Bot v-if="!isOpen" class="h-5 w-5" />
        <X v-else class="h-5 w-5" />
    </button>

    <!-- Chat Window -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
    >
        <div
            v-if="isOpen"
            class="fixed bottom-20 right-6 z-50 flex w-[380px] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-neutral-200/50 dark:border-zinc-800/50 bg-white/60 dark:bg-zinc-950/60 backdrop-blur-xl shadow-2xl"
            style="height: 520px"
        >
            <!-- Window Title Bar -->
            <div class="flex items-center justify-between border-b border-neutral-200/40 dark:border-zinc-800/40 bg-neutral-100/40 dark:bg-zinc-900/40 px-4 py-3 shrink-0">
                <div class="flex items-center gap-2">
                    <!-- Traffic Lights (Red Closes, Yellow Minimizes, Green Resets) -->
                    <div class="flex gap-1.5">
                        <span 
                            class="h-3 w-3 rounded-full bg-[#FF5F56] border border-[#E0443E] cursor-pointer hover:opacity-85 active:scale-90 transition-transform"
                            title="Close"
                            @click="isOpen = false"
                        ></span>
                        <span 
                            class="h-3 w-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] cursor-pointer hover:opacity-85 active:scale-90 transition-transform"
                            title="Minimize"
                            @click="isOpen = false"
                        ></span>
                        <span 
                            class="h-3 w-3 rounded-full bg-[#27C93F] border border-[#1AAB29] cursor-pointer hover:opacity-85 active:scale-90 transition-transform"
                            title="Reset Chat"
                            @click="resetChat"
                        ></span>
                    </div>
                </div>
                <div class="text-xs font-bold text-neutral-700 dark:text-neutral-200 flex items-center gap-1.5 tracking-wide">
                    <Bot class="h-3.5 w-3.5 text-primary" /> Asisten AI
                </div>
                <div>
                    <button
                        v-if="chatMessages.length > 0"
                        class="rounded-lg p-1.5 transition-colors hover:bg-neutral-200/40 dark:hover:bg-zinc-800/40 text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer"
                        @click="resetChat"
                        title="Clear Conversation"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                    </button>
                    <div v-else class="w-7"></div>
                </div>
            </div>

            <!-- Messages Area -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-neutral-50/20 dark:bg-zinc-950/10">
                <!-- Empty State -->
                <div v-if="chatMessages.length === 0" class="flex h-full items-center justify-center text-center p-4">
                    <div class="max-w-[280px]">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 border border-primary/20">
                            <Bot class="h-6 w-6 text-primary" />
                        </div>
                        <h4 class="text-sm font-bold text-neutral-800 dark:text-neutral-200">Halo! Saya Asisten AI</h4>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                            Ajukan pertanyaan seputar data keuangan, laporan pemasukan, atau daftar tunggakan siswa.
                        </p>
                        <div class="mt-4 flex flex-col gap-2">
                            <button 
                                class="rounded-xl border border-neutral-200/40 dark:border-zinc-800/40 bg-white/40 dark:bg-zinc-900/40 px-3 py-2 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 transition-colors hover:bg-neutral-50/80 dark:hover:bg-zinc-900/80 hover:border-neutral-300 dark:hover:border-zinc-700 cursor-pointer shadow-xs" 
                                @click="sendQuickQuestion('Berapa total tunggakan?')"
                            >
                                💰 Berapa total tunggakan?
                            </button>
                            <button 
                                class="rounded-xl border border-neutral-200/40 dark:border-zinc-800/40 bg-white/40 dark:bg-zinc-900/40 px-3 py-2 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 transition-colors hover:bg-neutral-50/80 dark:hover:bg-zinc-900/80 hover:border-neutral-300 dark:hover:border-zinc-700 cursor-pointer shadow-xs" 
                                @click="sendQuickQuestion('Siapa yang belum bayar bulan ini?')"
                            >
                                📋 Siapa yang belum bayar?
                            </button>
                            <button 
                                class="rounded-xl border border-neutral-200/40 dark:border-zinc-800/40 bg-white/40 dark:bg-zinc-900/40 px-3 py-2 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 transition-colors hover:bg-neutral-50/80 dark:hover:bg-zinc-900/80 hover:border-neutral-300 dark:hover:border-zinc-700 cursor-pointer shadow-xs" 
                                @click="sendQuickQuestion('Rekap pemasukan per bulan')"
                            >
                                📊 Rekap pemasukan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages List -->
                <div v-for="(msg, i) in chatMessages" :key="i" class="flex flex-col gap-1.5">
                    <!-- User Message -->
                    <div v-if="msg.role === 'user'" class="flex justify-end">
                        <div class="max-w-[85%] rounded-2xl rounded-tr-xs bg-primary px-3.5 py-2.5 text-xs text-primary-foreground shadow-sm">
                            <p class="leading-relaxed">{{ msg.content }}</p>
                        </div>
                    </div>
                    <!-- Assistant Message -->
                    <div v-else class="flex gap-2.5">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 border border-primary/20">
                            <Bot class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div class="max-w-[85%] rounded-2xl rounded-tl-xs bg-white/70 dark:bg-zinc-900/70 border border-neutral-200/30 dark:border-zinc-800/30 px-3.5 py-2.5 text-xs text-neutral-800 dark:text-neutral-200 shadow-sm leading-relaxed">
                            <div class="ai-content leading-relaxed" v-html="renderMarkdown(msg.content)" />
                        </div>
                    </div>
                </div>

                <!-- Loading Bubble -->
                <div v-if="chatLoading" class="flex gap-2.5">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 border border-primary/20">
                        <Bot class="h-3.5 w-3.5 text-primary animate-pulse" />
                    </div>
                    <div class="rounded-2xl rounded-tl-xs bg-white/70 dark:bg-zinc-900/70 border border-neutral-200/30 dark:border-zinc-800/30 px-4 py-3 shadow-xs">
                        <div class="flex items-center gap-1.5 text-xs text-neutral-400">
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-primary" style="animation-delay: 0ms" />
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-primary" style="animation-delay: 150ms" />
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-primary" style="animation-delay: 300ms" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-neutral-200/40 dark:border-zinc-800/40 bg-neutral-50/50 dark:bg-zinc-900/50 p-3 shrink-0">
                <form class="flex gap-2 items-center" @submit.prevent="sendMessage">
                    <input
                        v-model="chatInput"
                        type="text"
                        placeholder="Tanya asisten keuangan..."
                        class="flex-1 tahoe-input h-9 px-3.5 text-xs"
                        :disabled="chatLoading"
                    />
                    <button 
                        v-if="!chatLoading" 
                        type="submit" 
                        :disabled="!chatInput.trim()" 
                        class="tahoe-button-primary h-9 w-9 p-0 flex items-center justify-center shrink-0 cursor-pointer disabled:opacity-50"
                        title="Send Message"
                    >
                        <Send class="h-3.5 w-3.5 text-white" />
                    </button>
                    <button 
                        v-else 
                        type="button" 
                        class="h-9 w-9 rounded-xl border border-red-200 dark:border-red-950 bg-red-50 dark:bg-red-950/20 text-red-600 flex items-center justify-center shrink-0 cursor-pointer hover:bg-red-100" 
                        @click="cancelRequest"
                        title="Cancel Request"
                    >
                        <Square class="h-3.5 w-3.5" />
                    </button>
                </form>
            </div>
        </div>
    </Transition>
</template>
