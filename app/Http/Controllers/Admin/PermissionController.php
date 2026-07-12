<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        private readonly ActivityLoggerService $logger,
    ) {}

    public function index(Request $request): View
    {
        $groups = Group::with(['users', 'courses', 'folders'])->orderBy('name')->get();
        $courses = Course::orderBy('title')->get();
        $users = User::with('role')->orderBy('name')->get();
        $selectedGroupId = $request->get('group_id');

        $groupFolderIds = [];
        $groupUserIds = [];
        $folderOptions = [];

        if ($selectedGroupId) {
            $group = $groups->firstWhere('id', (int) $selectedGroupId);
            if ($group) {
                $groupFolderIds = $group->folders->pluck('id')->toArray();
                $groupUserIds = $group->users->pluck('id')->toArray();
            }
            $folderOptions = $this->buildFolderTree();
        }

        return view('admin.permissions.index', compact(
            'groups', 'courses', 'users', 'selectedGroupId', 'folderOptions',
            'groupFolderIds', 'groupUserIds'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Group::create($validated);

        $this->logger->log('group_created', "Group '{$validated['name']}' created");

        return redirect()->route('admin.permissions.index')
            ->with('success', __('messages.msg_group_created'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $group->update($validated);

        $this->logger->log('group_updated', "Group '{$validated['name']}' updated");

        return redirect()->route('admin.permissions.index')
            ->with('success', __('messages.msg_group_updated'));
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->logger->log('group_deleted', "Group '{$group->name}' deleted");
        $group->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', __('messages.msg_group_deleted'));
    }

    public function addUser(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'string', 'regex:/^[\d,]+$/'],
        ]);

        $userIds = array_filter(explode(',', $validated['user_ids']));

        if (empty($userIds)) {
            return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
                ->with('error', __('messages.msg_select_users'));
        }

        $group->users()->syncWithoutDetaching($userIds);

        $users = User::whereIn('id', $userIds)->get();
        $names = $users->pluck('name')->join(', ');
        $this->logger->log('group_user_added', "Users '{$names}' added to group '{$group->name}'");

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_users_added', ['count' => count($users)]));
    }

    public function removeUser(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $group->users()->detach($validated['user_id']);

        $user = User::find($validated['user_id']);
        $this->logger->log('group_user_removed', "User '{$user->name}' removed from group '{$group->name}'");

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_user_removed'));
    }

    public function addCourse(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'permission' => ['required', 'in:view,download'],
        ]);

        $group->courses()->syncWithoutDetaching([
            $validated['course_id'] => ['permission' => $validated['permission']],
        ]);

        $course = Course::find($validated['course_id']);
        $this->logger->log('group_course_added', "Course '{$course->title}' ({$validated['permission']}) added to group '{$group->name}'");

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_course_perm_added'));
    }

    public function removeCourse(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $group->courses()->detach($validated['course_id']);

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_course_perm_removed'));
    }

    public function addFolder(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'folder_ids' => ['required', 'string', 'regex:/^[\d,]+$/'],
            'permission' => ['required', 'in:view,download'],
        ]);

        $folderIds = array_filter(explode(',', $validated['folder_ids']));

        if (empty($folderIds)) {
            return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
                ->with('error', __('messages.msg_select_folders'));
        }

        $attach = [];
        $folders = Folder::whereIn('id', $folderIds)->get();

        foreach ($folders as $folder) {
            $attach[$folder->id] = ['permission' => $validated['permission']];
        }

        $group->folders()->syncWithoutDetaching($attach);

        $names = $folders->pluck('name')->join(', ');
        $this->logger->log('group_folder_added', "Folders '{$names}' ({$validated['permission']}) added to group '{$group->name}'");

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_folder_perms_added', ['count' => count($folders)]));
    }

    public function removeFolder(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'folder_id' => ['required', 'exists:folders,id'],
        ]);

        $group->folders()->detach($validated['folder_id']);

        return redirect()->route('admin.permissions.index', ['group_id' => $group->id])
            ->with('success', __('messages.msg_folder_perm_removed'));
    }

    private function buildFolderTree(): array
    {
        $allFolders = Folder::with('course')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $result = [];
        $byParent = $allFolders->groupBy(fn($f) => $f->parent_id ?? '__null__');

        $build = function ($parentId, $pathPrefix) use ($byParent, &$build, &$result) {
            $key = $parentId ?? '__null__';
            foreach ($byParent->get($key, collect()) as $folder) {
                $folder->display_name = $folder->course->title . ' › ' . $pathPrefix . $folder->name;
                $result[] = $folder;
                $build($folder->id, $pathPrefix . $folder->name . ' › ');
            }
        };

        $build(null, '');

        return $result;
    }
}
