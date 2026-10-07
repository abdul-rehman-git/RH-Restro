<template>
  <div ref="widgetRef" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 max-w-[calc(100vw-2rem)]">
    <!-- Chat Toggle Button -->
    <button
      v-if="!isOpen"
      @click.stop="isOpen = true"
      class="relative bg-[#0A0A0A] hover:bg-zinc-900 border-2 border-amber-500 p-2.5 sm:p-3 rounded-full shadow-[0_10px_30px_rgba(245,158,11,0.35)] focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-300 hover:scale-110 flex items-center justify-center group"
      aria-label="Open RH Restro AI Assistant"
    >
      <img
        :src="'/rh-restro-logo.png?v=20261007_v1'"
        alt="RH Assistant"
        class="h-8 w-8 sm:h-9 sm:w-9 object-contain group-hover:scale-105 transition-transform"
      />
      <!-- Active online pulse dot -->
      <span class="absolute -top-0.5 -right-0.5 flex h-3.5 w-3.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-[#0A0A0A]"></span>
      </span>
    </button>

    <!-- Chat Window -->
    <div
      v-else
      class="bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-[calc(100vw-2rem)] sm:w-[380px] h-[75vh] max-h-[580px] sm:h-[600px] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 slide-in-from-bottom-4 duration-300"
    >
      <!-- Luxury Header -->
      <div class="bg-slate-950 text-white p-3.5 sm:p-4 flex justify-between items-center border-b border-amber-500/20 relative overflow-hidden backdrop-blur-md shrink-0">
        <!-- Top Metallic Gold Accent Strip -->
        <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-amber-600 via-amber-300 to-amber-600"></div>
        
        <div class="flex items-center gap-3 min-w-0 relative z-10">
          <div class="w-9 h-9 rounded-full bg-[#0A0A0A] border border-amber-500/60 flex items-center justify-center shadow-lg shrink-0 p-1">
            <img :src="'/rh-restro-logo.png?v=20261007_v1'" alt="RH" class="w-full h-full object-contain" />
          </div>
          <div class="min-w-0">
            <h3 class="font-sans font-bold text-sm sm:text-base tracking-wide text-white flex items-center gap-1.5 truncate">
              <span class="text-white font-extrabold">RH</span>
              <span class="bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent font-extrabold">Restro</span>
              <span class="text-slate-300 font-normal text-xs sm:text-sm">Assistant</span>
            </h3>
            <div class="flex items-center gap-1.5 text-[11px] text-amber-200/90 font-medium mt-0.5">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
              <span class="truncate">Live Dining Support</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-1 relative z-10 shrink-0">
          <!-- Reset Session -->
          <button @click="resetSession" class="text-slate-300 hover:text-amber-400 hover:bg-slate-800/80 transition-colors p-1.5 rounded-lg" title="New Chat">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
          <!-- Close -->
          <button @click.stop="isOpen = false" class="text-slate-300 hover:text-amber-400 hover:bg-slate-800/80 transition-colors p-1.5 rounded-lg" title="Close">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="flex-1 overflow-y-auto p-5 space-y-5 bg-gray-50/50 dark:bg-zinc-900/50 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent" ref="messagesContainer">
        <div v-if="messages.length === 0" class="h-full flex flex-col items-center justify-center text-center px-4 opacity-90 animate-in fade-in duration-500">
           <div class="w-16 h-16 rounded-2xl bg-[#0A0A0A] border border-amber-500/40 flex items-center justify-center p-2 mb-3 shadow-md">
             <img :src="'/rh-restro-logo.png?v=20261007_v1'" alt="RH" class="w-full h-full object-contain" />
           </div>
          <p class="font-sans text-xl font-bold">
            Welcome to <span class="text-slate-900 dark:text-white font-extrabold">RH</span> <span class="bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent font-extrabold">Restro</span>
          </p>
          <p class="text-xs mt-2 text-gray-500 dark:text-zinc-400 max-w-[250px] leading-relaxed">Ask me about our menu, chef specials, reservations, or delivery.</p>
        </div>

        <div v-for="(msg, index) in messages" :key="index" 
             v-show="msg.content || msg.role === 'user'"
             :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
          <div :class="[
            'max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm',
            msg.role === 'user' 
              ? 'bg-amber-600 dark:bg-amber-600 text-white dark:text-zinc-950 font-medium rounded-tr-sm whitespace-pre-wrap' 
              : 'bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-800 dark:text-gray-200 rounded-tl-sm'
          ]">
            <template v-if="msg.role === 'user'">
              {{ msg.content }}
            </template>
            <template v-else>
              <div v-html="formatMessage(msg.content)" class="space-y-2"></div>
            </template>
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isLoading && !currentStreamingMessage" class="flex justify-start">
          <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm flex gap-1.5 items-center">
            <div class="w-1.5 h-1.5 bg-amber-600 dark:bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
            <div class="w-1.5 h-1.5 bg-amber-600 dark:bg-amber-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
            <div class="w-1.5 h-1.5 bg-amber-600 dark:bg-amber-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-3 bg-white dark:bg-zinc-950 border-t border-gray-200 dark:border-zinc-800">
        <!-- Attachment Preview Bar -->
        <div v-if="selectedImage" class="mb-2 px-2.5 py-1.5 flex items-center justify-between bg-amber-500/10 dark:bg-zinc-900 rounded-xl border border-amber-500/30">
          <div class="flex items-center gap-2">
            <img :src="selectedImage.previewUrl" class="w-10 h-10 object-cover rounded-lg border border-amber-500/50 shadow-sm" />
            <span class="text-xs text-zinc-700 dark:text-zinc-300 font-medium">Product image attached</span>
          </div>
          <button type="button" @click="removeImage" class="text-zinc-400 hover:text-red-500 p-1 rounded-full transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="sendMessage" class="flex items-center gap-2">
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleImageSelect"
          />
          
          <button
            type="button"
            @click="triggerImageUpload"
            :disabled="isLoading"
            title="Upload product image to search"
            class="p-2 text-zinc-500 hover:text-amber-600 dark:text-zinc-400 dark:hover:text-amber-400 hover:bg-gray-100 dark:hover:bg-zinc-900 rounded-full transition-all duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>

          <input
            v-model="newMessage"
            type="text"
            placeholder="Ask or attach product photo..."
            class="flex-1 rounded-full bg-gray-50 dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 focus:border-amber-500 focus:ring-amber-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 text-sm px-4 py-2.5 shadow-inner transition-colors"
            :disabled="isLoading"
          />
          <button
            type="submit"
            :disabled="(!newMessage.trim() && !selectedImage) || isLoading"
            class="bg-amber-600 hover:bg-amber-700 dark:bg-amber-600 dark:hover:bg-amber-500 text-white dark:text-zinc-950 rounded-full p-2.5 shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform active:scale-95"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch, onMounted, onUnmounted } from 'vue';

