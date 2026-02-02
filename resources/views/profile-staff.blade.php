<div class="profile-menu">
    <a href="#profil-saya">Profil Saya</a>
    <a href="#tugas-saya">Tugas Saya</a>
</div>

@if (!empty($canAssignTasks))
    <div class="card" style="margin-bottom: 16px;">
        <h3>Tambah Tugas</h3>
        <form class="form" method="POST" action="{{ route('profile.tasks.store', $user) }}">
            @csrf
            <div class="grid">
                <div>
                    <label for="task_title">Judul Tugas</label>
                    <input id="task_title" name="title" type="text" required>
                </div>
                <div>
                    <label for="task_due_date">Batas Waktu</label>
                    <input id="task_due_date" name="due_date" type="date">
                </div>
            </div>
            <div>
                <label for="task_notes">Catatan</label>
                <input id="task_notes" name="notes" type="text" maxlength="500">
            </div>
            <button class="button" type="submit">Simpan Tugas</button>
        </form>
    </div>
@endif

<div class="profile-grid" id="profil-saya">
    <div class="card">
        <h3>Aktivitas vs Konversi</h3>
        <div class="split-card">
            <div class="split-row">
                <span>Poin Aktivitas</span>
                <span class="chip">{{ number_format($activityPoints, 2) }}</span>
            </div>
            <div class="split-row">
                <span>Poin Konversi</span>
                <span class="chip">{{ number_format($conversionPoints, 2) }}</span>
            </div>
            <div class="split-row">
                <strong>Total</strong>
                <strong>{{ number_format($activityPoints + $conversionPoints, 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Target Tahunan {{ $targetYear }}</h3>
        @php
            $closingMax = max($memberTargetClosing, $memberClosingAchieved);
            $closingTargetHeight = $closingMax > 0 ? round(($memberTargetClosing / $closingMax) * 100) : 0;
            $closingAchievedHeight = $closingMax > 0 ? round(($memberClosingAchieved / $closingMax) * 100) : 0;
            $leadsMax = max($memberTargetLeads, $memberLeadsAchieved);
            $leadsTargetHeight = $leadsMax > 0 ? round(($memberTargetLeads / $leadsMax) * 100) : 0;
            $leadsAchievedHeight = $leadsMax > 0 ? round(($memberLeadsAchieved / $leadsMax) * 100) : 0;
        @endphp
        <div class="target-metrics">
            <div class="muted">Closing</div>
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $closingTargetHeight }}%;"></div>
                        <div class="bar achieved" style="height: {{ $closingAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($memberTargetClosing, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($memberClosingAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="muted">{{ $memberClosingPercent }}%</div>
            </div>
            <div class="muted">Leads</div>
            <div class="bar-cell">
                <div class="bar-chart">
                    <div class="bar-stack">
                        <div class="bar target" style="height: {{ $leadsTargetHeight }}%;"></div>
                        <div class="bar leads" style="height: {{ $leadsAchievedHeight }}%;"></div>
                    </div>
                    <div class="bar-values">
                        <span>Target {{ number_format($memberTargetLeads, 0, ',', '.') }}</span>
                        <span>Pencapaian {{ number_format($memberLeadsAchieved, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="muted">{{ $memberLeadsPercent }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="card" id="tugas-saya" style="margin-bottom: 20px;">
    <h3>Tugas Saya</h3>
    <p class="muted" style="margin-bottom: 12px;">Daftar tugas dari leader Anda.</p>
    <div class="profile-menu" style="margin-bottom: 12px;">
        <a href="{{ request()->fullUrlWithQuery(['task_status' => null]) }}">Semua ({{ $taskCounts['all'] ?? 0 }})</a>
        <a href="{{ request()->fullUrlWithQuery(['task_status' => 'open']) }}">Open ({{ $taskCounts['open'] ?? 0 }})</a>
        <a href="{{ request()->fullUrlWithQuery(['task_status' => 'done']) }}">Selesai ({{ $taskCounts['done'] ?? 0 }})</a>
    </div>
    <div class="task-list">
        @forelse(($staffTasks ?? []) as $task)
            <div class="task-item">
                <strong>{{ $task['title'] ?? $task->title ?? 'Tugas' }}</strong>
                <div class="task-meta">
                    Diberikan oleh {{ $task['leader_name'] ?? $task->leader_name ?? 'Leader' }}
                    · {{ $task['due_date'] ?? $task->due_date ?? '-' }}
                    · Status: {{ ($task['status'] ?? $task->status ?? 'open') === 'done' ? 'Selesai' : 'Open' }}
                </div>
                @if (!empty($task['notes'] ?? $task->notes ?? null))
                    <div>{{ $task['notes'] ?? $task->notes }}</div>
                @endif
                <div class="social-actions" style="margin-top: 6px;">
                    @if (!empty($canUpdateTaskStatus))
                        <form method="POST" action="{{ route('profile.tasks.status', $task['id'] ?? $task->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ ($task['status'] ?? $task->status ?? 'open') === 'done' ? 'open' : 'done' }}">
                            <button class="button" type="submit">
                                {{ ($task['status'] ?? $task->status ?? 'open') === 'done' ? 'Buka Kembali' : 'Tandai Selesai' }}
                            </button>
                        </form>
                    @endif
                    @if (!empty($canManageTasks))
                        <details class="inline-edit">
                            <summary class="button">Edit</summary>
                            <form class="inline-form" method="POST" action="{{ route('profile.tasks.update', $task['id'] ?? $task->id) }}">
                                @csrf
                                @method('PATCH')
                                <input name="title" type="text" value="{{ $task['title'] ?? $task->title ?? '' }}" required>
                                <input name="due_date" type="date" value="{{ $task['due_date_raw'] ?? null }}">
                                <input name="notes" type="text" value="{{ $task['notes'] ?? $task->notes ?? '' }}">
                                <select name="status" required>
                                    <option value="open" @selected(($task['status'] ?? $task->status ?? 'open') === 'open')>Open</option>
                                    <option value="done" @selected(($task['status'] ?? $task->status ?? 'open') === 'done')>Selesai</option>
                                </select>
                                <button class="button" type="submit">Simpan</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('profile.tasks.destroy', $task['id'] ?? $task->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Hapus</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="task-item">
                <strong>Belum ada tugas</strong>
                <div class="task-meta">Tugas akan muncul setelah leader menugaskan.</div>
            </div>
        @endforelse
    </div>
</div>
