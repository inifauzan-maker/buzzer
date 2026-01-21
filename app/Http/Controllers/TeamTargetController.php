<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use App\Models\Team;
use App\Models\TeamMemberTarget;
use App\Models\TeamTarget;
use App\Models\User;
use Illuminate\Http\Request;

class TeamTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->team_id) {
            abort(403, 'Tim tidak ditemukan.');
        }

        $now = now();
        $year = (int) $request->query('year', $now->year);

        if ($year < 2000 || $year > 2100) {
            $year = $now->year;
        }

        $target = TeamTarget::query()
            ->where('team_id', $user->team_id)
            ->where('year', $year)
            ->where('month', 0)
            ->first();

        $closingAchieved = Conversion::query()
            ->where('team_id', $user->team_id)
            ->where('status', 'Verified')
            ->where('type', 'Closing')
            ->whereYear('created_at', $year)
            ->sum('amount');

        $leadsAchieved = Conversion::query()
            ->where('team_id', $user->team_id)
            ->where('status', 'Verified')
            ->where('type', 'Lead')
            ->whereYear('created_at', $year)
            ->sum('amount');

        $targetClosing = (int) ($target?->target_closing ?? 0);
        $targetLeads = (int) ($target?->target_leads ?? 0);

        $closingPercent = $targetClosing > 0
            ? min(100, (int) round(($closingAchieved / $targetClosing) * 100))
            : 0;
        $leadsPercent = $targetLeads > 0
            ? min(100, (int) round(($leadsAchieved / $targetLeads) * 100))
            : 0;

        $yearOptions = [];
        for ($i = -2; $i <= 1; $i++) {
            $yearOptions[] = $now->year + $i;
        }

        $members = User::query()
            ->where('team_id', $user->team_id)
            ->where('role', 'staff')
            ->orderBy('name')
            ->get();

        $memberIds = $members->pluck('id');
        $memberTargets = collect();
        if ($memberIds->isNotEmpty()) {
            $memberTargets = TeamMemberTarget::query()
                ->where('team_id', $user->team_id)
                ->where('year', $year)
                ->where('month', 0)
                ->whereIn('user_id', $memberIds)
                ->get()
                ->keyBy('user_id');
        }

        $memberClosing = collect();
        $memberLeads = collect();

        if ($memberIds->isNotEmpty()) {
            $memberClosing = Conversion::query()
                ->where('team_id', $user->team_id)
                ->where('status', 'Verified')
                ->where('type', 'Closing')
                ->whereYear('created_at', $year)
                ->whereIn('user_id', $memberIds)
                ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            $memberLeads = Conversion::query()
                ->where('team_id', $user->team_id)
                ->where('status', 'Verified')
                ->where('type', 'Lead')
                ->whereYear('created_at', $year)
                ->whereIn('user_id', $memberIds)
                ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id');
        }

        $memberRows = $members->map(function (User $member) use ($memberTargets, $memberClosing, $memberLeads) {
            $target = $memberTargets->get($member->id);
            $targetClosingMember = (int) ($target?->target_closing ?? 0);
            $targetLeadsMember = (int) ($target?->target_leads ?? 0);
            $closingAchievedMember = (int) ($memberClosing[$member->id] ?? 0);
            $leadsAchievedMember = (int) ($memberLeads[$member->id] ?? 0);

            $closingPercentMember = $targetClosingMember > 0
                ? min(100, (int) round(($closingAchievedMember / $targetClosingMember) * 100))
                : 0;
            $leadsPercentMember = $targetLeadsMember > 0
                ? min(100, (int) round(($leadsAchievedMember / $targetLeadsMember) * 100))
                : 0;

            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role,
                'target_closing' => $targetClosingMember,
                'target_leads' => $targetLeadsMember,
                'closing_achieved' => $closingAchievedMember,
                'leads_achieved' => $leadsAchievedMember,
                'closing_percent' => $closingPercentMember,
                'leads_percent' => $leadsPercentMember,
            ];
        });

        return view('targets', [
            'target' => $target,
            'selectedYear' => $year,
            'yearOptions' => $yearOptions,
            'closingAchieved' => $closingAchieved,
            'leadsAchieved' => $leadsAchieved,
            'targetClosing' => $targetClosing,
            'targetLeads' => $targetLeads,
            'closingPercent' => $closingPercent,
            'leadsPercent' => $leadsPercent,
            'memberRows' => $memberRows,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->team_id) {
            abort(403, 'Tim tidak ditemukan.');
        }

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'target_closing' => ['required', 'integer', 'min:0'],
            'target_leads' => ['required', 'integer', 'min:0'],
        ]);

        TeamTarget::updateOrCreate(
            [
                'team_id' => $user->team_id,
                'year' => $data['year'],
                'month' => 0,
            ],
            [
                'target_closing' => $data['target_closing'],
                'target_leads' => $data['target_leads'],
            ]
        );

        return redirect()
            ->route('targets.index', ['year' => $data['year']])
            ->with('status', 'Target tim tahunan berhasil disimpan.');
    }

    public function storeMembers(Request $request)
    {
        $user = $request->user();

        if (! $user->team_id) {
            abort(403, 'Tim tidak ditemukan.');
        }

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'targets' => ['required', 'array'],
            'targets.*.user_id' => ['required', 'integer'],
            'targets.*.target_closing' => ['required', 'integer', 'min:0'],
            'targets.*.target_leads' => ['required', 'integer', 'min:0'],
        ]);

        $memberIds = User::query()
            ->where('team_id', $user->team_id)
            ->where('role', 'staff')
            ->pluck('id')
            ->all();

        foreach ($data['targets'] as $targetData) {
            $memberId = (int) $targetData['user_id'];
            if (! in_array($memberId, $memberIds, true)) {
                continue;
            }

            TeamMemberTarget::updateOrCreate(
                [
                    'team_id' => $user->team_id,
                    'user_id' => $memberId,
                    'year' => $data['year'],
                    'month' => 0,
                ],
                [
                    'target_closing' => (int) $targetData['target_closing'],
                    'target_leads' => (int) $targetData['target_leads'],
                ]
            );
        }

        return redirect()
            ->route('targets.index', ['year' => $data['year']])
            ->with('status', 'Target anggota tim tahunan berhasil disimpan.');
    }

    public function adminIndex(Request $request)
    {
        $now = now();
        $year = (int) $request->query('year', $now->year);

        if ($year < 2000 || $year > 2100) {
            $year = $now->year;
        }

        $yearOptions = [];
        for ($i = -2; $i <= 1; $i++) {
            $yearOptions[] = $now->year + $i;
        }

        $teams = Team::query()->orderBy('team_name')->get();
        $teamTargets = TeamTarget::query()
            ->where('year', $year)
            ->where('month', 0)
            ->get()
            ->keyBy('team_id');

        $teamClosing = Conversion::query()
            ->where('status', 'Verified')
            ->where('type', 'Closing')
            ->whereYear('created_at', $year)
            ->selectRaw('team_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('team_id')
            ->pluck('total', 'team_id');

        $teamLeads = Conversion::query()
            ->where('status', 'Verified')
            ->where('type', 'Lead')
            ->whereYear('created_at', $year)
            ->selectRaw('team_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('team_id')
            ->pluck('total', 'team_id');

        $teamRows = $teams->map(function (Team $team) use ($teamTargets, $teamClosing, $teamLeads) {
            $target = $teamTargets->get($team->id);
            $targetClosing = (int) ($target?->target_closing ?? 0);
            $targetLeads = (int) ($target?->target_leads ?? 0);
            $closingAchieved = (int) ($teamClosing[$team->id] ?? 0);
            $leadsAchieved = (int) ($teamLeads[$team->id] ?? 0);

            $closingPercent = $targetClosing > 0
                ? min(100, (int) round(($closingAchieved / $targetClosing) * 100))
                : 0;
            $leadsPercent = $targetLeads > 0
                ? min(100, (int) round(($leadsAchieved / $targetLeads) * 100))
                : 0;

            return [
                'id' => $team->id,
                'name' => $team->team_name,
                'target_closing' => $targetClosing,
                'target_leads' => $targetLeads,
                'closing_achieved' => $closingAchieved,
                'leads_achieved' => $leadsAchieved,
                'closing_percent' => $closingPercent,
                'leads_percent' => $leadsPercent,
            ];
        });

        $members = User::query()
            ->where('role', 'staff')
            ->with('team')
            ->orderBy('name')
            ->get();

        $memberTargets = TeamMemberTarget::query()
            ->where('year', $year)
            ->where('month', 0)
            ->get()
            ->keyBy('user_id');

        $memberClosing = Conversion::query()
            ->where('status', 'Verified')
            ->where('type', 'Closing')
            ->whereYear('created_at', $year)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $memberLeads = Conversion::query()
            ->where('status', 'Verified')
            ->where('type', 'Lead')
            ->whereYear('created_at', $year)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $memberRows = $members->map(function (User $member) use ($memberTargets, $memberClosing, $memberLeads) {
            $target = $memberTargets->get($member->id);
            $targetClosing = (int) ($target?->target_closing ?? 0);
            $targetLeads = (int) ($target?->target_leads ?? 0);
            $closingAchieved = (int) ($memberClosing[$member->id] ?? 0);
            $leadsAchieved = (int) ($memberLeads[$member->id] ?? 0);

            $closingPercent = $targetClosing > 0
                ? min(100, (int) round(($closingAchieved / $targetClosing) * 100))
                : 0;
            $leadsPercent = $targetLeads > 0
                ? min(100, (int) round(($leadsAchieved / $targetLeads) * 100))
                : 0;

            return [
                'id' => $member->id,
                'name' => $member->name,
                'team' => $member->team?->team_name ?? '-',
                'target_closing' => $targetClosing,
                'target_leads' => $targetLeads,
                'closing_achieved' => $closingAchieved,
                'leads_achieved' => $leadsAchieved,
                'closing_percent' => $closingPercent,
                'leads_percent' => $leadsPercent,
            ];
        });

        return view('targets-admin', [
            'selectedYear' => $year,
            'yearOptions' => $yearOptions,
            'teamRows' => $teamRows,
            'memberRows' => $memberRows,
        ]);
    }
}
