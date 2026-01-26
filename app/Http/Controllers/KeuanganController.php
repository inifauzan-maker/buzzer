<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KeuanganController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureRoles($request, ['superadmin', 'keuangan']);

        $registrations = PublicRegistration::query()
            ->with('programItem')
            ->where('validation_status', 'validated')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('keuangan.index', [
            'registrations' => $registrations,
        ]);
    }

    public function uploadProof(Request $request, PublicRegistration $registration): RedirectResponse
    {
        $this->ensureRoles($request, ['superadmin', 'keuangan']);

        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'payment_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $path = $data['payment_proof']->store('payment-proofs', 'public');

        $registration->forceFill([
            'payment_proof_path' => $path,
            'payment_amount' => $data['payment_amount'] ?? $registration->payment_amount,
            'payment_status' => 'submitted',
            'payment_submitted_at' => now(),
        ])->save();

        return back()->with('status', 'Bukti pembayaran tersimpan, menunggu verifikasi.');
    }

    public function verifyPayment(Request $request, PublicRegistration $registration): RedirectResponse
    {
        $this->ensureRoles($request, ['superadmin', 'keuangan']);

        if (! $registration->payment_proof_path) {
            return back()->withErrors([
                'payment_proof' => 'Bukti pembayaran belum diunggah.',
            ]);
        }

        $invoiceNumber = $registration->payment_invoice_number ?? $this->makePaymentInvoiceNumber($registration);

        $registration->forceFill([
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'payment_verified_by' => $request->user()->id,
            'payment_invoice_number' => $invoiceNumber,
            'payment_invoice_issued_at' => now(),
            'academic_forwarded_at' => now(),
            'academic_forwarded_by' => $request->user()->id,
        ])->save();

        return back()->with('status', 'Pembayaran diverifikasi dan invoice pembayaran diterbitkan.');
    }

    public function paymentInvoice(Request $request, PublicRegistration $registration)
    {
        $this->ensureRoles($request, ['superadmin', 'keuangan']);

        $program = $registration->programItem;
        $invoiceNumber = $registration->payment_invoice_number ?? $this->makePaymentInvoiceNumber($registration);
        $paymentAmount = (int) ($registration->payment_amount ?? $registration->invoice_total ?? 0);
        $paymentDate = $registration->payment_verified_at ?? $registration->payment_submitted_at ?? now();

        $payload = [
            'registration' => $registration,
            'programName' => $program?->nama ?? $registration->program ?? 'Program Bimbel',
            'invoiceNumber' => $invoiceNumber,
            'paymentDate' => $paymentDate instanceof Carbon ? $paymentDate : Carbon::parse($paymentDate),
            'paymentAmount' => $paymentAmount,
            'paymentSystem' => strtoupper((string) $registration->payment_system),
            'paymentStatus' => strtoupper((string) ($registration->payment_status ?? 'PAID')),
        ];

        if ($request->boolean('preview')) {
            return view('invoices.payment', $payload);
        }

        $pdf = Pdf::loadView('invoices.payment', $payload);
        $filename = $invoiceNumber.'.pdf';

        return $pdf->download($filename);
    }

    private function ensureRoles(Request $request, array $roles): void
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function makePaymentInvoiceNumber(PublicRegistration $registration): string
    {
        $date = now()->format('Ymd');
        $suffix = str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT);
        return 'PAY-'.$date.'-'.$suffix;
    }
}
