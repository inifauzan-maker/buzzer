<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['leader', 'users'])
            ->withCount('users')
            ->orderBy('team_name')
            ->get();

        return view('teams', [
            'teams' => $teams,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'team_name' => 'required|string|max:120|unique:teams,team_name',
            'reminder_phone' => 'nullable|string|max:30',
        ]);

        Team::create($data);

        return redirect()
            ->route('teams.index')
            ->with('status', 'Tim baru berhasil ditambahkan.');
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'team_name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('teams', 'team_name')->ignore($team->id),
            ],
            'reminder_phone' => 'nullable|string|max:30',
        ]);

        $team->update($data);

        return redirect()
            ->route('teams.index')
            ->with('status', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        $hasUsers = $team->users()->exists();
        $hasActivities = $team->activities()->exists();
        $hasConversions = $team->conversions()->exists();

        if ($hasUsers || $hasActivities || $hasConversions) {
            return redirect()
                ->route('teams.index')
                ->withErrors(['team' => 'Tim tidak bisa dihapus karena masih memiliki data (user/aktivitas/konversi).']);
        }

        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('status', 'Tim berhasil dihapus.');
    }

    public function storeMember(Request $request)
    {
        $data = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'role' => 'required|in:leader,staff',
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($data['role'] === 'leader') {
            $exists = User::where('team_id', $data['team_id'])
                ->where('role', 'leader')
                ->exists();

            if ($exists) {
                return back()
                    ->withErrors(['role' => 'Tim ini sudah memiliki leader.'])
                    ->withInput();
            }
        }

        User::create([
            'team_id' => $data['team_id'],
            'role' => $data['role'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('teams.index')
            ->with('status', 'User tim berhasil ditambahkan.');
    }
}
