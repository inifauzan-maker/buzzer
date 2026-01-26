<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AkademikController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['superadmin', 'akademik'], true)) {
            abort(403, 'Akses ditolak.');
        }

        $students = PublicRegistration::query()
            ->with('academicForwardedBy')
            ->where('validation_status', 'validated')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('akademik.index', [
            'students' => $students,
        ]);
    }
}
