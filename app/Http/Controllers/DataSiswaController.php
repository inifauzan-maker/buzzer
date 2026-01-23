<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DataSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user || $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak.');
        }

        $registrations = PublicRegistration::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('data-siswa', [
            'registrations' => $registrations,
        ]);
    }
}
