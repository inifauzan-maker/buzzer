<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Services\SystemActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('team')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $teams = Team::orderBy('team_name')->get();

        return view('users', [
            'users' => $users,
            'teams' => $teams,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($data['role'] === 'leader' && $data['team_id']) {
            $exists = User::where('team_id', $data['team_id'])
                ->where('role', 'leader')
                ->exists();

            if ($exists) {
                return back()
                    ->withErrors(['role' => 'Tim ini sudah memiliki leader.'])
                    ->withInput();
            }
        }

        $requiresTeam = in_array($data['role'], ['leader', 'staff'], true);

        $created = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'team_id' => $requiresTeam ? $data['team_id'] : null,
            'password' => Hash::make($data['password']),
        ]);

        SystemActivityLogger::log($request->user(), 'Menambahkan user '.$created->name.' ('.$created->role.').');

        return redirect()
            ->route('users.index')
            ->with('status', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateData($request, $user);

        if ($data['role'] === 'leader' && $data['team_id']) {
            $exists = User::where('team_id', $data['team_id'])
                ->where('role', 'leader')
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return back()
                    ->withErrors(['role' => 'Tim ini sudah memiliki leader.'])
                    ->withInput();
            }
        }

        $requiresTeam = in_array($data['role'], ['leader', 'staff'], true);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'team_id' => $requiresTeam ? $data['team_id'] : null,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        SystemActivityLogger::log($request->user(), 'Memperbarui user '.$user->name.' ('.$user->role.').');

        return redirect()
            ->route('users.index')
            ->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return redirect()
                ->route('users.index')
                ->withErrors(['user' => 'Tidak bisa menghapus akun sendiri.']);
        }

        SystemActivityLogger::log($request->user(), 'Menghapus user '.$user->name.' ('.$user->role.').');

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User berhasil dihapus.');
    }

    private function validateData(Request $request, ?User $user = null): array
    {
        $rule = [
            'name' => 'required|string|max:120',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:superadmin,admin,campaign_planner,ads_specialist,analyst,management,leader,staff,guest',
            'team_id' => [
                Rule::requiredIf(fn () => in_array($request->input('role'), ['leader', 'staff'], true)),
                'nullable',
                'exists:teams,id',
            ],
            'password' => $user
                ? 'nullable|string|min:6|confirmed'
                : 'required|string|min:6|confirmed',
        ];

        return $request->validate($rule);
    }
}
