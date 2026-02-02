<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\Notification;
use App\Models\SocialAccount;
use App\Models\StaffTask;
use App\Models\TeamMemberTarget;
use App\Models\User;
use App\Services\SystemActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return $this->renderProfile($request->user(), true);
    }

    public function showUser(User $user)
    {
        $viewer = request()->user();
        if ($user->role === 'superadmin' && $viewer?->role !== 'superadmin') {
            abort(403, 'Akses ditolak.');
        }

        return $this->renderProfile($user, false);
    }

    public function storeTask(Request $request, User $user)
    {
        $actor = $request->user();
        if (! $actor || ! in_array($actor->role, ['superadmin', 'leader'], true)) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->role !== 'staff') {
            abort(403, 'Target tugas harus staff.');
        }

        if ($actor->role === 'leader' && $actor->team_id !== $user->team_id) {
            abort(403, 'Leader hanya boleh memberi tugas ke staff dalam timnya.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
        ]);

        $task = StaffTask::create([
            'leader_id' => $actor->id,
            'staff_id' => $user->id,
            'team_id' => $user->team_id,
            'title' => $data['title'],
            'notes' => $data['notes'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'open',
        ]);

        SystemActivityLogger::log($actor, 'Memberikan tugas ke staff '.$user->name.'.');

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Tugas baru',
            'message' => 'Anda mendapat tugas: '.$data['title'],
            'type' => 'task',
        ]);

        return redirect()
            ->route('profile.view', $user)
            ->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function updateTask(Request $request, StaffTask $task)
    {
        $actor = $request->user();
        if (! $actor || ! in_array($actor->role, ['superadmin', 'leader'], true)) {
            abort(403, 'Akses ditolak.');
        }

        if ($actor->role === 'leader' && $actor->team_id !== $task->team_id) {
            abort(403, 'Leader hanya boleh mengubah tugas timnya.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'status' => 'required|in:open,done',
        ]);

        $task->update($data);

        SystemActivityLogger::log($actor, 'Memperbarui tugas staff '.$task->staff?->name.'.');

        return redirect()
            ->route('profile.view', $task->staff_id)
            ->with('status', 'Tugas berhasil diperbarui.');
    }

    public function destroyTask(Request $request, StaffTask $task)
    {
        $actor = $request->user();
        if (! $actor || ! in_array($actor->role, ['superadmin', 'leader'], true)) {
            abort(403, 'Akses ditolak.');
        }

        if ($actor->role === 'leader' && $actor->team_id !== $task->team_id) {
            abort(403, 'Leader hanya boleh menghapus tugas timnya.');
        }

        $staffId = $task->staff_id;
        $task->delete();

        SystemActivityLogger::log($actor, 'Menghapus tugas staff.');

        return redirect()
            ->route('profile.view', $staffId)
            ->with('status', 'Tugas berhasil dihapus.');
    }

    public function updateTaskStatus(Request $request, StaffTask $task)
    {
        $actor = $request->user();
        if (! $actor) {
            abort(403, 'Akses ditolak.');
        }

        $isOwner = $actor->id === $task->staff_id;
        $isManager = in_array($actor->role, ['superadmin', 'leader'], true)
            && ($actor->role === 'superadmin' || $actor->team_id === $task->team_id);

        if (! $isOwner && ! $isManager) {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'status' => 'required|in:open,done',
        ]);

        $task->update(['status' => $data['status']]);

        if ($data['status'] === 'done') {
            if ($task->leader_id) {
                Notification::create([
                    'user_id' => $task->leader_id,
                    'title' => 'Tugas selesai',
                    'message' => 'Tugas "'.$task->title.'" telah diselesaikan oleh '.$task->staff?->name.'.',
                    'type' => 'task',
                ]);
            }
        }

        return redirect()
            ->route($isOwner ? 'profile.show' : 'profile.view', $isOwner ? [] : $task->staff_id)
            ->with('status', 'Status tugas diperbarui.');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        SystemActivityLogger::log($user, 'Memperbarui profil.');

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function storeSocialAccount(Request $request)
    {
        $data = $this->validateSocialAccount($request);

        SocialAccount::create([
            'user_id' => $request->user()->id,
            'platform' => $data['platform'],
            'handle' => $data['handle'],
            'profile_url' => $data['profile_url'] ?? null,
            'followers' => $data['followers'] ?? null,
            'following' => $data['following'] ?? null,
            'posts_count' => $data['posts_count'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Akun media sosial ditambahkan.');
    }

    public function updateSocialAccount(Request $request, SocialAccount $socialAccount)
    {
        if ($request->user()->id !== $socialAccount->user_id) {
            abort(403, 'Akses ditolak.');
        }

        $data = $this->validateSocialAccount($request);

        $socialAccount->update([
            'platform' => $data['platform'],
            'handle' => $data['handle'],
            'profile_url' => $data['profile_url'] ?? null,
            'followers' => $data['followers'] ?? null,
            'following' => $data['following'] ?? null,
            'posts_count' => $data['posts_count'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Akun media sosial diperbarui.');
    }

    public function destroySocialAccount(Request $request, SocialAccount $socialAccount)
    {
        if ($request->user()->id !== $socialAccount->user_id) {
            abort(403, 'Akses ditolak.');
        }

        $socialAccount->delete();

        return redirect()
            ->route('profile.show')
            ->with('status', 'Akun media sosial dihapus.');
    }

    private function renderProfile(User $user, bool $isOwner)
    {
        $viewer = request()->user();
        $platformPoints = ActivityLog::query()
            ->selectRaw('platform, COALESCE(SUM(computed_points), 0) as total_points')
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->groupBy('platform')
            ->orderByDesc('total_points')
            ->get();

        $activityPoints = ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->sum('computed_points');

        $conversionPoints = Conversion::query()
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->sum('computed_points');

        $socialAccounts = SocialAccount::query()
            ->where('user_id', $user->id)
            ->orderBy('platform')
            ->orderBy('handle')
            ->get();

        $targetYear = now()->year;
        $memberTargetClosing = 0;
        $memberTargetLeads = 0;
        $memberClosingAchieved = 0;
        $memberLeadsAchieved = 0;
        $memberClosingPercent = 0;
        $memberLeadsPercent = 0;
        $staffTasks = collect();
        $canAssignTasks = false;
        $canManageTasks = false;
        $canUpdateTaskStatus = false;
        $taskStatusFilter = request()->get('task_status');
        $taskCounts = ['open' => 0, 'done' => 0, 'all' => 0];

        if ($user->role === 'staff') {
            $memberTarget = TeamMemberTarget::query()
                ->where('user_id', $user->id)
                ->where('year', $targetYear)
                ->where('month', 0)
                ->first();

            $memberTargetClosing = (int) ($memberTarget?->target_closing ?? 0);
            $memberTargetLeads = (int) ($memberTarget?->target_leads ?? 0);

            $memberClosingAchieved = (int) Conversion::query()
                ->where('user_id', $user->id)
                ->where('status', 'Verified')
                ->where('type', 'Closing')
                ->whereYear('created_at', $targetYear)
                ->sum('amount');

            $memberLeadsAchieved = (int) Conversion::query()
                ->where('user_id', $user->id)
                ->where('status', 'Verified')
                ->where('type', 'Lead')
                ->whereYear('created_at', $targetYear)
                ->sum('amount');

            $memberClosingPercent = $memberTargetClosing > 0
                ? min(100, (int) round(($memberClosingAchieved / $memberTargetClosing) * 100))
                : 0;
            $memberLeadsPercent = $memberTargetLeads > 0
                ? min(100, (int) round(($memberLeadsAchieved / $memberTargetLeads) * 100))
                : 0;

            $tasksQuery = StaffTask::query()
                ->with('leader:id,name')
                ->where('staff_id', $user->id);

            if (in_array($taskStatusFilter, ['open', 'done'], true)) {
                $tasksQuery->where('status', $taskStatusFilter);
            }

            $staffTasks = $tasksQuery
                ->latest()
                ->get()
                ->map(function (StaffTask $task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'notes' => $task->notes,
                        'due_date' => optional($task->due_date)->format('d M Y'),
                        'due_date_raw' => optional($task->due_date)->format('Y-m-d'),
                        'status' => $task->status,
                        'leader_name' => $task->leader?->name,
                    ];
                });

            $taskCounts['open'] = StaffTask::where('staff_id', $user->id)->where('status', 'open')->count();
            $taskCounts['done'] = StaffTask::where('staff_id', $user->id)->where('status', 'done')->count();
            $taskCounts['all'] = $taskCounts['open'] + $taskCounts['done'];

            $canAssignTasks = ! $isOwner
                && $viewer
                && in_array($viewer->role, ['superadmin', 'leader'], true)
                && ($viewer->role === 'superadmin' || $viewer->team_id === $user->team_id);

            $canManageTasks = $canAssignTasks;
            $canUpdateTaskStatus = $isOwner;
        }

        return view('profile', [
            'user' => $user,
            'isOwner' => $isOwner,
            'platformPoints' => $platformPoints,
            'activityPoints' => (float) $activityPoints,
            'conversionPoints' => (float) $conversionPoints,
            'socialAccounts' => $socialAccounts,
            'platformOptions' => $this->platformOptions(),
            'targetYear' => $targetYear,
            'memberTargetClosing' => $memberTargetClosing,
            'memberTargetLeads' => $memberTargetLeads,
            'memberClosingAchieved' => $memberClosingAchieved,
            'memberLeadsAchieved' => $memberLeadsAchieved,
            'memberClosingPercent' => $memberClosingPercent,
            'memberLeadsPercent' => $memberLeadsPercent,
            'staffTasks' => $staffTasks,
            'canAssignTasks' => $canAssignTasks,
            'canManageTasks' => $canManageTasks,
            'canUpdateTaskStatus' => $canUpdateTaskStatus,
            'taskStatusFilter' => $taskStatusFilter,
            'taskCounts' => $taskCounts,
        ]);
    }

    private function validateSocialAccount(Request $request): array
    {
        return $request->validate([
            'platform' => 'required|in:IG,FB,TT,YT,Blog,WA',
            'handle' => 'required|string|max:120',
            'profile_url' => 'nullable|string|max:2048',
            'followers' => 'nullable|integer|min:0',
            'following' => 'nullable|integer|min:0',
            'posts_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
    }

    private function platformOptions(): array
    {
        return ['IG', 'FB', 'TT', 'YT', 'Blog', 'WA'];
    }
}
