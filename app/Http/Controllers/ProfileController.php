<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $platformPoints = \App\Models\ActivityLog::query()
            ->selectRaw('platform, COALESCE(SUM(computed_points), 0) as total_points')
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->groupBy('platform')
            ->orderByDesc('total_points')
            ->get();

        $activityPoints = \App\Models\ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->sum('computed_points');

        $conversionPoints = \App\Models\Conversion::query()
            ->where('user_id', $user->id)
            ->where('status', 'Verified')
            ->sum('computed_points');

        return view('profile', [
            'user' => $user,
            'platformPoints' => $platformPoints,
            'activityPoints' => (float) $activityPoints,
            'conversionPoints' => (float) $conversionPoints,
        ]);
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
}