const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const sessionId = ref(null);
const currentStreamingMessage = ref('');
const messagesContainer = ref(null);
const widgetRef = ref(null);
const selectedImage = ref(null);
const fileInput = ref(null);

const triggerImageUpload = () => {
  if (fileInput.value) fileInput.value.click();
};

const handleImageSelect = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    alert('Please select an image smaller than 5MB.');
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    selectedImage.value = {
      base64: e.target.result,
      previewUrl: e.target.result
    };
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  selectedImage.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const handleClickOutside = (event) => {
  if (isOpen.value && widgetRef.value && !widgetRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

const saveStateToStorage = () => {
  try {
    sessionStorage.setItem('rh_chat_messages', JSON.stringify(messages.value));
    if (sessionId.value) {
      sessionStorage.setItem('rh_chat_session_id', sessionId.value);
    }
  } catch (e) {
    console.error('Failed to save chat state:', e);
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);

  // Restore chat state from sessionStorage
  try {
    const savedMessages = sessionStorage.getItem('rh_chat_messages');
    const savedSessionId = sessionStorage.getItem('rh_chat_session_id');
    if (savedMessages) {
      messages.value = JSON.parse(savedMessages);
    }
    if (savedSessionId) {
      sessionId.value = savedSessionId;
    }
  } catch (e) {
    console.error('Failed to load chat history:', e);
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

watch(isOpen, (newVal) => {
  if (newVal) {
    scrollToBottom();
    setTimeout(scrollToBottom, 60);
    setTimeout(scrollToBottom, 150);
  }
});

const formatMessage = (text) => {
  if (!text) return '';
  let formatted = text;
  
  // Parse images first
  formatted = formatted.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="w-24 h-24 object-cover rounded-lg mb-2 shadow-sm border border-gray-200 dark:border-zinc-700" />');
  
  // Parse standard links
  formatted = formatted.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-amber-600 dark:text-amber-500 hover:brightness-110 underline decoration-amber-600/30 dark:decoration-amber-500/30 hover:decoration-amber-600 dark:hover:decoration-amber-500 font-medium transition-colors">$1</a>');
  
  // Parse bold text
  formatted = formatted.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-gray-900 dark:text-gray-100 font-semibold">$1</strong>');
  
  return formatted;
};

const resetSession = () => {
  messages.value = [];
  sessionId.value = null;
  newMessage.value = '';
  currentStreamingMessage.value = '';
  try {
    sessionStorage.removeItem('rh_chat_messages');
    sessionStorage.removeItem('rh_chat_session_id');
  } catch (e) {
    // Ignore storage clear error
  }
};

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedImage.value) || isLoading.value) return;

  const userText = newMessage.value.trim();
  const imagePayload = selectedImage.value ? selectedImage.value.base64 : null;

  let displayContent = userText;
  if (imagePayload) {
    displayContent = "![" + (userText || "Product Image") + "](" + imagePayload + ")" + (userText ? "\n\n" + userText : "");
  }

  messages.value.push({ role: 'user', content: displayContent });
  newMessage.value = '';
  selectedImage.value = null;
  if (fileInput.value) fileInput.value.value = '';

  isLoading.value = true;
  currentStreamingMessage.value = '';

  // Add empty assistant message placeholder for streaming
  messages.value.push({ role: 'assistant', content: '' });
  const assistantMsgIndex = messages.value.length - 1;
  saveStateToStorage();
  scrollToBottom();

  try {
    const response = await fetch(route('chat.message.store'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        message: userText,
        image: imagePayload,
        session_id: sessionId.value
      })
    });

    if (!response.ok) throw new Error('Network response was not ok');

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let done = false;

    while (!done) {
      const { value, done: readerDone } = await reader.read();
      done = readerDone;
      if (value) {
        const chunk = decoder.decode(value, { stream: true });
        
        const lines = chunk.split('\n\n');
        for (const line of lines) {
          if (line.startsWith('data: ')) {
            const dataStr = line.replace('data: ', '').trim();
            if (dataStr === '[DONE]') {
               // Streaming finished
            } else if (dataStr) {
               try {
                 const data = JSON.parse(dataStr);
                 if (data.session_id) {
                   sessionId.value = data.session_id;
                 }
                 if (data.text) {
                   let chunkText = data.text;
                   if (chunkText.includes('rate limited') || chunkText.includes('Rate limit')) {
                     messages.value[assistantMsgIndex].content = "I'm receiving a lot of questions right now! Please wait a few seconds and ask me again. 😊";
                   } else if (chunkText.includes('Error:') || chunkText.includes('Connection refused') || chunkText.includes('cURL error')) {
                     messages.value[assistantMsgIndex].content = "I'm having a brief connection issue. Please try your message again in a moment! 😊";
                   } else {
                     messages.value[assistantMsgIndex].content += chunkText;
                   }
                   currentStreamingMessage.value = messages.value[assistantMsgIndex].content;
                   saveStateToStorage();
                   scrollToBottom();
                 }
               } catch (e) {
                 // Ignore parse errors from chunk fragmentation
               }
             }
          }
        }
      }
    }
  } catch (error) {
    console.error('Chat Error:', error);
    messages.value[assistantMsgIndex].content = "Network connection hiccup. Please check your connection and try again! 🌐";
  } finally {
    isLoading.value = false;
    currentStreamingMessage.value = '';
    saveStateToStorage();
    scrollToBottom();
  }
};
</script>
