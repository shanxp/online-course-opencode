@extends('layouts.admin')

@section('title', __('messages.edit_user'))

@section('page-content')
    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.edit_user_label', ['name' => $user->name]) }}</h1>

    <div class="mt-6 max-w-2xl space-y-6">
        @if(session('reset_password'))
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-yellow-800">{{ __('messages.password_reset') }}</p>
                        <p class="mt-1 text-sm text-yellow-700">
                            {{ __('messages.new_password_for', ['name' => $user->name]) }}
                        </p>
                        <div class="mt-2 flex items-center gap-2">
                            <code class="px-3 py-1.5 bg-white border border-yellow-300 rounded text-sm font-mono text-yellow-900 select-all">{{ session('reset_password') }}</code>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ session('reset_password') }}')"
                                    class="text-xs text-yellow-700 hover:text-yellow-900 underline shrink-0">
                                {{ __('messages.copy') }}
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-yellow-600">{{ __('messages.share_with_user') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
            <span><strong>{{ __('messages.created_at_col') }}:</strong> {{ $user->created_at->format('M d, Y H:i') }}</span>
            <span><strong>{{ __('messages.updated_at_col') }}:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</span>
            <span><strong>{{ __('messages.last_login_col') }}:</strong> {{ $user->last_login_at?->format('M d, Y H:i') ?? '—' }}</span>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">{{ __('messages.username') }}</label>
                <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('messages.email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('messages.password_leave_blank') }}</label>
                <input type="password" name="password" id="password"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 border-t">
                <p class="text-sm text-gray-500 mb-2">{{ __('messages.forgot_password_generate') }}</p>
                <a href="{{ route('admin.users.reset-password', $user) }}"
                   @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.users.reset-password', $user) }}', isLink: true, message: '{{ __('messages.reset_password_for_user', ['name' => $user->name]) }}' })"
                   class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 cursor-pointer">
                    {{ __('messages.reset_password') }}
                </a>
            </div>

            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">{{ __('messages.role') }}</label>
                <select name="role_id" id="role_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="is_active" class="text-sm text-gray-700">{{ __('messages.active') }}</label>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                    {{ __('messages.update_user') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
