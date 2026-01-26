<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use App\Models\ProdukItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DataSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureSuperadmin($request);

        $registrations = PublicRegistration::query()
            ->with('programItem')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('data-siswa', [
            'registrations' => $registrations,
        ]);
    }

    public function validateRegistration(Request $request, PublicRegistration $registration): RedirectResponse
    {
        $this->ensureSuperadmin($request);

        $data = $request->validate([
            'invoice_total' => ['nullable', 'integer', 'min:0'],
        ]);

        $program = $registration->programItem;
        $invoiceTotal = $data['invoice_total'] ?? $registration->invoice_total ?? $this->calculateInvoiceTotal($program);

        if ($invoiceTotal === null) {
            return back()->withErrors([
                'invoice_total' => 'Total invoice belum tersedia. Isi manual jika program belum ada.',
            ]);
        }

        $invoiceNumber = $registration->invoice_number ?? $this->makeInvoiceNumber($registration);
        $updates = [
            'validation_status' => 'validated',
            'invoice_number' => $invoiceNumber,
            'invoice_total' => $invoiceTotal,
            'invoice_sent_to' => $registration->parent_phone,
        ];

        if (! $registration->validated_at) {
            $updates['validated_at'] = now();
            $updates['validated_by'] = $request->user()->id;
        }

        $registration->forceFill($updates)->save();

        return back()->with('status', 'Data siswa tervalidasi dan invoice dibuat.');
    }

    public function sendInvoice(Request $request, PublicRegistration $registration): RedirectResponse
    {
        $this->ensureSuperadmin($request);

        if ($registration->validation_status !== 'validated') {
            return back()->withErrors([
                'invoice_total' => 'Validasi data siswa terlebih dahulu.',
            ]);
        }

        if (! $registration->invoice_total) {
            return back()->withErrors([
                'invoice_total' => 'Invoice belum dibuat.',
            ]);
        }

        $phone = $this->normalizePhone(
            $registration->parent_phone ?? $registration->phone_number ?? $registration->phone
        );

        if (! $phone) {
            return back()->withErrors([
                'parent_phone' => 'Nomor WA orang tua/wali tidak ditemukan.',
            ]);
        }

        $invoiceNumber = $registration->invoice_number ?? $this->makeInvoiceNumber($registration);
        $message = $this->buildInvoiceMessage($registration, $registration->programItem, $invoiceNumber);
        $waUrl = 'https://wa.me/'.$phone.'?text='.urlencode($message);

        $registration->forceFill([
            'invoice_number' => $invoiceNumber,
            'invoice_sent_at' => now(),
            'invoice_sent_to' => $phone,
        ])->save();

        return redirect()->away($waUrl);
    }

    public function invoice(Request $request, PublicRegistration $registration)
    {
        $this->ensureSuperadmin($request);

        $program = $registration->programItem;
        $invoiceNumber = $registration->invoice_number ?? $this->makeInvoiceNumber($registration);
        $rawInvoiceTotal = (int) ($registration->invoice_total ?? $this->calculateInvoiceTotal($program) ?? 0);
        $totalProgram = max(0, $rawInvoiceTotal);

        $biayaDaftar = (int) ($program?->biaya_daftar ?? 0);
        $biayaPendidikan = (int) ($program?->biaya_pendidikan ?? 0);
        $discount = (int) ($program?->discount ?? 0);

        $paymentSystem = strtolower((string) $registration->payment_system);
        $type = strtolower((string) $request->query('type', $paymentSystem === 'angsuran' ? 'angsuran' : 'lunas'));
        $isAngsuran = $type === 'angsuran';

        $invoiceDateInput = $request->query('invoice_date');
        $invoiceDate = $invoiceDateInput ? Carbon::parse($invoiceDateInput) : now();

        $dueDateInput = $request->query('due_date');
        $dueDate = $dueDateInput ? Carbon::parse($dueDateInput) : $invoiceDate->copy()->addDays(7);

        $installmentTotal = (int) $request->query('installment_total', $rawInvoiceTotal);
        $installmentLabel = (string) $request->query('installment_label', '');
        if ($installmentLabel === '') {
            $installmentNo = (int) $request->query('installment_no', 1);
            $installmentLabel = 'Angsuran Ke-'.$installmentNo;
        }
        $invoiceTotal = $isAngsuran ? $installmentTotal : $rawInvoiceTotal;
        $remainingBalance = $request->filled('remaining_balance')
            ? (int) $request->query('remaining_balance')
            : (int) ($registration->remaining_balance ?? 0);

        $paymentStatus = strtoupper((string) $request->query('status', $isAngsuran ? 'ANGSURAN' : 'LUNAS'));
        $programName = $program?->nama ?? $registration->program ?? 'Program Bimbel';

        if ($request->filled('remaining_balance')) {
            $registration->forceFill([
                'remaining_balance' => $remainingBalance,
            ])->save();
        }

        $payload = [
            'registration' => $registration,
            'programName' => $programName,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $invoiceDate,
            'dueDate' => $dueDate,
            'isAngsuran' => $isAngsuran,
            'installmentLabel' => $installmentLabel,
            'installmentTotal' => $installmentTotal,
            'invoiceTotal' => $invoiceTotal,
            'totalProgram' => $totalProgram,
            'remainingBalance' => $remainingBalance,
            'biayaDaftar' => $biayaDaftar,
            'biayaPendidikan' => $biayaPendidikan,
            'discount' => $discount,
            'paymentStatus' => $paymentStatus,
        ];

        if ($request->boolean('preview')) {
            return view('invoices.show', $payload);
        }

        $pdf = Pdf::loadView('invoices.show', $payload);
        $filename = $invoiceNumber.'.pdf';

        return $pdf->download($filename);
    }

    private function ensureSuperadmin(Request $request): void
    {
        $user = $request->user();
        if (! $user || $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak.');
        }
    }

    private function calculateInvoiceTotal(?ProdukItem $program): ?int
    {
        if (! $program) {
            return null;
        }

        $total = (int) $program->biaya_daftar + (int) $program->biaya_pendidikan - (int) $program->discount;
        return max(0, $total);
    }

    private function makeInvoiceNumber(PublicRegistration $registration): string
    {
        $date = now()->format('Ymd');
        $suffix = str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT);
        return 'INV-'.$date.'-'.$suffix;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $phone);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    private function buildInvoiceMessage(
        PublicRegistration $registration,
        ?ProdukItem $program,
        string $invoiceNumber
    ): string {
        $programName = $program?->nama ?? $registration->program ?? 'Program Bimbel';
        $biayaDaftar = $program?->biaya_daftar ?? 0;
        $biayaPendidikan = $program?->biaya_pendidikan ?? 0;
        $discount = $program?->discount ?? 0;
        $total = (int) $registration->invoice_total;

        $parentName = $registration->parent_name ?: 'Orang Tua/Wali';
        $studentName = $registration->full_name ?: $registration->name;

        return implode("\n", array_filter([
            'Halo '.$parentName.',',
            '',
            'Berikut invoice pendaftaran untuk '.$studentName.'.',
            'No Invoice: '.$invoiceNumber,
            'Program: '.$programName,
            'Lokasi Belajar: '.$registration->study_location,
            'Jadwal: '.$registration->study_day.' - '.$registration->study_time,
            'Sistem Pembayaran: '.$registration->payment_system,
            '',
            'Rincian biaya:',
            '- Biaya daftar: Rp '.number_format((int) $biayaDaftar, 0, ',', '.'),
            '- Biaya pendidikan: Rp '.number_format((int) $biayaPendidikan, 0, ',', '.'),
            '- Diskon: Rp '.number_format((int) $discount, 0, ',', '.'),
            'Total: Rp '.number_format($total, 0, ',', '.'),
            '',
            'Terima kasih.',
        ]));
    }
}
