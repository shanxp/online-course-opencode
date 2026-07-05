<div
    x-data="{
        visible: false,
        message: '',
        type: 'success',
        timer: null,
        show(type, message) {
            this.type = type;
            this.message = message;
            this.visible = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.visible = false; }, 4000);
        }
    }"
    x-on:toast.window="show($event.detail.type, $event.detail.message)"
    x-show="visible"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm"
    :class="type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'"
    x-text="message"
></div>

@if(session('success'))
    <script>
        document.addEventListener('alpine:init', () => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: '{{ session('success') }}' } }));
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('alpine:init', () => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: '{{ session('error') }}' } }));
        });
    </script>
@endif
