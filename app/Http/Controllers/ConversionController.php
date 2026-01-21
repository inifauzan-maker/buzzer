<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use App\Services\PointCalculator;
use App\Services\SystemActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Conversion::with(['user', 'team'])->latest();

        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'leader') {
            $query->where('team_id', $user->team_id);
        }

        $conversions = $query->paginate(15);

        return view('conversions', [
            'conversions' => $conversions,
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

        return view('conversions-create', [
            'teams' => $teams,
            'users' => $users,
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
            'type' => 'required|in:Lead,Closing',
            'amount' => 'required|integer|min:1',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file')
                ->store('proofs', 'public');
        }

        if ($user->role === 'staff') {
            $data['user_id'] = $user->id;
            $data['team_id'] = $user->team_id;
        }

        $data['status'] = 'Pending';

        $conversion = Conversion::create($data);

        SystemActivityLogger::log($user, 'Mengirim konversi '.$conversion->type.' sejumlah '.$conversion->amount.' untuk verifikasi.');

        return redirect()
            ->route('conversions.index')
            ->with('status', 'Data konversi berhasil dikirim untuk verifikasi.');
    }

    public function verify(Conversion $conversion)
    {
        $user = request()->user();

        if ($user->role === 'leader' && $conversion->team_id !== $user->team_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->role === 'leader') {
            if ($conversion->status !== 'Pending') {
                return redirect()
                    ->route('conversions.index')
                    ->withErrors(['conversion' => 'Konversi sudah diproses.']);
            }

            $conversion->status = 'Reviewed';
            $conversion->computed_points = null;
            $conversion->save();

            SystemActivityLogger::log($user, 'Review konversi '.$conversion->type.' (ID '.$conversion->id.').');

            return redirect()
                ->route('conversions.index')
                ->with('status', 'Konversi berhasil direview.');
        }

        if ($conversion->status !== 'Reviewed') {
            return redirect()
                ->route('conversions.index')
                ->withErrors(['conversion' => 'Konversi harus direview leader terlebih dahulu.']);
        }

        $conversion->status = 'Verified';
        $conversion->computed_points = PointCalculator::conversion($conversion);
        $conversion->save();

        SystemActivityLogger::log($user, 'Verifikasi konversi '.$conversion->type.' (ID '.$conversion->id.').');

        Notification::create([
            'user_id' => $conversion->user_id,
            'title' => 'Konversi disetujui',
            'message' => 'Konversi '.$conversion->type.' sejumlah '.$conversion->amount.' disetujui. Poin: '.number_format((float) $conversion->computed_points, 2),
            'type' => 'conversion_approved',
        ]);

        return redirect()
            ->route('conversions.index')
            ->with('status', 'Konversi berhasil diverifikasi admin.');
    }

    public function reject(Conversion $conversion)
    {
        $user = request()->user();

        if ($user->role === 'leader' && $conversion->team_id !== $user->team_id) {
            abort(403, 'Akses ditolak.');
        }

        $conversion->status = 'Rejected';
        $conversion->computed_points = null;
        $conversion->save();

        SystemActivityLogger::log($user, 'Menolak konversi '.$conversion->type.' (ID '.$conversion->id.').');

        Notification::create([
            'user_id' => $conversion->user_id,
            'title' => 'Konversi ditolak',
            'message' => 'Konversi '.$conversion->type.' sejumlah '.$conversion->amount.' ditolak. Silakan cek kembali data/bukti.',
            'type' => 'conversion_rejected',
        ]);

        return redirect()
            ->route('conversions.index')
            ->with('status', 'Konversi ditolak.');
    }
}
