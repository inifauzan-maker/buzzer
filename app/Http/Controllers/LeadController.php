<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeadController extends Controller
{
    private array $channels = [
        'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'X', 'Website', 'Referensi', 'Ads/Iklan',
    ];

    private array $statuses = ['prospect', 'follow_up', 'closing', 'lost'];

    public function index(Request $request)
    {
        $this->ensureAccess($request);

        [$teamId, $status, $channel] = $this->readFilters($request);

        $leads = $this->baseQuery($teamId, $status, $channel)
            ->with(['team', 'assignee'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $funnel = $this->buildFunnel($teamId, $channel);

        $admins = $this->adminUsers();
        $teams = Team::query()->orderBy('team_name')->get(['id', 'team_name']);

        return view('leads.index', [
            'leads' => $leads,
            'funnel' => $funnel,
            'channels' => $this->channels,
            'statuses' => $this->statuses,
            'teams' => $teams,
            'admins' => $admins,
            'selectedTeamId' => $teamId,
            'selectedStatus' => $status,
            'selectedChannel' => $channel,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAccess($request);

        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:150'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:32'],
            'channel' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'in:prospect,follow_up,closing,lost'],
            'follow_up_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['created_by'] = $request->user()->id;
        $data['team_id'] = $request->user()->team_id;
        $data['last_contact_at'] = $data['status'] !== 'prospect' ? now() : null;

        Lead::create($data);

        return back()->with('status', 'Lead berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureAccess($request);

        $data = $request->validate([
            'status' => ['required', 'in:prospect,follow_up,closing,lost'],
        ]);

        $lead->forceFill([
            'status' => $data['status'],
            'last_contact_at' => now(),
        ])->save();

        return back()->with('status', 'Status lead diperbarui.');
    }

    public function followups(Request $request)
    {
        $this->ensureAccess($request);

        [$teamId, $status, $channel] = $this->readFilters($request);

        $leadIds = $this->baseQuery($teamId, $status, $channel)
            ->pluck('id');

        $followups = LeadFollowUp::query()
            ->with(['lead', 'user'])
            ->whereIn('lead_id', $leadIds)
            ->orderBy('follow_up_at')
            ->paginate(20)
            ->withQueryString();

        $today = Carbon::today();
        $reminders = LeadFollowUp::query()
            ->with('lead')
            ->whereIn('lead_id', $leadIds)
            ->whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', '<=', $today)
            ->where('status', 'planned')
            ->orderBy('follow_up_at')
            ->limit(10)
            ->get();

        $calendar = $this->buildCalendar($leadIds);

        $leadOptions = Lead::query()
            ->whereIn('id', $leadIds)
            ->orderBy('student_name')
            ->get(['id', 'student_name']);

        return view('leads.followups', [
            'followups' => $followups,
            'reminders' => $reminders,
            'calendar' => $calendar,
            'leadOptions' => $leadOptions,
            'channels' => $this->channels,
            'teams' => Team::query()->orderBy('team_name')->get(['id', 'team_name']),
            'selectedTeamId' => $teamId,
            'selectedStatus' => $status,
            'selectedChannel' => $channel,
        ]);
    }

    public function storeFollowup(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureAccess($request);

        $data = $request->validate([
            'note' => ['nullable', 'string'],
            'follow_up_at' => ['nullable', 'date'],
            'status' => ['required', 'in:planned,completed'],
        ]);

        $data['lead_id'] = $lead->id;
        $data['user_id'] = $request->user()->id;
        LeadFollowUp::create($data);

        $lead->forceFill([
            'follow_up_at' => $data['follow_up_at'] ?? $lead->follow_up_at,
            'last_contact_at' => now(),
        ])->save();

        return back()->with('status', 'Aktivitas follow-up disimpan.');
    }

    public function analytics(Request $request)
    {
        $this->ensureAccess($request);

        [$teamId, $status, $channel] = $this->readFilters($request);

        $baseQuery = $this->baseQuery($teamId, $status, $channel);

        $channelStats = (clone $baseQuery)
            ->selectRaw('channel, COUNT(*) as total, SUM(CASE WHEN status = "closing" THEN 1 ELSE 0 END) as closings')
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get();

        $projection = $this->projection($baseQuery);

        $adminPerformance = (clone $baseQuery)
            ->selectRaw('assigned_to, COUNT(*) as total, SUM(CASE WHEN status = "closing" THEN 1 ELSE 0 END) as closings')
            ->groupBy('assigned_to')
            ->with('assignee')
            ->get();

        return view('leads.analytics', [
            'channelStats' => $channelStats,
            'projection' => $projection,
            'adminPerformance' => $adminPerformance,
            'channels' => $this->channels,
            'teams' => Team::query()->orderBy('team_name')->get(['id', 'team_name']),
            'selectedTeamId' => $teamId,
            'selectedStatus' => $status,
            'selectedChannel' => $channel,
        ]);
    }

    public function whatsapp(Request $request)
    {
        $this->ensureAccess($request);

        return view('leads.whatsapp', [
            'webhookUrl' => route('leads.whatsapp.webhook'),
        ]);
    }

    public function webhook(Request $request): Response
    {
        if ($request->isMethod('get')) {
            return response('OK', 200);
        }

        return response()->noContent();
    }

    private function baseQuery(?string $teamId, ?string $status, ?string $channel)
    {
        return Lead::query()
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($channel, fn ($query) => $query->where('channel', $channel));
    }

    private function buildFunnel(?string $teamId, ?string $channel): array
    {
        $base = Lead::query()
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->when($channel, fn ($query) => $query->where('channel', $channel));

        $counts = $base
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'prospect' => (int) ($counts['prospect'] ?? 0),
            'follow_up' => (int) ($counts['follow_up'] ?? 0),
            'closing' => (int) ($counts['closing'] ?? 0),
            'lost' => (int) ($counts['lost'] ?? 0),
        ];
    }

    private function projection($baseQuery): array
    {
        $total = (clone $baseQuery)->count();
        $closings = (clone $baseQuery)->where('status', 'closing')->count();
        $open = (clone $baseQuery)->whereIn('status', ['prospect', 'follow_up'])->count();
        $rate = $total > 0 ? round(($closings / $total) * 100, 1) : 0;
        $projected = $rate > 0 ? (int) round(($rate / 100) * $open) : 0;

        return [
            'rate' => $rate,
            'projected' => $projected,
            'open' => $open,
            'total' => $total,
        ];
    }

    private function buildCalendar(Collection $leadIds): array
    {
        $month = now()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $days = [];

        $counts = LeadFollowUp::query()
            ->whereIn('lead_id', $leadIds)
            ->whereBetween('follow_up_at', [$month, $end])
            ->selectRaw('DATE(follow_up_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $cursor = $month->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $days[] = [
                'date' => $cursor->copy(),
                'total' => (int) ($counts[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return [
            'month' => $month,
            'days' => $days,
        ];
    }

    private function adminUsers()
    {
        return User::query()
            ->whereIn('role', ['superadmin', 'leader', 'staff', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    private function readFilters(Request $request): array
    {
        $teamId = $request->query('team_id');
        $status = $request->query('status');
        $channel = $request->query('channel');

        return [
            $teamId !== '' ? $teamId : null,
            $status !== '' ? $status : null,
            $channel !== '' ? $channel : null,
        ];
    }

    private function ensureAccess(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['superadmin', 'leader', 'staff'], true)) {
            abort(403, 'Akses ditolak.');
        }
    }
}
