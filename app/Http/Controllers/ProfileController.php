<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Conversion;
use App\Models\SocialAccount;
use App\Models\User;
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
        return $this->renderProfile($user, false);
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

        return view('profile', [
            'user' => $user,
            'isOwner' => $isOwner,
            'platformPoints' => $platformPoints,
            'activityPoints' => (float) $activityPoints,
            'conversionPoints' => (float) $conversionPoints,
            'socialAccounts' => $socialAccounts,
            'platformOptions' => $this->platformOptions(),
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
