@extends('layouts.admin')

@section('title', __('messages.users'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.users') }}</h1>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
            {{ __('messages.create_user') }}
        </a>
    </div>

    <div class="mt-4 bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="{{ __('messages.search') }}..."
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">{{ __('messages.filter') }}</button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.clear') }}</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-4 bg-white rounded-lg shadow">
        @if($users->isEmpty())
            <x-empty-state :title="__('messages.no_users')" :description="__('messages.create_first_user')" :action="route('admin.users.create')" :actionLabel="__('messages.create_user')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.name_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.username') }}</th>
                        <th class="px-6 py-3">{{ __('messages.email') }}</th>
                        <th class="px-6 py-3">{{ __('messages.role_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.groups_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.status_col') }}</th>
                        <th class="px-6 py-3">
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $sortField === 'created_at' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700">
                                {{ __('messages.created_at_col') }}
                                @if($sortField === 'created_at') <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3">
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'last_login_at', 'direction' => $sortField === 'last_login_at' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700">
                                {{ __('messages.last_login_col') }}
                                @if($sortField === 'last_login_at') <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($users as $user)
                        <tr class="border-t hover:bg-gray-50 cursor-pointer" @click="window.location = '{{ route('admin.users.edit', $user) }}'">
                            <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->username }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->isAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $user->role?->name ?? __('messages.none') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->groups->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->groups as $group)
                                            <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-primary-100 text-primary-700">{{ $group->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">{{ __('messages.none') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="text-green-600 font-medium">{{ __('messages.active') }}</span>
                                @else
                                    <span class="text-red-600 font-medium">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $user->last_login_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-4" @click.stop>
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" class="text-primary-600 hover:text-primary-900 text-sm">{{ __('messages.actions_col') }}</button>
                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-44 bg-white rounded-md shadow-lg border z-50">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.edit') }}</a>
                                        <a href="{{ route('admin.users.reset-password', $user) }}"
                                           @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.users.reset-password', $user) }}', isLink: true, message: '{{ __('messages.reset_password_for_user_simple', ['name' => $user->name]) }}' })"
                                           class="block px-4 py-2 text-sm text-yellow-700 hover:bg-gray-100 cursor-pointer">{{ __('messages.reset_password') }}</a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                @csrf @method('DELETE')
                                                <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.users.destroy', $user) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete_user') }}' })" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 cursor-pointer">{{ __('messages.delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
