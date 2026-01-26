<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Pendaftaran Publik - SIVMI</title>
        <style>
            :root {
                --bg-1: #f6f2ea;
                --bg-2: #e3f1f0;
                --ink: #1f2937;
                --muted: #6b7280;
                --primary: #0a0a5c;
                --secondary: #c20f31;
                --accent-yellow: #c6bb0c;
                --accent-orange: #ff7e24;
                --accent: var(--primary);
                --accent-dark: var(--secondary);
                --card: #ffffff;
                --border: #e2e8f0;
                --shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                color: var(--ink);
                background: linear-gradient(140deg, var(--bg-1), var(--bg-2));
            }

            .page {
                min-height: 100vh;
                padding: calc(24px + env(safe-area-inset-top)) 18px
                    calc(32px + env(safe-area-inset-bottom));
            }

            .container {
                width: min(960px, 100%);
                margin: 0 auto;
                display: grid;
                gap: 20px;
            }

            .page-header {
                display: grid;
                gap: 8px;
                text-align: center;
            }

            .page-actions {
                display: flex;
                justify-content: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .page-title {
                margin: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                font-size: clamp(34px, 6vw, 52px);
                letter-spacing: 0.18em;
            }

            .page-subtitle {
                margin: 0;
                font-size: 14px;
                color: var(--muted);
            }

            .btn-link {
                text-decoration: none;
                color: var(--ink);
                border: 1px solid var(--border);
                padding: 6px 14px;
                border-radius: 999px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                font-size: 12px;
                background: #f9fafb;
                cursor: pointer;
            }

            .card {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 20px;
                padding: 18px;
                box-shadow: var(--shadow);
            }

            .alert {
                border-radius: 14px;
                padding: 12px 14px;
                border: 1px solid var(--border);
                background: #f0fdf4;
                font-size: 13px;
            }

            .alert-success {
                border-color: #bbf7d0;
                color: #166534;
            }

            .alert-error {
                background: #fef2f2;
                border-color: #fecdd3;
                color: #991b1b;
            }

            .form {
                display: grid;
                gap: 18px;
            }

            .section-title {
                font-size: 13px;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 6px;
            }

            .form-grid {
                display: grid;
                gap: 12px;
            }

            .input-field {
                display: grid;
                gap: 6px;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                position: relative;
            }

            .input-field input,
            .input-field select,
            .input-field textarea {
                width: 100%;
                padding: 10px 12px;
                border-radius: 12px;
                border: 1px solid var(--border);
                background: #f9fafb;
                color: var(--ink);
                font-size: 14px;
            }

            .input-field textarea {
                min-height: 90px;
                resize: vertical;
            }

            .input-field select option {
                background: #ffffff;
                color: #0e0e0e;
            }

            .is-hidden {
                display: none;
            }

            .text-muted {
                font-size: 12px;
                opacity: 0.7;
                text-transform: none;
                letter-spacing: 0.02em;
            }

            .suggest-list {
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                right: 0;
                background: #ffffff;
                color: #0f0f0f;
                border-radius: 12px;
                border: 1px solid var(--border);
                box-shadow: 0 18px 32px rgba(20, 0, 30, 0.2);
                max-height: 200px;
                overflow-y: auto;
                display: none;
                z-index: 30;
            }

            .suggest-list.is-visible {
                display: block;
            }

            .suggest-item {
                padding: 10px 12px;
                cursor: pointer;
                font-size: 13px;
            }

            .suggest-item:hover {
                background: rgba(99, 26, 156, 0.08);
            }

            .suggest-item.suggest-add {
                font-weight: 600;
                color: var(--accent-dark);
            }

            .suggest-name {
                font-weight: 600;
            }

            .suggest-meta {
                margin-top: 4px;
                font-size: 12px;
                opacity: 0.7;
                text-transform: none;
                letter-spacing: 0.02em;
            }

            .form-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .checkbox-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 8px 10px;
                padding: 10px 12px;
                border: 1px solid var(--border);
                border-radius: 12px;
                background: #f9fafb;
            }

            .checkbox-item {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                letter-spacing: 0.02em;
                text-transform: none;
            }

            .btn-primary {
                border: none;
                border-radius: 999px;
                padding: 12px 18px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                background: var(--accent);
                color: #ffffff;
                cursor: pointer;
            }

            .section-heading {
                font-weight: 700;
                font-size: 15px;
                letter-spacing: 0.08em;
                margin: 0 0 12px;
                text-transform: uppercase;
            }

            .section-block {
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 14px;
                background: #f9fafb;
                display: grid;
                gap: 12px;
            }

            .divider {
                height: 1px;
                border: none;
                border-top: 1px solid var(--border);
                margin: 8px 0 14px;
            }

            @media (min-width: 720px) {
                .form-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .input-field.full {
                    grid-column: 1 / -1;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="container">
                <header class="page-header">
                    <h1 class="page-title">PENDAFTARAN</h1>
                    <p class="page-subtitle">Formulir Pendaftaran Bimbel Gambar Villa Merah.</p>
                </header>

                <section class="card">
                    <div id="alert-success" class="alert alert-success" @if (!session('status')) style="display:none;" @endif>
                        {{ session('status') }}
                    </div>
                    <div id="alert-error" class="alert alert-error" @if (!$errors->any()) style="display:none;" @endif>
                        {{ $errors->first() }}
                    </div>

                    <form id="registration-form" class="form" method="POST" action="{{ route('public.register.store') }}">
                        @csrf
                        <section class="form-section section-block">
                            <div class="section-heading">I. Biodata Siswa</div>
                            <div class="form-grid">
                                <label class="input-field">
                                    Nama Lengkap
                                    <input type="text" id="full-name" name="full_name" placeholder="Nama lengkap" required />
                                </label>
                                <label class="input-field">
                                    Tempat Lahir
                                    <input type="text" id="birth-place" name="birth_place" placeholder="Tempat lahir" required />
                                </label>
                                <label class="input-field">
                                    Tanggal Lahir
                                    <input type="date" id="birth-date" name="birth_date" required />
                                </label>
                                <label class="input-field full">
                                    Asal Sekolah
                                    <input
                                        type="text"
                                        id="school-search"
                                        placeholder="Contoh: SMAN 3 Bandung"
                                        autocomplete="off"
                                        required
                                    />
                                    <input type="hidden" id="school-id" name="school_id" />
                                    <input type="hidden" id="school-name" name="school_name" />
                                    <div class="suggest-list" id="school-suggest"></div>
                                    <small class="text-muted">
                                        Ketik SMAN/SMAS/SMK/MA untuk memunculkan daftar sekolah favorit. Jika tidak ada,
                                        pilih opsi tambah sekolah.
                                    </small>
                                </label>
                                <label class="input-field">
                                    Kelas
                                    <select id="class-level" name="class_level" required disabled>
                                        <option value="">Pilih kelas</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Jurusan
                                    <select id="major" name="major" required>
                                        <option value="">Pilih jurusan</option>
                                        <option value="IPA">IPA</option>
                                        <option value="IPS">IPS</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Nomor HP (diawali 62)
                                    <input
                                        type="tel"
                                        id="phone-number"
                                        name="phone_number"
                                        placeholder="62xxxxxxxxxxx"
                                        required
                                    />
                                </label>
                                <label class="input-field">
                                    Ukuran Kaos
                                    <select id="shirt-size" name="shirt_size" required>
                                        <option value="">Pilih ukuran</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </label>
                                <label class="input-field is-hidden" id="shirt-size-other-wrap">
                                    Ukuran Kaos (Lainnya)
                                    <input type="text" id="shirt-size-other" name="shirt_size_other" placeholder="Isi ukuran lainnya" />
                                </label>
                                <label class="input-field">
                                    Media Sosial yang sering digunakan
                                    <select id="social-media" name="social_media" required>
                                        <option value="">Pilih media sosial</option>
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="Tiktok">Tiktok</option>
                                        <option value="X">X (Twitter)</option>
                                        <option value="Youtube">Youtube</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Lokasi Belajar
                                    <select id="study-location" name="study_location" required>
                                        <option value="">Pilih lokasi belajar</option>
                                        @foreach ($locations as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </section>

                        <section class="form-section section-block">
                            <div class="section-heading">II. Data Orang Tua / Wali</div>
                            <div class="form-grid">
                                <label class="input-field">
                                    Nama Orang Tua/Wali
                                    <input type="text" id="parent-name" name="parent_name" placeholder="Nama orang tua/wali" required />
                                </label>
                                <label class="input-field">
                                    Nomor WA Orang Tua/Wali
                                    <input
                                        type="tel"
                                        id="parent-phone"
                                        name="parent_phone"
                                        placeholder="62xxxxxxxxxxx"
                                        required
                                    />
                                </label>
                                <label class="input-field">
                                    Pekerjaan Orang Tua/Wali
                                    <input type="text" id="parent-job" name="parent_job" placeholder="Pekerjaan" />
                                </label>
                                <label class="input-field">
                                    Provinsi
                                    <select id="province" name="province" required>
                                        <option value="">Memuat provinsi...</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Kota / Kabupaten
                                    <select id="city" name="city" required disabled>
                                        <option value="">Pilih provinsi terlebih dahulu</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Kecamatan
                                    <select id="district" name="district" required disabled>
                                        <option value="">Pilih kota/kabupaten terlebih dahulu</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Kelurahan
                                    <select id="subdistrict" name="subdistrict" required disabled>
                                        <option value="">Pilih kecamatan terlebih dahulu</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Kode Pos
                                    <input type="text" id="postal-code" name="postal_code" inputmode="numeric" required />
                                </label>
                                <label class="input-field full">
                                    Detail Alamat (Opsional)
                                    <textarea id="address-detail" name="address_detail" rows="2"></textarea>
                                </label>
                            </div>
                        </section>

                        <section class="form-section section-block">
                            <div class="section-heading">III. Sumber Informasi</div>
                            <label class="input-field full">
                                Sumber Informasi
                                <select id="referral-source" name="referral_sources">
                                    <option value="">Pilih sumber informasi</option>
                                    <option value="Website">Website</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Tiktok">Tiktok</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="X">X (Twitter)</option>
                                    <option value="Sekolah">Sekolah</option>
                                    <option value="Brosur">Brosur</option>
                                    <option value="Teman">Teman</option>
                                    <option value="Keluarga">Keluarga</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </label>
                        </section>

                        <section class="form-section section-block">
                            <div class="section-heading">IV. Program Bimbel</div>
                            <div class="form-grid">
                                <label class="input-field full">
                                    Program Bimbel
                                    <select id="program" name="program_id" required disabled>
                                        <option value="">Pilih program sesuai jenjang kelas</option>
                                    </select>
                                    <small class="text-muted">
                                        Program otomatis mengikuti jenjang kelas yang dipilih.
                                    </small>
                                </label>
                                <label class="input-field">
                                    Waktu Belajar - Hari
                                    <select id="study-day" name="study_day" required>
                                        <option value="">Pilih hari</option>
                                        <option value="Sabtu">Sabtu</option>
                                        <option value="Minggu">Minggu</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Waktu Belajar - Jam
                                    <select id="study-time" name="study_time" required>
                                        <option value="">Pilih jam</option>
                                    </select>
                                </label>
                                <label class="input-field">
                                    Sistem Pembayaran
                                    <select id="payment-system" name="payment_system" required>
                                        <option value="">Pilih sistem pembayaran</option>
                                        <option value="Lunas">Lunas</option>
                                        <option value="Angsuran">Angsuran</option>
                                    </select>
                                </label>
                            </div>
                        </section>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Kirim Pendaftaran</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
        <script>
            const form = document.getElementById("registration-form");
            const successAlert = document.getElementById("alert-success");
            const errorAlert = document.getElementById("alert-error");
            const schoolInput = document.getElementById("school-search");
            const schoolSuggest = document.getElementById("school-suggest");
            const schoolIdInput = document.getElementById("school-id");
            const schoolNameInput = document.getElementById("school-name");
            const classSelect = document.getElementById("class-level");
            const programSelect = document.getElementById("program");
            const shirtSizeSelect = document.getElementById("shirt-size");
            const shirtSizeOtherWrap = document.getElementById("shirt-size-other-wrap");
            const shirtSizeOtherInput = document.getElementById("shirt-size-other");
            const phoneInput = document.getElementById("phone-number");
            const studyLocationSelect = document.getElementById("study-location");
            const studyDaySelect = document.getElementById("study-day");
            const studyTimeSelect = document.getElementById("study-time");
            const paymentSystemSelect = document.getElementById("payment-system");
            const provinceSelect = document.getElementById("province");
            const citySelect = document.getElementById("city");
            const districtSelect = document.getElementById("district");
            const subdistrictSelect = document.getElementById("subdistrict");
            const postalCodeInput = document.getElementById("postal-code");

            const classOptionsMap = {
                SD: ["I", "II", "III", "IV", "V", "VI"],
                SMP: ["VII", "VIII", "IX"],
                SMA: ["X", "XI", "XII"],
            };
            const allClassLevels = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];

            let activeSchool = null;
            let suggestTimer = null;
            let lastSchoolQuery = "";

            const studyTimeOptions = {
                Jakarta: [
                    "Sesi 1 (09.00 - 13.00)",
                    "Sesi 2 (14.00 - 18.00)",
                ],
                Bandung: [
                    "Sesi 1 (10.00 - 12.00)",
                    "Sesi 2 (13.00 - 15.00)",
                ],
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

            const hideAlert = (el) => {
                el.style.display = "none";
                el.innerHTML = "";
            };

            const showAlert = (el, message) => {
                el.innerHTML = message;
                el.style.display = "block";
            };

            const resolveLevelGroup = (name) => {
                const upper = (name || "").toUpperCase();
                if (upper.includes("ALUMNI") || upper.includes("BOARDING")) {
                    return "NONE";
                }
                if (upper.includes("SMA") || upper.includes("SMK") || /\bMA\b/.test(upper)) {
                    return "SMA";
                }
                if (upper.includes("SMP")) {
                    return "SMP";
                }
                if (upper.includes("SD")) {
                    return "SD";
                }
                return null;
            };

            const normalizeSchoolName = (value) => (value || "").trim().toUpperCase();

            const setSchoolValues = (school) => {
                activeSchool = school;
                schoolInput.value = school?.name ?? schoolInput.value;
                schoolIdInput.value = school?.id ?? "";
                schoolNameInput.value = school?.name ?? schoolInput.value.trim();
                const group = school?.level_group || resolveLevelGroup(school?.name ?? "");
                updateClassLevels(group);
                resetPrograms();
            };

            const renderSchoolSuggestions = (items) => {
                schoolSuggest.innerHTML = "";
                const upperQuery = normalizeSchoolName(lastSchoolQuery);
                const hasExact = items.some((item) => normalizeSchoolName(item.name || item.label || "") === upperQuery);

                items.forEach((item) => {
                    const btn = document.createElement("div");
                    btn.className = "suggest-item";
                    const name = document.createElement("div");
                    name.className = "suggest-name";
                    name.textContent = item.name || item.label || "";
                    btn.appendChild(name);

                    const metaParts = [];
                    if (item.type) {
                        metaParts.push(item.type);
                    }

                    const location = [item.city, item.province].filter(Boolean).join(", ");
                    if (location) {
                        metaParts.push(location);
                    }

                    if (metaParts.length) {
                        const meta = document.createElement("div");
                        meta.className = "suggest-meta";
                        meta.textContent = metaParts.join(" - ");
                        btn.appendChild(meta);
                    }
                    btn.addEventListener("click", () => {
                        setSchoolValues(item);
                        schoolSuggest.classList.remove("is-visible");
                    });
                    schoolSuggest.appendChild(btn);
                });

                if (!hasExact && upperQuery.length >= 3) {
                    const addBtn = document.createElement("div");
                    addBtn.className = "suggest-item suggest-add";
                    addBtn.textContent = `Tambah sekolah: ${upperQuery}`;
                    addBtn.addEventListener("click", () => {
                        activeSchool = null;
                        schoolInput.value = upperQuery;
                        schoolIdInput.value = "";
                        schoolNameInput.value = upperQuery;
                        updateClassLevels(resolveLevelGroup(upperQuery));
                        resetPrograms();
                        schoolSuggest.classList.remove("is-visible");
                    });
                    schoolSuggest.appendChild(addBtn);
                }

                if (!items.length && upperQuery.length < 3) {
                    schoolSuggest.classList.remove("is-visible");
                    return;
                }

                schoolSuggest.classList.add("is-visible");
            };

            const fetchSchools = (query) => {
                lastSchoolQuery = query;
                fetch(`/pendaftaran/sekolah?q=${encodeURIComponent(query)}`)
                    .then((response) => response.json())
                    .then((payload) => {
                        renderSchoolSuggestions(payload.data ?? []);
                    })
                    .catch(() => {
                        renderSchoolSuggestions([]);
                    });
            };

            schoolInput.addEventListener("input", (event) => {
                const value = event.target.value.trim();
                activeSchool = null;
                schoolIdInput.value = "";
                schoolNameInput.value = value;
                updateClassLevels(resolveLevelGroup(value));
                resetPrograms();

                if (suggestTimer) {
                    clearTimeout(suggestTimer);
                }

                if (!value) {
                    schoolSuggest.classList.remove("is-visible");
                    return;
                }

                suggestTimer = setTimeout(() => fetchSchools(value), 300);
            });

            schoolInput.addEventListener("blur", () => {
                setTimeout(() => {
                    schoolSuggest.classList.remove("is-visible");
                    if (!activeSchool) {
                        const upperName = normalizeSchoolName(schoolInput.value);
                        schoolInput.value = upperName;
                        schoolNameInput.value = upperName;
                        updateClassLevels(resolveLevelGroup(schoolNameInput.value));
                    }
                }, 150);
            });

            const updateClassLevels = (levelGroup) => {
                classSelect.innerHTML = "";
                const placeholder = document.createElement("option");
                placeholder.value = "";
                placeholder.textContent = "Pilih kelas";
                classSelect.appendChild(placeholder);

                if (levelGroup === "NONE") {
                    classSelect.disabled = true;
                    return;
                }

                if (levelGroup === "NONE") {
                    classSelect.value = "";
                    classSelect.disabled = true;
                    resetPrograms();
                    return;
                }

                const options = levelGroup && classOptionsMap[levelGroup] ? classOptionsMap[levelGroup] : allClassLevels;
                options.forEach((level) => {
                    const option = document.createElement("option");
                    option.value = level;
                    option.textContent = level;
                    classSelect.appendChild(option);
                });

                classSelect.disabled = false;
            };

            const toggleShirtSizeOther = () => {
                if (!shirtSizeSelect || !shirtSizeOtherWrap || !shirtSizeOtherInput) {
                    return;
                }
                const isOther = shirtSizeSelect.value === "Lainnya";
                shirtSizeOtherWrap.classList.toggle("is-hidden", !isOther);
                if (!isOther) {
                    shirtSizeOtherInput.value = "";
                }
            };

            const resolveStudyLocation = (value) => {
                if (!value) {
                    return null;
                }
                return value === "Bandung" ? "Bandung" : "Jakarta";
            };

            const updateStudyTimes = () => {
                if (!studyTimeSelect) {
                    return;
                }
                studyTimeSelect.innerHTML = "";
                const placeholder = document.createElement("option");
                placeholder.value = "";
                placeholder.textContent = "Pilih jam";
                studyTimeSelect.appendChild(placeholder);

                const locationKey = resolveStudyLocation(studyLocationSelect?.value);
                if (!locationKey) {
                    studyTimeSelect.disabled = true;
                    return;
                }
                const options = studyTimeOptions[locationKey] || [];
                options.forEach((label) => {
                    const option = document.createElement("option");
                    option.value = label;
                    option.textContent = label;
                    studyTimeSelect.appendChild(option);
                });
                studyTimeSelect.disabled = false;
            };

            if (shirtSizeSelect) {
                shirtSizeSelect.addEventListener("change", toggleShirtSizeOther);
                toggleShirtSizeOther();
            }

            if (studyLocationSelect) {
                studyLocationSelect.addEventListener("change", updateStudyTimes);
                updateStudyTimes();
            }

            const resetPrograms = () => {
                programSelect.innerHTML = "";
                const option = document.createElement("option");
                option.value = "";
                option.textContent = "Pilih program bimbel";
                programSelect.appendChild(option);
                programSelect.disabled = true;
            };

            const loadPrograms = (classLevel) => {
                programSelect.disabled = true;
                programSelect.innerHTML = "";

                fetch(`/pendaftaran/programs?classLevel=${encodeURIComponent(classLevel)}`)
                    .then((response) => response.json())
                    .then((payload) => {
                        const programs = payload.data ?? [];
                        const placeholder = document.createElement("option");
                        placeholder.value = "";
                        placeholder.textContent = programs.length ? "Pilih program bimbel" : "Program belum tersedia";
                        programSelect.appendChild(placeholder);

                        programs.forEach((program) => {
                            const option = document.createElement("option");
                            option.value = program.id;
                            option.textContent = program.label ?? program.name;
                            programSelect.appendChild(option);
                        });

                        programSelect.disabled = programs.length === 0;
                    })
                    .catch(() => {
                        const option = document.createElement("option");
                        option.value = "";
                        option.textContent = "Program tidak dapat dimuat";
                        programSelect.appendChild(option);
                        programSelect.disabled = true;
                    });
            };

            classSelect.addEventListener("change", () => {
                const classLevel = classSelect.value;
                resetPrograms();
                if (classLevel) {
                    loadPrograms(classLevel);
                } else {
                    resetPrograms();
                }
            });

            phoneInput.addEventListener("blur", () => {
                const raw = phoneInput.value.trim();
                if (raw && !raw.startsWith("62")) {
                    phoneInput.value = `62${raw.replace(/^0+/, "")}`;
                }
            });

            const fillSelect = (select, items, placeholder, includePostal = false) => {
                select.innerHTML = "";
                const option = document.createElement("option");
                option.value = "";
                option.textContent = placeholder;
                select.appendChild(option);

                items.forEach((item) => {
                    const opt = document.createElement("option");
                    opt.value = item.id ?? item.code;
                    opt.textContent = item.name;
                    if (includePostal && item.postal_code) {
                        opt.dataset.postal = item.postal_code;
                    }
                    select.appendChild(opt);
                });

                select.disabled = items.length === 0;
            };

            const loadProvinces = () => {
                fetch("https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json")
                    .then((response) => response.json())
                    .then((provinces) => {
                        fillSelect(provinceSelect, provinces, "Pilih provinsi");
                    })
                    .catch(() => {
                        fillSelect(provinceSelect, [], "Gagal memuat provinsi");
                    });
            };

            provinceSelect.addEventListener("change", () => {
                const provinceId = provinceSelect.value;
                fillSelect(citySelect, [], "Memuat kota/kabupaten...");
                fillSelect(districtSelect, [], "Pilih kecamatan");
                fillSelect(subdistrictSelect, [], "Pilih kelurahan");
                postalCodeInput.value = "";

                if (!provinceId) {
                    return;
                }

                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`)
                    .then((response) => response.json())
                    .then((cities) => {
                        fillSelect(citySelect, cities, "Pilih kota/kabupaten");
                    })
                    .catch(() => {
                        fillSelect(citySelect, [], "Gagal memuat kota/kabupaten");
                    });
            });

            citySelect.addEventListener("change", () => {
                const cityId = citySelect.value;
                fillSelect(districtSelect, [], "Memuat kecamatan...");
                fillSelect(subdistrictSelect, [], "Pilih kelurahan");
                postalCodeInput.value = "";

                if (!cityId) {
                    return;
                }

                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`)
                    .then((response) => response.json())
                    .then((districts) => {
                        fillSelect(districtSelect, districts, "Pilih kecamatan");
                    })
                    .catch(() => {
                        fillSelect(districtSelect, [], "Gagal memuat kecamatan");
                    });
            });

            districtSelect.addEventListener("change", () => {
                const districtId = districtSelect.value;
                fillSelect(subdistrictSelect, [], "Memuat kelurahan...");
                postalCodeInput.value = "";

                if (!districtId) {
                    return;
                }

                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`)
                    .then((response) => response.json())
                    .then((villages) => {
                        fillSelect(subdistrictSelect, villages, "Pilih kelurahan", true);
                    })
                    .catch(() => {
                        fillSelect(subdistrictSelect, [], "Gagal memuat kelurahan");
                    });
            });

            const searchPostalCode = (subdistrictName, districtName, cityName) => {
                if (!subdistrictName) {
                    return;
                }

                const queryParts = [subdistrictName, districtName, cityName].filter(Boolean).join(" ");
                fetch(`https://kodepos.vercel.app/search?q=${encodeURIComponent(queryParts)}`)
                    .then((response) => response.json())
                    .then((payload) => {
                        const collection = Array.isArray(payload)
                            ? payload
                            : Array.isArray(payload?.data)
                            ? payload.data
                            : [];
                        const postal = collection.length ? collection[0]?.postalcode : "";
                        if (postal) {
                            postalCodeInput.value = postal;
                        }
                    })
                    .catch(() => {});
            };

            subdistrictSelect.addEventListener("change", () => {
                const selected = subdistrictSelect.selectedOptions[0];
                if (selected) {
                    postalCodeInput.value = selected.dataset.postal ?? "";
                    if (!postalCodeInput.value) {
                        searchPostalCode(
                            selected.textContent,
                            districtSelect.selectedOptions[0]?.textContent ?? "",
                            citySelect.selectedOptions[0]?.textContent ?? ""
                        );
                    }
                }
            });

            form.addEventListener("submit", (event) => {
                event.preventDefault();
                hideAlert(successAlert);
                hideAlert(errorAlert);

                const payload = {
                    full_name: form.full_name.value.trim(),
                    birth_place: form.birth_place.value.trim(),
                    birth_date: form.birth_date.value,
                    school_id: schoolIdInput.value ? Number(schoolIdInput.value) : null,
                    school_name: schoolNameInput.value.trim(),
                    class_level: classSelect.value,
                    major: form.major.value,
                    phone_number: phoneInput.value.trim(),
                    shirt_size: form.shirt_size.value,
                    shirt_size_other: form.shirt_size_other.value.trim(),
                    social_media: form.social_media.value,
                    parent_name: form.parent_name.value.trim(),
                    parent_phone: form.parent_phone.value.trim(),
                    parent_job: form.parent_job.value.trim(),
                    province: provinceSelect.selectedOptions[0]?.textContent ?? "",
                    city: citySelect.selectedOptions[0]?.textContent ?? "",
                    district: districtSelect.selectedOptions[0]?.textContent ?? "",
                    subdistrict: subdistrictSelect.selectedOptions[0]?.textContent ?? "",
                    postal_code: postalCodeInput.value.trim(),
                    address_detail: form.address_detail.value.trim(),
                    referral_sources: form.referral_sources.value,
                    program_id: programSelect.value,
                    study_location: studyLocationSelect.value,
                    study_day: studyDaySelect.value,
                    study_time: studyTimeSelect.value,
                    payment_system: paymentSystemSelect.value,
                };

                fetch(form.action, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) {
                            const messages = Object.values(data.errors ?? {})
                                .flat()
                                .join("<br>");
                            showAlert(errorAlert, messages || "Terjadi kesalahan saat menyimpan.");
                            return;
                        }

                        form.reset();
                        schoolNameInput.value = "";
                        schoolIdInput.value = "";
                        schoolInput.value = "";
                        resetPrograms();
                        updateClassLevels(null);
                        fillSelect(citySelect, [], "Pilih provinsi terlebih dahulu");
                        fillSelect(districtSelect, [], "Pilih kota/kabupaten terlebih dahulu");
                        fillSelect(subdistrictSelect, [], "Pilih kecamatan terlebih dahulu");
                        showAlert(successAlert, data.message ?? "Pendaftaran berhasil.");
                    })
                    .catch(() => {
                        showAlert(errorAlert, "Terjadi kesalahan jaringan. Silakan coba lagi.");
                    });
            });

            loadProvinces();
            updateClassLevels(null);
            resetPrograms();
        </script>
    </body>
</html>
