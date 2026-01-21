@extends('layout')

@section('title', 'Chat')

@section('content')
    <style>
        .chat-shell {
            display: grid;
            grid-template-columns: minmax(240px, 320px) 1fr;
            gap: 16px;
            align-items: start;
        }
        .chat-panel {
            display: grid;
            gap: 12px;
        }
        .chat-list {
            display: grid;
            gap: 8px;
            max-height: 420px;
            overflow-y: auto;
        }
        .chat-item {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #ffffff;
            display: grid;
            gap: 6px;
        }
        .chat-item.active {
            border-color: var(--accent);
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.15);
        }
        .chat-item-title {
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .chat-item-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .chat-item-title span {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }
        .chat-badge {
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: grid;
            place-items: center;
        }
        .chat-scroll {
            display: grid;
            gap: 10px;
            max-height: 420px;
            overflow-y: auto;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        .chat-bubble {
            padding: 10px 12px;
            border-radius: 14px;
            max-width: 70%;
            background: #ffffff;
            border: 1px solid var(--border);
            position: relative;
        }
        .chat-bubble::after {
            content: "";
            position: absolute;
            top: 10px;
            left: -6px;
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-left: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            transform: rotate(45deg);
        }
        .chat-bubble.me {
            margin-left: auto;
            background: #d9f7f3;
            border-color: rgba(18, 181, 201, 0.4);
        }
        .chat-bubble.me::after {
            left: auto;
            right: -6px;
            background: #d9f7f3;
            border-left: none;
            border-bottom: none;
            border-right: 1px solid rgba(18, 181, 201, 0.4);
            border-top: 1px solid rgba(18, 181, 201, 0.4);
        }
        .chat-meta {
            margin-top: 6px;
            font-size: 11px;
            color: var(--muted);
        }
        .chat-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 6px 0;
        }
        .chat-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        @media (max-width: 860px) {
            .chat-shell {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <h1>Chat</h1>
    <p class="muted">Komunikasi antara leader dengan anggota tim atau sesama leader.</p>

    <div class="chat-shell">
        <div class="card chat-panel">
            <h3>Mulai Chat</h3>
            @if ($contactGroups->isEmpty())
                <p class="muted">Belum ada kontak yang bisa dihubungi.</p>
            @else
                <form method="POST" action="{{ route('chat.start') }}" class="form">
                    @csrf
                    <div>
                        <label for="chat-user">Kontak</label>
                        <select id="chat-user" name="user_id" required>
                            <option value="">Pilih kontak</option>
                            @foreach ($contactGroups as $label => $contacts)
                                <optgroup label="{{ $label }}">
                                    @foreach ($contacts as $contact)
                                        <option value="{{ $contact->id }}">
                                            {{ $contact->name }}
                                            ({{ ucfirst($contact->role) }}@if ($contact->team) - {{ $contact->team->team_name }}@endif)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <button class="button" type="submit">Mulai Chat</button>
                </form>
            @endif

            <hr class="chat-divider">

            <h3>Riwayat</h3>
                <div class="chat-list">
                    @forelse ($threads as $thread)
                        @php($other = $thread->otherParticipant(auth()->user()))
                        <a class="chat-item {{ $activeThread && $activeThread->id === $thread->id ? 'active' : '' }}"
                           href="{{ route('chat.show', $thread) }}">
                            <div class="chat-item-title">
                                <div>{{ $other?->name ?? 'User tidak ditemukan' }}</div>
                                <div class="chat-item-meta">
                                    @if (($unreadCounts[$thread->id] ?? 0) > 0)
                                        <span class="chat-badge">{{ $unreadCounts[$thread->id] }}</span>
                                    @endif
                                    @if ($thread->last_message_at)
                                        <span>{{ $thread->last_message_at->format('d M, H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="muted">
                                {{ $thread->latestMessage?->body ? \Illuminate\Support\Str::limit($thread->latestMessage->body, 60) : 'Belum ada pesan.' }}
                            </div>
                    </a>
                @empty
                    <p class="muted">Belum ada percakapan.</p>
                @endforelse
            </div>
        </div>

        <div class="card chat-panel">
            @if ($activeThread)
                @php($other = $activeThread->otherParticipant(auth()->user()))
                <h3>{{ $other?->name ?? 'Percakapan' }}</h3>
                <div class="muted">
                    {{ $other?->role ? ucfirst($other->role) : '-' }}
                    @if ($other?->team)
                        · {{ $other->team->team_name }}
                    @endif
                </div>
                <div class="chat-scroll">
                    @forelse ($messages as $message)
                        <div class="chat-bubble {{ $message->sender_id === auth()->id() ? 'me' : '' }}">
                            <div>{!! nl2br(e($message->body)) !!}</div>
                            <div class="chat-meta">
                                {{ $message->sender?->name ?? 'User' }} · {{ $message->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    @empty
                        <p class="muted">Belum ada pesan di percakapan ini.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('chat.messages.store', $activeThread) }}" class="form">
                    @csrf
                    <div>
                        <label for="chat-body">Tulis pesan</label>
                        <textarea id="chat-body" name="body" rows="3" required maxlength="2000"
                                  placeholder="Ketik pesan..."></textarea>
                    </div>
                    <div class="chat-actions">
                        <button class="button" type="submit">Kirim</button>
                        <span class="muted">Maksimal 2000 karakter.</span>
                    </div>
                </form>
            @else
                <h3>Pilih percakapan</h3>
                <p class="muted">Pilih kontak di sebelah kiri untuk mulai chat.</p>
            @endif
        </div>
    </div>
@endsection
