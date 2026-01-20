<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Team;
use App\Models\User;
use App\Services\PointCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ActivityLog::with(['user', 'team'])->latest();

        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'leader') {
            $query->where('team_id', $user->team_id);
        }

        $activities = $query->paginate(15);

        return view('activities', [
            'activities' => $activities,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'staff') {
            $teams = Team::where('id', $user->team_id)->get();
            $users = User::where('id', $user->id)->get();
        } elseif ($user->role === 'leader') {
            $teams = Team::where('id', $user->team_id)->get();
            $users = User::where('team_id', $user->team_id)->orderBy('name')->get();
        } else {
            $teams = Team::orderBy('team_name')->get();
            $users = User::orderBy('name')->get();
        }

        return view('activities-create', [
            'teams' => $teams,
            'users' => $users,
            'platforms' => $this->platforms(),
            'lockTeam' => $user->role !== 'superadmin',
            'lockUser' => $user->role === 'staff',
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->when(
                    $user->role === 'leader',
                    fn ($rule) => $rule->where('team_id', $user->team_id)
                ),
            ],
            'team_id' => [
                'required',
                Rule::exists('teams', 'id')->when(
                    $user->role === 'leader',
                    fn ($rule) => $rule->where('id', $user->team_id)
                ),
            ],
            'platform' => 'required|in:IG,FB,TT,YT,Blog,WA',
            'post_url' => 'required|string|max:2048',
            'post_date' => 'required|date',
            'likes' => 'nullable|integer|min:0',
            'comments' => 'nullable|integer|min:0',
            'shares' => 'nullable|integer|min:0',
            'saves' => 'nullable|integer|min:0',
            'reach' => 'nullable|integer|min:0',
            'evidence_screenshot' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('evidence_screenshot')) {
            $data['evidence_screenshot'] = $request->file('evidence_screenshot')
                ->store('evidence', 'public');
        }

        $data['platform_post_id'] = $this->extractPostId($data['platform'], $data['post_url']);
        $data['normalized_post_url'] = $this->normalizePostUrl($data['post_url']);

        if ($data['platform_post_id']) {
            $duplicate = ActivityLog::where('team_id', $data['team_id'])
                ->where('platform', $data['platform'])
                ->where('platform_post_id', $data['platform_post_id'])
                ->exists();

            if ($duplicate) {
                return back()
                    ->withErrors(['post_url' => 'Postingan ini terdeteksi duplikat.'])
                    ->withInput();
            }
        } elseif ($data['normalized_post_url']) {
            $duplicate = ActivityLog::where('team_id', $data['team_id'])
                ->where('platform', $data['platform'])
                ->where('normalized_post_url', $data['normalized_post_url'])
                ->exists();

            if ($duplicate) {
                return back()
                    ->withErrors(['post_url' => 'Postingan ini terdeteksi duplikat (URL sama).'])
                    ->withInput();
            }
        }

        if ($user->role === 'staff') {
            $data['user_id'] = $user->id;
            $data['team_id'] = $user->team_id;
        }

        $data['likes'] = $data['likes'] ?? 0;
        $data['comments'] = $data['comments'] ?? 0;
        $data['shares'] = $data['shares'] ?? 0;
        $data['saves'] = $data['saves'] ?? 0;
        $data['reach'] = $data['reach'] ?? 0;
        $data['status'] = 'Pending';
        $data['admin_grade'] = 'B';

        ActivityLog::create($data);

        return redirect()
            ->route('activities.index')
            ->with('status', 'Aktivitas baru berhasil dikirim untuk verifikasi.');
    }

    public function verify(Request $request, ActivityLog $activity)
    {
        $user = $request->user();

        if ($user->role === 'leader' && $activity->team_id !== $user->team_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->role === 'leader') {
            if ($activity->status !== 'Pending') {
                return redirect()
                    ->route('activities.index')
                    ->withErrors(['activity' => 'Aktivitas sudah diproses.']);
            }

            $activity->status = 'Reviewed';
            $activity->computed_points = null;
            $activity->save();

            return redirect()
                ->route('activities.index')
                ->with('status', 'Aktivitas berhasil direview.');
        }

        if ($activity->status !== 'Reviewed') {
            return redirect()
                ->route('activities.index')
                ->withErrors(['activity' => 'Aktivitas harus direview leader terlebih dahulu.']);
        }

        $data = $request->validate([
            'admin_grade' => 'required|in:A,B,C',
        ]);

        $activity->admin_grade = $data['admin_grade'];
        $activity->status = 'Verified';
        $activity->computed_points = PointCalculator::activity($activity);
        $activity->save();

        return redirect()
            ->route('activities.index')
            ->with('status', 'Aktivitas berhasil diverifikasi admin.');
    }

    public function reject(ActivityLog $activity)
    {
        $user = request()->user();

        if ($user->role === 'leader' && $activity->team_id !== $user->team_id) {
            abort(403, 'Akses ditolak.');
        }

        $activity->status = 'Rejected';
        $activity->computed_points = null;
        $activity->save();

        return redirect()
            ->route('activities.index')
            ->with('status', 'Aktivitas ditolak.');
    }

    private function platforms(): array
    {
        return ['IG', 'FB', 'TT', 'YT', 'Blog', 'WA'];
    }

    private function extractPostId(string $platform, string $url): ?string
    {
        $platform = strtoupper($platform);
        $url = trim($url);

        if ($platform === 'IG') {
            if (preg_match('~/(?:p|reel|tv)/([A-Za-z0-9_-]+)~', $url, $match)) {
                return $match[1];
            }
        }

        if ($platform === 'TT') {
            if (preg_match('~/video/(\d+)~', $url, $match)) {
                return $match[1];
            }
        }

        if ($platform === 'YT') {
            $parsed = parse_url($url);
            if (! empty($parsed['query'])) {
                parse_str($parsed['query'], $query);
                if (! empty($query['v'])) {
                    return $query['v'];
                }
            }

            if (preg_match('~youtu\.be/([A-Za-z0-9_-]+)~', $url, $match)) {
                return $match[1];
            }

            if (preg_match('~/shorts/([A-Za-z0-9_-]+)~', $url, $match)) {
                return $match[1];
            }
        }

        if ($platform === 'FB') {
            if (preg_match('~/(?:posts|reel|videos)/(\d+)~', $url, $match)) {
                return $match[1];
            }
        }

        return null;
    }

    private function normalizePostUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $host = preg_replace('/^www\./', '', $host);
        $path = $parts['path'] ?? '';
        $path = rtrim($path, '/');

        return $host.$path;
    }
}
