@extends('layouts.admin')

@section('title', __('messages.permissions'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.user_groups_permissions') }}</h1>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-6 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.create_group') }}</h2>
                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <input type="text" name="name" placeholder="{{ __('messages.group_name') }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    </div>
                    <div>
                        <textarea name="description" placeholder="{{ __('messages.description_optional') }}" rows="2"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                        {{ __('messages.create_group') }}
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.groups_label') }}</h2>
                @if($groups->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('messages.no_groups') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach($groups as $group)
                            <a href="{{ route('admin.permissions.index', ['group_id' => $group->id]) }}"
                               class="block px-3 py-2 rounded-md text-sm truncate {{ $selectedGroupId == $group->id ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                {{ $group->name }}
                                <span class="text-xs text-gray-400">({{ $group->users->count() }} {{ __('messages.users_count') }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-5">
            @if($selectedGroupId)
                @php $group = $groups->firstWhere('id', (int) $selectedGroupId); @endphp
                @if($group)
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <form method="POST" action="{{ route('admin.permissions.update', $group) }}">
                                @csrf
                                <div class="flex items-center gap-3">
                                    <input type="text" name="name" value="{{ $group->name }}" required
                                           class="flex-1 min-w-0 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-lg font-medium px-4 py-3">
                                    <button type="submit" class="px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-md hover:bg-primary-700">{{ __('messages.save') }}</button>
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <textarea name="description" rows="1" placeholder="{{ __('messages.description') }}"
                                              class="flex-1 min-w-0 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">{{ $group->description }}</textarea>
                                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.permissions.destroy', $group) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete_group') }}' })"
                                            class="text-sm text-red-600 hover:text-red-800 whitespace-nowrap cursor-pointer">{{ __('messages.delete') }}</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.members') }}</h2>
                            <form method="POST" action="{{ route('admin.permissions.add-user', $group) }}" class="mb-4">
                                @csrf
                                <div class="space-y-3">
                                    <x-searchable-multi-select name="user_ids" :options="$users" :placeholder="__('messages.search_users')" displayField="name" subTextField="email" :disabledIds="$groupUserIds" />
                                    <button type="submit" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">{{ __('messages.add_user_to_group') }}</button>
                                </div>
                            </form>
                            @if($group->users->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($group->users as $member)
                                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-md gap-3">
                                            <span class="text-sm truncate min-w-0">{{ $member->name }} <span class="text-gray-500">({{ $member->email }})</span></span>
                                            <form method="POST" action="{{ route('admin.permissions.remove-user', $group) }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.permissions.remove-user', $group) }}', method: 'POST', message: '{{ __('messages.confirm_remove_user') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.remove_from_group') }}</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('messages.no_members') }}</p>
                            @endif
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.course_permissions') }}</h2>
                            <form method="POST" action="{{ route('admin.permissions.add-course', $group) }}" class="mb-4">
                                @csrf
                                <div class="space-y-3">
                                    <x-searchable-select name="course_id" :options="$courses" :placeholder="__('messages.search_courses')" displayField="title" />
                                    <div class="flex items-center gap-3">
                                        <select name="permission" required class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                                            <option value="view">{{ __('messages.view_option') }}</option>
                                            <option value="download">{{ __('messages.view_download_option') }}</option>
                                        </select>
                                        <button type="submit" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">{{ __('messages.grant') }}</button>
                                    </div>
                                </div>
                            </form>
                            @if($group->courses->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($group->courses as $perm)
                                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-md gap-3">
                                            <span class="text-sm truncate min-w-0">{{ $perm->title }} - <span class="text-primary-600 font-medium">{{ __('messages.permission_' . $perm->pivot->permission) }}</span></span>
                                            <form method="POST" action="{{ route('admin.permissions.remove-course', $group) }}">
                                                @csrf
                                                <input type="hidden" name="course_id" value="{{ $perm->id }}">
                                                <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.permissions.remove-course', $group) }}', method: 'POST', message: '{{ __('messages.confirm_revoke_course') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.revoke') }}</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('messages.no_course_permissions') }}</p>
                            @endif
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('messages.folder_permissions') }}</h2>
                            <form method="POST" action="{{ route('admin.permissions.add-folder', $group) }}" class="mb-4">
                                @csrf
                                <div class="space-y-3">
                                    <x-searchable-multi-select name="folder_ids" :options="$folderOptions" :placeholder="__('messages.search_folders')" displayField="display_name" :disabledIds="$groupFolderIds" />
                                    <div class="flex items-center gap-3">
                                        <select name="permission" required class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                                            <option value="view">{{ __('messages.view_option') }}</option>
                                            <option value="download">{{ __('messages.view_download_option') }}</option>
                                        </select>
                                        <button type="submit" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">{{ __('messages.grant') }}</button>
                                    </div>
                                </div>
                            </form>
                            @if($group->folders->isNotEmpty())
                                <div class="space-y-2">
                                    @php $folderOpts = collect($folderOptions)->keyBy('id'); @endphp
                                    @foreach($group->folders as $perm)
                                        @php $opt = $folderOpts->get($perm->id); @endphp
                                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-md gap-3">
                                            <span class="text-sm truncate min-w-0">
                                                @if($opt)
                                                    {{ $opt->display_name }}
                                                @else
                                                    {{ $perm->name }}
                                                @endif
                                                - <span class="text-primary-600 font-medium">{{ __('messages.permission_' . $perm->pivot->permission) }}</span>
                                            </span>
                                            <form method="POST" action="{{ route('admin.permissions.remove-folder', $group) }}">
                                                @csrf
                                                <input type="hidden" name="folder_id" value="{{ $perm->id }}">
                                                <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.permissions.remove-folder', $group) }}', method: 'POST', message: '{{ __('messages.confirm_revoke_folder') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.revoke') }}</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('messages.no_folder_permissions') }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                        {{ __('messages.no_groups_prompt') }}
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                    <p class="text-lg">{{ __('messages.select_a_group') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
