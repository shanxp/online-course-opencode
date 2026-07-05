@props(['confirmText' => __('messages.delete'), 'cancelText' => __('messages.cancel')])

<div x-data="{
    show: false,
    action: '',
    method: 'DELETE',
    message: '',
    isLink: false,
    confirm() {
        if (this.isLink) {
            window.location.href = this.action;
        } else {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.action;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            if (this.method !== 'POST') {
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = this.method;
                form.appendChild(method);
            }
            document.body.appendChild(form);
            form.submit();
        }
        this.show = false;
    }
}"
x-on:confirm-open.window="show = true; action = $event.detail.action; method = $event.detail.method || 'DELETE'; message = $event.detail.message; isLink = $event.detail.isLink || false"
x-show="show" x-cloak
class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50" @click="show = false"></div>
    <div class="relative bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
        <div class="flex items-center gap-3">
            <div class="shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.confirm') }}</h3>
        </div>
        <p class="mt-3 text-sm text-gray-600" x-text="message"></p>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="show = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">{{ $cancelText }}</button>
            <button @click="confirm()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">{{ $confirmText }}</button>
        </div>
    </div>
</div>
