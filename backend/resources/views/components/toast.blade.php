{{--
    Reusable Notification Toast Component for Laravel Blade & Tailwind CSS
    Features:
    - Alpine.js powered smooth transitions (slide & fade)
    - Auto-dismiss with configurable timer (default: 7s)
    - Manual dismiss button ('X')
    - 4 Status Types: Success, Error/Danger, Warning, Info with inline SVG icons
    - Auto-detects Laravel session flash messages: session('success'), session('error'), etc.
    - Global window event listener: window.dispatchEvent(new CustomEvent('toast', { detail: { message: '...', type: 'success', duration: 7000 } }))
    - Mobile-friendly & responsive (bottom on mobile, top-right on desktop)
    - Pause timer on hover
--}}

@props([
    'type' => null,
    'message' => null,
    'title' => null,
    'duration' => 7000, // Duration in milliseconds (7 seconds)
    'position' => 'top-right', // 'top-right', 'top-center', 'bottom-right', 'bottom-center'
])

@php
    // Detect standard Laravel session flash messages if not passed as props
    $sessionType = null;
    $sessionMessage = null;

    if (session()->has('success')) {
        $sessionType = 'success';
        $sessionMessage = session('success');
    } elseif (session()->has('error')) {
        $sessionType = 'error';
        $sessionMessage = session('error');
    } elseif (session()->has('danger')) {
        $sessionType = 'error';
        $sessionMessage = session('danger');
    } elseif (session()->has('warning')) {
        $sessionType = 'warning';
        $sessionMessage = session('warning');
    } elseif (session()->has('info')) {
        $sessionType = 'info';
        $sessionMessage = session('info');
    } elseif (session()->has('status')) {
        $sessionType = 'info';
        $sessionMessage = session('status');
    }

    $initialType = $type ?? $sessionType;
    $initialMessage = $message ?? $sessionMessage;
@endphp

<div
    x-data="notificationToast({
        defaultDuration: {{ (int) $duration }},
        initial: @js($initialMessage ? ['type' => $initialType, 'message' => $initialMessage, 'title' => $title] : null)
    })"
    x-init="init()"
    @toast.window="addToast($event.detail)"
    aria-live="assertive"
    class="fixed inset-0 z-50 pointer-events-none flex flex-col p-4 sm:p-6 sm:items-end justify-start gap-3 w-full"
>
    <template x-for="item in toasts" :key="item.id">
        <div
            x-show="item.visible"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95 sm:translate-x-4"
            @mouseenter="pauseTimer(item)"
            @mouseleave="resumeTimer(item)"
            :class="getThemeClasses(item.type)"
            class="pointer-events-auto relative w-full sm:max-w-md rounded-xl shadow-lg border p-4 backdrop-blur-sm transition-all overflow-hidden"
            role="alert"
        >
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0 mt-0.5">
                    <!-- Success Icon -->
                    <template x-if="item.type === 'success'">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </template>

                    <!-- Error / Danger Icon -->
                    <template x-if="item.type === 'error' || item.type === 'danger'">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </template>

                    <!-- Warning Icon -->
                    <template x-if="item.type === 'warning'">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </template>

                    <!-- Info Icon -->
                    <template x-if="item.type === 'info'">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-sky-500/15 text-sky-600 dark:text-sky-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Content -->
                <div class="flex-1 pt-0.5 min-w-0">
                    <template x-if="item.title">
                        <h4 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white mb-0.5" x-text="item.title"></h4>
                    </template>
                    <p class="text-xs sm:text-sm font-normal text-gray-700 dark:text-gray-300 break-words leading-relaxed" x-text="item.message"></p>
                </div>

                <!-- Manual Close Button -->
                <button
                    type="button"
                    @click="dismiss(item.id)"
                    class="flex-shrink-0 -mr-1 -mt-1 p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800/60 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition"
                    aria-label="Close notification"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Auto-Dismiss Progress Bar -->
            <div
                class="absolute bottom-0 left-0 h-1 bg-current opacity-20 transition-all duration-100 ease-linear rounded-full"
                :style="`width: ${item.progress}%;`"
            ></div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationToast', ({ defaultDuration = 7000, initial = null } = {}) => ({
            toasts: [],
            counter: 0,
            defaultDuration,

            init() {
                if (initial && initial.message) {
                    this.addToast({
                        type: initial.type || 'info',
                        message: initial.message,
                        title: initial.title || null,
                        duration: this.defaultDuration
                    });
                }
            },

            addToast({ type = 'info', message = '', title = null, duration = this.defaultDuration }) {
                const id = ++this.counter;
                const toast = {
                    id,
                    type,
                    message,
                    title,
                    duration: duration > 0 ? duration : this.defaultDuration,
                    remaining: duration > 0 ? duration : this.defaultDuration,
                    progress: 100,
                    visible: true,
                    timer: null,
                    interval: null,
                    isPaused: false,
                    startedAt: Date.now()
                };

                this.toasts.push(toast);
                this.startTimer(toast);
            },

            startTimer(toast) {
                const step = 50; // Update progress bar every 50ms
                toast.startedAt = Date.now();

                toast.interval = setInterval(() => {
                    if (!toast.isPaused) {
                        toast.remaining -= step;
                        toast.progress = Math.max(0, (toast.remaining / toast.duration) * 100);

                        if (toast.remaining <= 0) {
                            clearInterval(toast.interval);
                            this.dismiss(toast.id);
                        }
                    }
                }, step);
            },

            pauseTimer(toast) {
                toast.isPaused = true;
            },

            resumeTimer(toast) {
                toast.isPaused = false;
            },

            dismiss(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    const toast = this.toasts[index];
                    if (toast.interval) clearInterval(toast.interval);
                    toast.visible = false;
                    // Remove from DOM array after transition completes
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 250);
                }
            },

            getThemeClasses(type) {
                switch (type) {
                    case 'success':
                        return 'bg-white dark:bg-gray-900 border-emerald-500/30 text-emerald-600 dark:text-emerald-400';
                    case 'error':
                    case 'danger':
                        return 'bg-white dark:bg-gray-900 border-rose-500/30 text-rose-600 dark:text-rose-400';
                    case 'warning':
                        return 'bg-white dark:bg-gray-900 border-amber-500/30 text-amber-600 dark:text-amber-400';
                    case 'info':
                    default:
                        return 'bg-white dark:bg-gray-900 border-sky-500/30 text-sky-600 dark:text-sky-400';
                }
            }
        }));
    });

    /**
     * Global helper function to trigger a toast from anywhere in JavaScript
     * Example: toast('Changes saved successfully!', 'success', 'Saved', 7000);
     */
    window.toast = function(message, type = 'info', title = null, duration = 7000) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type, title, duration }
        }));
    };
</script>
