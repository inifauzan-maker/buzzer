<?php

namespace App\Http\Controllers;

use App\Models\PublicRegistration;
use App\Models\ProdukItem;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicRegistrationController extends Controller
{
    private const STUDY_LOCATIONS = [
        'Bandung' => 'Bandung',
        'Jaksel' => 'Jaksel',
        'Jaktim' => 'Jaktim',
    ];

    private const CLASS_LEVELS = [
        'I', 'II', 'III', 'IV', 'V', 'VI',
        'VII', 'VIII', 'IX',
        'X', 'XI', 'XII',
    ];


    public function create()
    {
        return view('public.register', [
            'locations' => self::STUDY_LOCATIONS,
            'classLevels' => self::CLASS_LEVELS,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:255',
            'birth_place' => 'required|string|max:120',
            'birth_date' => 'required|date',
            'school_id' => 'nullable|integer',
            'school_name' => 'required|string|max:255',
            'class_level' => ['nullable', Rule::in(self::CLASS_LEVELS)],
            'major' => ['required', Rule::in(['IPA', 'IPS', 'Lainnya'])],
            'shirt_size' => ['required', Rule::in(['S', 'M', 'L', 'XL', 'Lainnya'])],
            'shirt_size_other' => ['nullable', 'string', 'max:50'],
            'social_media' => ['required', Rule::in(['Facebook', 'Instagram', 'Tiktok', 'X', 'Youtube', 'Lainnya'])],
            'study_location' => ['required', Rule::in(array_keys(self::STUDY_LOCATIONS))],
            'phone_number' => ['required', 'regex:/^62\\d{9,13}$/'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'regex:/^62\\d{9,13}$/'],
            'parent_job' => ['nullable', 'string', 'max:120'],
            'referral_sources' => ['nullable', 'string', Rule::in(['Website', 'Instagram', 'Tiktok', 'Facebook', 'X', 'Sekolah', 'Brosur', 'Teman', 'Keluarga', 'Lainnya'])],
            'province' => 'required|string|max:120',
            'city' => 'required|string|max:120',
            'district' => 'required|string|max:120',
            'subdistrict' => 'required|string|max:120',
            'postal_code' => 'required|string|max:10',
            'address_detail' => 'nullable|string|max:255',
            'program_id' => ['nullable', 'integer', 'exists:produk_items,id'],
            'study_day' => ['required', Rule::in(['Sabtu', 'Minggu'])],
            'study_time' => ['required', 'string', 'max:50'],
            'payment_system' => ['required', Rule::in(['Lunas', 'Angsuran'])],
        ], [
            'phone_number.regex' => 'Nomor HP wajib diawali 62 dan terdiri dari 11-15 digit.',
        ]);

        $schoolId = isset($data['school_id']) ? (int) $data['school_id'] : null;
        $schoolName = trim((string) ($data['school_name'] ?? ''));
        $isSpecialSchool = $this->isSpecialSchool($schoolName);
        if (! $schoolId && $schoolName !== '') {
            $upperName = strtoupper($schoolName);
            $data['school_name'] = $upperName;
            $existingSchool = School::query()
                ->whereRaw('UPPER(name) = ?', [$upperName])
                ->first();

            if ($existingSchool) {
                $data['school_id'] = $existingSchool->id;
                $schoolId = $existingSchool->id;
                $isSpecialSchool = $this->isSpecialSchool($existingSchool->name);
                if ($existingSchool->level_group && ! $this->classMatchesLevelGroup($data['class_level'] ?? '', $existingSchool->level_group)) {
                    return $this->errorResponse(
                        $request,
                        ['class_level' => ['Jenjang kelas tidak sesuai dengan asal sekolah.']],
                        422
                    );
                }
            } else {
                $levelGroup = $this->inferSchoolLevelGroup($data['class_level'] ?? '');
                $newSchool = School::create([
                    'name' => $upperName,
                    'level_group' => $levelGroup,
                ]);
                $data['school_id'] = $newSchool->id;
                $schoolId = $newSchool->id;
            }
        }
        if ($schoolId) {
            $school = School::query()->find($schoolId);
            if (! $school) {
                return $this->errorResponse(
                    $request,
                    ['school_id' => ['Sekolah tidak ditemukan.']],
                    422
                );
            }

            $data['school_name'] = $school->name;
            $isSpecialSchool = $this->isSpecialSchool($school->name);
            if ($school->level_group && ! $this->classMatchesLevelGroup($data['class_level'] ?? '', $school->level_group)) {
                return $this->errorResponse(
                    $request,
                    ['class_level' => ['Jenjang kelas tidak sesuai dengan asal sekolah.']],
                    422
                );
            }
        }

        if (! $isSpecialSchool && empty($data['class_level'])) {
            return $this->errorResponse(
                $request,
                ['class_level' => ['Kelas wajib dipilih untuk jenjang sekolah ini.']],
                422
            );
        }

        if (($data['shirt_size'] ?? '') === 'Lainnya' && empty($data['shirt_size_other'])) {
            return $this->errorResponse(
                $request,
                ['shirt_size_other' => ['Isi ukuran kaos jika memilih Lainnya.']],
                422
            );
        }

        if (! $isSpecialSchool && empty($data['program_id'])) {
            return $this->errorResponse(
                $request,
                ['program_id' => ['Program bimbel wajib dipilih.']],
                422
            );
        }

        if (! empty($data['program_id'])) {
            $program = ProdukItem::find($data['program_id']);
            if (! $program) {
                return $this->errorResponse(
                    $request,
                    ['program_id' => ['Program bimbel tidak ditemukan.']],
                    404
                );
            }

            if (! $this->programMatchesClass($program, $data['class_level'] ?? '')) {
                return $this->errorResponse(
                    $request,
                    ['program_id' => ['Program tidak sesuai dengan jenjang kelas.']],
                    422
                );
            }

            $data['program'] = $program->nama;
        }
        $data['name'] = $data['full_name'];
        $data['phone'] = $data['phone_number'];
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        if (! empty($data['referral_sources']) && is_array($data['referral_sources'])) {
            $data['referral_sources'] = implode(', ', $data['referral_sources']);
        }

        PublicRegistration::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pendaftaran berhasil dikirim. Tim kami akan menghubungi Anda.',
            ]);
        }

        return redirect()
            ->route('public.register')
            ->with('status', 'Pendaftaran berhasil dikirim. Tim kami akan menghubungi Anda.');
    }

    public function schools(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $results = $this->searchSchools($term);

        return response()->json([
            'data' => $results,
        ]);
    }

    public function programs(Request $request): JsonResponse
    {
        $classLevel = strtoupper((string) $request->query('classLevel', ''));
        $group = $this->resolveClassGroup($classLevel);
        $query = ProdukItem::query()
            ->select('id', 'nama', 'kode_1', 'kode_2', 'kode_3', 'tahun_ajaran')
            ->orderBy('nama');

        $tahun = $this->activeTahunAjaran();
        if ($tahun) {
            $query->where('tahun_ajaran', $tahun);
        }

        if ($group) {
            $query->where('kode_4', 'like', '%'.$group.'%');
        }

        $programs = $query->get()->map(function (ProdukItem $item) {
            return [
                'id' => $item->id,
                'label' => $item->nama,
                'name' => $item->nama,
            ];
        });

        return response()->json([
            'data' => $programs,
        ]);
    }

    private function searchSchools(string $term): array
    {
        $query = School::query();
        if ($term !== '') {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhere('city', 'like', '%'.$term.'%')
                ->orWhere('province', 'like', '%'.$term.'%');
        }

        $schools = $query->orderBy('name')->limit(30)->get();

        return $schools->map(static function (School $school): array {
            $labelParts = array_filter([$school->city, $school->province]);
            $labelSuffix = $labelParts ? ' - '.implode(', ', $labelParts) : '';

            return [
                'id' => $school->id,
                'name' => $school->name,
                'type' => $school->type,
                'city' => $school->city,
                'province' => $school->province,
                'level_group' => $school->level_group,
                'label' => $school->name.$labelSuffix,
            ];
        })->all();
    }

    private function classMatchesLevelGroup(string $classLevel, string $levelGroup): bool
    {
        if ($classLevel === '') {
            return false;
        }

        return match (strtoupper($levelGroup)) {
            'SD' => in_array($classLevel, ['I', 'II', 'III', 'IV', 'V', 'VI'], true),
            'SMP' => in_array($classLevel, ['VII', 'VIII', 'IX'], true),
            'SMA' => in_array($classLevel, ['X', 'XI', 'XII'], true),
            default => false,
        };
    }

    private function resolveClassGroup(string $classLevel): ?string
    {
        return match ($classLevel) {
            'I', 'II', 'III', 'IV', 'V', 'VI' => 'SD',
            'VII', 'VIII', 'IX' => 'SMP',
            'X', 'XI' => 'X-XI',
            'XII' => 'XII',
            default => null,
        };
    }

    private function inferSchoolLevelGroup(string $classLevel): ?string
    {
        return match ($classLevel) {
            'I', 'II', 'III', 'IV', 'V', 'VI' => 'SD',
            'VII', 'VIII', 'IX' => 'SMP',
            'X', 'XI', 'XII' => 'SMA',
            default => null,
        };
    }

    private function programMatchesClass(ProdukItem $program, string $classLevel): bool
    {
        // Jika jenjang tidak diisi (kasus alumni/boarding), lewati pengecekan
        if ($classLevel === '') {
            return true;
        }

        $group = $this->resolveClassGroup($classLevel);
        if (! $group) {
            return true;
        }

        $programGroup = strtoupper((string) $program->kode_4);

        return $programGroup !== '' && str_contains($programGroup, $group);
    }

    private function activeTahunAjaran(): ?string
    {
        $available = ProdukItem::query()
            ->select('tahun_ajaran')
            ->distinct()
            ->pluck('tahun_ajaran')
            ->filter()
            ->values()
            ->all();

        if (! $available) {
            return null;
        }

        foreach (array_reverse(ProdukItem::TAHUN_AJARAN) as $option) {
            if (in_array($option, $available, true)) {
                return $option;
            }
        }

        return $available[0] ?? null;
    }

    private function errorResponse(Request $request, array $errors, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $errors], $status);
        }

        return redirect()
            ->route('public.register')
            ->withErrors($errors);
    }

    private function isSpecialSchool(string $schoolName): bool
    {
        $upper = strtoupper($schoolName);
        return str_contains($upper, 'ALUMNI')
            || str_contains($upper, 'BOARDING');
    }
}
