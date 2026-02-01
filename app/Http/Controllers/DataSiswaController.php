<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use App\Models\ProdukItem;
use App\Models\School;
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

    public function dashboard(Request $request): View
    {
        $this->ensureSuperadmin($request);

        $fixedYears = collect(range(2025, 2028));
        $dataYears = PublicRegistration::query()
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->filter()
            ->values();

        $yearOptions = $fixedYears
            ->merge($dataYears)
            ->unique()
            ->sortDesc()
            ->values();

        if ($yearOptions->isEmpty()) {
            $yearOptions = collect([now()->year]);
        }

        $filterYear = (int) $request->query('filter_year', now()->year);
        if (! $yearOptions->contains($filterYear)) {
            $filterYear = (int) $yearOptions->first();
        }
        $filterMonth = (int) $request->query('filter_month', 0);
        $filterLocation = trim((string) $request->query('filter_location', ''));
        $filterProgram = trim((string) $request->query('filter_program', ''));
        $filterKode = trim((string) $request->query('filter_kode', ''));

        $baseQuery = PublicRegistration::query()
            ->when($filterYear > 0, fn ($query) => $query->whereYear('created_at', $filterYear))
            ->when($filterMonth > 0, fn ($query) => $query->whereMonth('created_at', $filterMonth))
            ->when($filterLocation !== '', fn ($query) => $query->where('study_location', $filterLocation))
            ->when($filterProgram !== '', fn ($query) => $query->where('program', $filterProgram))
            ->when($filterKode !== '', function ($query) use ($filterKode) {
                $query->whereHas('programItem', function ($builder) use ($filterKode) {
                    $builder->where('kode_1', 'like', '%'.$filterKode.'%')
                        ->orWhere('kode_2', 'like', '%'.$filterKode.'%')
                        ->orWhere('kode_3', 'like', '%'.$filterKode.'%')
                        ->orWhere('kode_4', 'like', '%'.$filterKode.'%');
                });
            });

        [$chartLabels, $chartValues, $chartMax, $chartRangeLabel] = $this->buildRegistrationChart(
            $filterYear,
            $filterMonth,
            $filterLocation,
            $filterProgram,
            $filterKode
        );

        $topSchools = (clone $baseQuery)
            ->selectRaw('school_name, COUNT(*) as total')
            ->whereNotNull('school_name')
            ->where('school_name', '!=', '')
            ->groupBy('school_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $programTotals = (clone $baseQuery)
            ->selectRaw('program, COUNT(*) as total')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->groupBy('program')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $regionCounts = (clone $baseQuery)
            ->select('province')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->get()
            ->map(fn ($row) => $this->resolveRegion($row->province))
            ->countBy()
            ->all();

        $regions = [
            ['key' => 'SUMATERA', 'label' => 'Sumatera'],
            ['key' => 'JAWA', 'label' => 'Jawa'],
            ['key' => 'KALIMANTAN', 'label' => 'Kalimantan'],
            ['key' => 'SULAWESI', 'label' => 'Sulawesi'],
            ['key' => 'BALI_NUSRA', 'label' => 'Bali & Nusra'],
            ['key' => 'MALUKU_PAPUA', 'label' => 'Maluku & Papua'],
        ];

        $regionMax = max(1, ...(array_values($regionCounts) ?: [0]));
        $regionData = collect($regions)->map(function (array $region) use ($regionCounts, $regionMax) {
            $total = (int) ($regionCounts[$region['key']] ?? 0);
            $heat = $total > 0 ? 0.25 + (0.65 * ($total / $regionMax)) : 0.12;

            return [
                'key' => $region['key'],
                'label' => $region['label'],
                'total' => $total,
                'heat' => $heat,
            ];
        });

        $locationOptions = PublicRegistration::query()
            ->select('study_location')
            ->whereNotNull('study_location')
            ->where('study_location', '!=', '')
            ->distinct()
            ->orderBy('study_location')
            ->pluck('study_location');

        $programOptions = PublicRegistration::query()
            ->select('program')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        $kodeOptions = $this->kodeOptions();

        return view('data-siswa-dashboard', [
            'yearOptions' => $yearOptions,
            'filterYear' => $filterYear,
            'filterMonth' => $filterMonth,
            'filterLocation' => $filterLocation,
            'filterProgram' => $filterProgram,
            'filterKode' => $filterKode,
            'locationOptions' => $locationOptions,
            'programOptions' => $programOptions,
            'kodeOptions' => $kodeOptions,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'chartMax' => $chartMax,
            'chartRangeLabel' => $chartRangeLabel,
            'topSchools' => $topSchools,
            'programTotals' => $programTotals,
            'regionData' => $regionData,
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

    public function exportCsv(Request $request)
    {
        $this->ensureSuperadmin($request);

        $registrations = PublicRegistration::with('programItem')
            ->orderByDesc('created_at')
            ->get();

        $headers = [
            'id',
            'created_at',
            'full_name',
            'birth_place',
            'birth_date',
            'school_name',
            'class_level',
            'major',
            'phone_number',
            'parent_name',
            'parent_phone',
            'study_location',
            'program',
            'program_id',
            'kode_1',
            'kode_2',
            'kode_3',
            'kode_4',
            'province',
            'city',
            'district',
            'subdistrict',
            'postal_code',
            'payment_system',
            'study_day',
            'study_time',
            'validation_status',
            'invoice_number',
            'invoice_total',
            'remaining_balance',
            'payment_status',
        ];

        $filename = 'data-siswa-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($registrations, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($registrations as $reg) {
                $programItem = $reg->programItem;
                fputcsv($handle, [
                    $reg->id,
                    optional($reg->created_at)->format('Y-m-d H:i:s'),
                    $reg->full_name ?? $reg->name,
                    $reg->birth_place,
                    optional($reg->birth_date)->format('Y-m-d'),
                    $reg->school_name,
                    $reg->class_level,
                    $reg->major,
                    $reg->phone_number ?? $reg->phone,
                    $reg->parent_name,
                    $reg->parent_phone,
                    $reg->study_location,
                    $reg->program,
                    $reg->program_id,
                    $programItem?->kode_1,
                    $programItem?->kode_2,
                    $programItem?->kode_3,
                    $programItem?->kode_4,
                    $reg->province,
                    $reg->city,
                    $reg->district,
                    $reg->subdistrict,
                    $reg->postal_code,
                    $reg->payment_system,
                    $reg->study_day,
                    $reg->study_time,
                    $reg->validation_status,
                    $reg->invoice_number,
                    $reg->invoice_total,
                    $reg->remaining_balance,
                    $reg->payment_status,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function templateCsv(Request $request)
    {
        $this->ensureSuperadmin($request);

        $headers = [
            'full_name',
            'birth_place',
            'birth_date',
            'school_name',
            'class_level',
            'major',
            'phone_number',
            'parent_name',
            'parent_phone',
            'study_location',
            'program',
            'program_id',
            'kode_1',
            'kode_2',
            'kode_3',
            'kode_4',
            'province',
            'city',
            'district',
            'subdistrict',
            'postal_code',
            'payment_system',
            'study_day',
            'study_time',
        ];

        $sample = [
            'Budi Santoso',
            'Bandung',
            '2007-02-14',
            'SMA 1 BANDUNG',
            'X',
            'IPA',
            '6281234567890',
            'Siti Santoso',
            '628111222333',
            'Bandung',
            'SR INT SNBP',
            '',
            'SR',
            'TM',
            'R',
            'SMA',
            'JAWA BARAT',
            'BANDUNG',
            'Coblong',
            'Dago',
            '40135',
            'Lunas',
            'Sabtu',
            '09.00 - 13.00',
        ];

        $filename = 'template-data-siswa.csv';

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin($request);

        $request->validate([
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt'],
            'csv_text' => ['nullable', 'string'],
        ]);

        $csvText = trim((string) $request->input('csv_text', ''));
        $file = $request->file('csv_file');
        if (! $file && $csvText === '') {
            return back()->withErrors(['csv_file' => 'Masukkan file CSV atau tempelkan isi CSV.']);
        }

        $handle = null;
        if ($file) {
            $handle = fopen($file->getRealPath(), 'r');
        } else {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $csvText);
            rewind($handle);
        }

        if (! $handle) {
            return back()->withErrors(['csv_file' => 'File CSV tidak bisa dibaca.']);
        }

        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Header CSV tidak ditemukan.']);
        }

        $headerMap = $this->csvHeaderMap();
        $columns = array_map(function ($header) use ($headerMap) {
            $normalized = $this->normalizeCsvHeader($header);
            return $headerMap[$normalized] ?? null;
        }, $headerRow);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $payload = [];
            foreach ($columns as $index => $field) {
                if (! $field) {
                    continue;
                }
                $payload[$field] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }

            $fullName = $payload['full_name'] ?? $payload['name'] ?? '';
            if ($fullName === '') {
                $skipped++;
                $errors[] = 'Baris tanpa nama lengkap dilewati.';
                continue;
            }

            $payload['full_name'] = $fullName;
            $payload['name'] = $fullName;

            $phoneNumber = $this->normalizePhone($payload['phone_number'] ?? $payload['phone'] ?? '');
            if ($phoneNumber) {
                $payload['phone_number'] = $phoneNumber;
                $payload['phone'] = $phoneNumber;
            }

            $parentPhone = $this->normalizePhone($payload['parent_phone'] ?? '');
            if ($parentPhone) {
                $payload['parent_phone'] = $parentPhone;
            }

            if (! empty($payload['birth_date'])) {
                try {
                    $payload['birth_date'] = Carbon::parse($payload['birth_date'])->format('Y-m-d');
                } catch (\Throwable $e) {
                    $payload['birth_date'] = null;
                }
            }

            if (! empty($payload['school_name'])) {
                $schoolName = strtoupper($payload['school_name']);
                $payload['school_name'] = $schoolName;
                $school = School::query()->whereRaw('UPPER(name) = ?', [$schoolName])->first();
                if (! $school) {
                    $school = School::create(['name' => $schoolName]);
                }
                $payload['school_id'] = $school->id;
            }

            $programId = $payload['program_id'] ?? null;
            $programItem = null;
            if ($programId) {
                $programItem = ProdukItem::find((int) $programId);
            }

            if (! $programItem) {
                $kode = $payload['kode_1'] ?? $payload['kode_2'] ?? $payload['kode_3'] ?? $payload['kode_4'] ?? null;
                if ($kode) {
                    $programItem = ProdukItem::query()
                        ->where('kode_1', 'like', '%'.$kode.'%')
                        ->orWhere('kode_2', 'like', '%'.$kode.'%')
                        ->orWhere('kode_3', 'like', '%'.$kode.'%')
                        ->orWhere('kode_4', 'like', '%'.$kode.'%')
                        ->first();
                }
            }

            if (! $programItem && ! empty($payload['program'])) {
                $programItem = ProdukItem::query()
                    ->where('nama', $payload['program'])
                    ->first();
            }

            if ($programItem) {
                $payload['program_id'] = $programItem->id;
                $payload['program'] = $programItem->nama;
            }

            if (! empty($payload['payment_system'])) {
                $payload['payment_system'] = ucfirst(strtolower($payload['payment_system']));
            }

            try {
                PublicRegistration::create($payload);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = 'Gagal import: '.$fullName;
            }
        }

        fclose($handle);

        $message = 'Import selesai. Berhasil: '.$imported.', Gagal: '.$skipped.'.';

        if ($errors) {
            return back()->with('status', $message)->withErrors($errors);
        }

        return back()->with('status', $message);
    }

    private function ensureSuperadmin(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['superadmin', 'leader'], true)) {
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

    private function normalizeCsvHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9\s_]/', '', $normalized);
        $normalized = str_replace(' ', '_', $normalized);
        $normalized = preg_replace('/_+/', '_', $normalized);

        return $normalized;
    }

    private function csvHeaderMap(): array
    {
        return [
            'full_name' => 'full_name',
            'nama_lengkap' => 'full_name',
            'nama' => 'full_name',
            'birth_place' => 'birth_place',
            'tempat_lahir' => 'birth_place',
            'birth_date' => 'birth_date',
            'tanggal_lahir' => 'birth_date',
            'school_name' => 'school_name',
            'asal_sekolah' => 'school_name',
            'class_level' => 'class_level',
            'kelas' => 'class_level',
            'major' => 'major',
            'jurusan' => 'major',
            'phone_number' => 'phone_number',
            'nomor_hp' => 'phone_number',
            'nomer_hp' => 'phone_number',
            'parent_name' => 'parent_name',
            'nama_orang_tua' => 'parent_name',
            'nama_orang_tuawali' => 'parent_name',
            'parent_phone' => 'parent_phone',
            'nomor_wa' => 'parent_phone',
            'nomor_wa_orang_tua' => 'parent_phone',
            'study_location' => 'study_location',
            'lokasi_belajar' => 'study_location',
            'program' => 'program',
            'program_bimbel' => 'program',
            'program_kelas' => 'program',
            'program_id' => 'program_id',
            'kode_1' => 'kode_1',
            'kode_2' => 'kode_2',
            'kode_3' => 'kode_3',
            'kode_4' => 'kode_4',
            'province' => 'province',
            'provinsi' => 'province',
            'city' => 'city',
            'kota' => 'city',
            'district' => 'district',
            'kecamatan' => 'district',
            'subdistrict' => 'subdistrict',
            'kelurahan' => 'subdistrict',
            'postal_code' => 'postal_code',
            'kode_pos' => 'postal_code',
            'payment_system' => 'payment_system',
            'sistem_pembayaran' => 'payment_system',
            'study_day' => 'study_day',
            'hari_belajar' => 'study_day',
            'study_time' => 'study_time',
            'jam_belajar' => 'study_time',
        ];
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

    private function buildRegistrationChart(
        int $year,
        int $month,
        ?string $location = null,
        ?string $program = null,
        ?string $kode = null
    ): array {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $baseQuery = PublicRegistration::query()
            ->when($location !== null && $location !== '', fn ($query) => $query->where('study_location', $location))
            ->when($program !== null && $program !== '', fn ($query) => $query->where('program', $program))
            ->when($kode !== null && $kode !== '', function ($query) use ($kode) {
                $query->whereHas('programItem', function ($builder) use ($kode) {
                    $builder->where('kode_1', 'like', '%'.$kode.'%')
                        ->orWhere('kode_2', 'like', '%'.$kode.'%')
                        ->orWhere('kode_3', 'like', '%'.$kode.'%')
                        ->orWhere('kode_4', 'like', '%'.$kode.'%');
                });
            });

        if ($month > 0) {
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = (clone $start)->endOfMonth();

            $totals = (clone $baseQuery)
                ->selectRaw('DATE(created_at) as reg_date, COUNT(*) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('reg_date')
                ->pluck('total', 'reg_date');

            $labels = [];
            $values = [];
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $dateKey = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('j');
                $values[] = (int) ($totals[$dateKey] ?? 0);
                $cursor->addDay();
            }

            $max = max(1, ...$values);
            $rangeLabel = $monthNames[$month].' '.$year;

            return [$labels, $values, $max, $rangeLabel];
        }

        $totals = (clone $baseQuery)
            ->selectRaw('MONTH(created_at) as reg_month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('reg_month')
            ->pluck('total', 'reg_month');

        $labels = [];
        $values = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $monthNames[$i];
            $values[] = (int) ($totals[$i] ?? 0);
        }

        $max = max(1, ...$values);
        $rangeLabel = 'Tahun '.$year;

        return [$labels, $values, $max, $rangeLabel];
    }

    private function resolveRegion(?string $province): string
    {
        $normalized = strtoupper(trim((string) $province));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        $sumatera = [
            'ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT', 'RIAU', 'KEPULAUAN RIAU', 'JAMBI',
            'SUMATERA SELATAN', 'BENGKULU', 'LAMPUNG', 'KEPULAUAN BANGKA BELITUNG',
            'BANGKA BELITUNG', 'BABEL',
        ];
        $jawa = [
            'DKI JAKARTA', 'JAKARTA', 'BANTEN', 'JAWA BARAT', 'JAWA TENGAH', 'JAWA TIMUR',
            'DI YOGYAKARTA', 'DAERAH ISTIMEWA YOGYAKARTA', 'DIY', 'YOGYAKARTA',
        ];
        $kalimantan = [
            'KALIMANTAN BARAT', 'KALIMANTAN TENGAH', 'KALIMANTAN SELATAN',
            'KALIMANTAN TIMUR', 'KALIMANTAN UTARA',
        ];
        $sulawesi = [
            'SULAWESI UTARA', 'GORONTALO', 'SULAWESI TENGAH', 'SULAWESI BARAT',
            'SULAWESI SELATAN', 'SULAWESI TENGGARA',
        ];
        $balinusra = [
            'BALI', 'NUSA TENGGARA BARAT', 'NUSA TENGGARA TIMUR', 'NTB', 'NTT',
        ];
        $malpap = [
            'MALUKU', 'MALUKU UTARA', 'PAPUA', 'PAPUA BARAT', 'PAPUA BARAT DAYA',
            'PAPUA TENGAH', 'PAPUA PEGUNUNGAN', 'PAPUA SELATAN',
        ];

        if (in_array($normalized, $sumatera, true)) {
            return 'SUMATERA';
        }
        if (in_array($normalized, $jawa, true)) {
            return 'JAWA';
        }
        if (in_array($normalized, $kalimantan, true)) {
            return 'KALIMANTAN';
        }
        if (in_array($normalized, $sulawesi, true)) {
            return 'SULAWESI';
        }
        if (in_array($normalized, $balinusra, true)) {
            return 'BALI_NUSRA';
        }
        if (in_array($normalized, $malpap, true) || str_contains($normalized, 'PAPUA')) {
            return 'MALUKU_PAPUA';
        }

        return 'LAINNYA';
    }

    private function kodeOptions()
    {
        $lists = [
            ProdukItem::query()->select('kode_1')->whereNotNull('kode_1')->where('kode_1', '!=', '')->distinct()->pluck('kode_1'),
            ProdukItem::query()->select('kode_2')->whereNotNull('kode_2')->where('kode_2', '!=', '')->distinct()->pluck('kode_2'),
            ProdukItem::query()->select('kode_3')->whereNotNull('kode_3')->where('kode_3', '!=', '')->distinct()->pluck('kode_3'),
            ProdukItem::query()->select('kode_4')->whereNotNull('kode_4')->where('kode_4', '!=', '')->distinct()->pluck('kode_4'),
        ];

        return collect($lists)
            ->flatten()
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }
}
