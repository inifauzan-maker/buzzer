<?php

namespace App\Http\Controllers;

use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        [$threads, $contactGroups, $unreadCounts] = $this->prepareChatData($user);

        return view('chat', [
            'threads' => $threads,
            'contactGroups' => $contactGroups,
            'activeThread' => null,
            'messages' => collect(),
            'unreadCounts' => $unreadCounts,
        ]);
    }

    public function show(Request $request, ChatThread $thread)
    {
        $user = $request->user();

        if (! $thread->hasParticipant($user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $thread->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        [$threads, $contactGroups, $unreadCounts] = $this->prepareChatData($user);

        $messages = $thread->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('chat', [
            'threads' => $threads,
            'contactGroups' => $contactGroups,
            'activeThread' => $thread,
            'messages' => $messages,
            'unreadCounts' => $unreadCounts,
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $target = User::query()->findOrFail($request->integer('user_id'));

        if (! $this->canChatWith($user, $target)) {
            abort(403, 'Akses ditolak.');
        }

        [$one, $two] = $this->normalizePair($user->id, $target->id);

        $thread = ChatThread::firstOrCreate([
            'user_one_id' => $one,
            'user_two_id' => $two,
        ]);

        return redirect()->route('chat.show', $thread);
    }

    public function storeMessage(Request $request, ChatThread $thread)
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        if (! $thread->hasParticipant($user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $thread->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->string('body')->toString(),
        ]);

        $thread->forceFill(['last_message_at' => now()])->save();

        return redirect()
            ->route('chat.show', $thread)
            ->with('status', 'Pesan terkirim.');
    }

    private function prepareChatData(User $user): array
    {
        $threads = ChatThread::forUser($user->id)
            ->with([
                'userOne.team',
                'userTwo.team',
                'latestMessage',
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $threadIds = $threads->pluck('id');
        $unreadCounts = collect();
        if ($threadIds->isNotEmpty()) {
            $unreadCounts = \App\Models\ChatMessage::query()
                ->selectRaw('chat_thread_id, COUNT(*) as total')
                ->whereIn('chat_thread_id', $threadIds)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->groupBy('chat_thread_id')
                ->pluck('total', 'chat_thread_id');
        }

        $leaders = collect();
        $members = collect();

        if ($user->role === 'leader') {
            $members = User::query()
                ->where('team_id', $user->team_id)
                ->where('role', 'staff')
                ->orderBy('name')
                ->with('team')
                ->get();

            $leaders = User::query()
                ->where('role', 'leader')
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->with('team')
                ->get();
        } elseif ($user->role === 'staff') {
            $leaders = User::query()
                ->where('team_id', $user->team_id)
                ->where('role', 'leader')
                ->orderBy('name')
                ->with('team')
                ->get();
        }

        $contactGroups = collect([
            'Leader' => $leaders,
            'Anggota Tim' => $members,
        ])->filter(fn ($group) => $group->isNotEmpty());

        return [$threads, $contactGroups, $unreadCounts];
    }

    private function canChatWith(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($user->role === 'leader') {
            if ($target->role === 'leader') {
                return true;
            }

            return $target->role === 'staff' && $target->team_id === $user->team_id;
        }

        if ($user->role === 'staff') {
            return $target->role === 'leader' && $target->team_id === $user->team_id;
        }

        return false;
    }

    private function normalizePair(int $firstId, int $secondId): array
    {
        return $firstId < $secondId
            ? [$firstId, $secondId]
            : [$secondId, $firstId];
    }
}
