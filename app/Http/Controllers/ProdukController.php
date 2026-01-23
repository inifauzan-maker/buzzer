<?php

namespace App\Http\Controllers;

use App\Models\ProdukItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $selectedTahun = $this->selectedTahun(
            $request->query('tahun_ajaran'),
            $request->has('tahun_ajaran')
        );
        $selectedProgram = $this->selectedProgram($request->query('program'));
        $items = $this->queryItems($selectedTahun, $selectedProgram)->get();

        return view('produk.index', [
            'items' => $items,
            'grouped' => $items->groupBy('program'),
            'programs' => ProdukItem::programOptions(),
            'tahunOptions' => ProdukItem::tahunAjaranOptions(),
            'selectedTahun' => $selectedTahun,
            'selectedProgram' => $selectedProgram,
            'editItem' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validatedData($request);
        if ($data['program'] === 'custom') {
            $data['program'] = $data['program_custom'];
        }
        unset($data['program_custom']);
        $data['omzet'] = $this->calculateOmzet($data);

        ProdukItem::create($data);

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program')
            ))
            ->with('status', 'Data produk berhasil ditambahkan.');
    }

    public function edit(Request $request, ProdukItem $produk): View
    {
        $this->authorizeAdmin($request);

        $selectedTahun = $this->selectedTahun(
            $request->query('tahun_ajaran'),
            $request->has('tahun_ajaran')
        );
        $selectedProgram = $this->selectedProgram($request->query('program'));
        $items = $this->queryItems($selectedTahun, $selectedProgram)->get();

        return view('produk.index', [
            'items' => $items,
            'grouped' => $items->groupBy('program'),
            'programs' => ProdukItem::programOptions(),
            'tahunOptions' => ProdukItem::tahunAjaranOptions(),
            'selectedTahun' => $selectedTahun,
            'selectedProgram' => $selectedProgram,
            'editItem' => $produk,
        ]);
    }

    public function update(Request $request, ProdukItem $produk): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validatedData($request);
        if ($data['program'] === 'custom') {
            $data['program'] = $data['program_custom'];
        }
        unset($data['program_custom']);
        $data['omzet'] = $this->calculateOmzet($data);

        $produk->update($data);

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program')
            ))
            ->with('status', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Request $request, ProdukItem $produk): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $produk->delete();

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program')
            ))
            ->with('status', 'Data produk berhasil dihapus.');
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()?->role !== 'superadmin') {
            abort(403, 'Akses ditolak.');
        }
    }

    private function validatedData(Request $request): array
    {
        $programKeys = array_keys(ProdukItem::programOptions());
        $programKeys[] = 'custom';
        $tahunOptions = ProdukItem::tahunAjaranOptions();

        return $request->validate([
            'program' => ['required', Rule::in($programKeys)],
            'program_custom' => ['required_if:program,custom', 'nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['required', Rule::in($tahunOptions)],
            'kode_1' => ['required', 'string', 'max:10'],
            'kode_2' => ['required', 'string', 'max:10'],
            'kode_3' => ['required', 'string', 'max:10'],
            'kode_4' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:120'],
            'biaya_daftar' => ['required', 'integer', 'min:0'],
            'biaya_pendidikan' => ['required', 'integer', 'min:0'],
            'discount' => ['required', 'integer', 'min:0'],
            'siswa' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function calculateOmzet(array $data): int
    {
        $subtotal = (int) $data['biaya_daftar']
            + (int) $data['biaya_pendidikan']
            - (int) $data['discount'];

        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal * (int) $data['siswa'];
    }

    private function selectedTahun(?string $value, bool $hasFilter): ?string
    {
        if ($hasFilter) {
            return $this->normalizeTahun($value);
        }

        $default = '2026 - 2027';
        $options = ProdukItem::tahunAjaranOptions();

        return in_array($default, $options, true) ? $default : null;
    }

    private function normalizeTahun(?string $value): ?string
    {
        $options = ProdukItem::tahunAjaranOptions();

        return in_array($value, $options, true) ? $value : null;
    }

    private function selectedProgram(?string $value): ?string
    {
        $options = array_keys(ProdukItem::programOptions());

        return in_array($value, $options, true) ? $value : null;
    }

    private function filterQuery(?string $tahun, ?string $program): array
    {
        $query = [];
        $selectedTahun = $this->normalizeTahun($tahun);
        $selectedProgram = $this->selectedProgram($program);

        if ($selectedTahun) {
            $query['tahun_ajaran'] = $selectedTahun;
        }

        if ($selectedProgram) {
            $query['program'] = $selectedProgram;
        }

        return $query;
    }

    private function queryItems(?string $tahun, ?string $program)
    {
        $query = ProdukItem::orderBy('program')->orderBy('id');

        if ($tahun) {
            $query->where('tahun_ajaran', $tahun);
        }

        if ($program) {
            $query->where('program', $program);
        }

        return $query;
    }
}
