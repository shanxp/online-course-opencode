@extends('layouts.admin')

@section('title', __('messages.activity_logs'))

@section('page-content')
    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.activity_logs') }}</h1>

    <div class="mt-4">
        <form method="GET" class="flex gap-4">
            <select name="action" class="rounded-md border-gray-300 text-base px-4 py-3" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_actions') }}</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
            <x-searchable-multi-select name="user_ids" :options="$users" :selected="$selectedUsers" placeholder="{{ __('messages.all_users') }}" autosubmit />
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
        @if($logs->isEmpty())
            <x-empty-state :title="__('messages.no_activity_logs')" :description="__('messages.activity_will_appear')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.user_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.action_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.description_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.date_time') }}</th>
                        <th class="px-6 py-3">{{ __('messages.ip_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($logs as $log)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($log->action === 'created') bg-green-100 text-green-700
                                    @elseif($log->action === 'updated') bg-blue-100 text-blue-700
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $log->description }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
