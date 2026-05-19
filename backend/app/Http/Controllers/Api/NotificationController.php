<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Support\ApiFormatter;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'notifications' => $notifications
                ->map(fn (Notification $notification) => ApiFormatter::notification($notification))
                ->values(),
        ]);
    }

    public function show(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        return response()->json([
            'notification' => ApiFormatter::notification($notification),
        ]);
    }

    public function read(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'notification' => ApiFormatter::notification($notification->fresh()),
        ]);
    }

    public function readAll(Request $request)
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi telah dibaca',
        ]);
    }

    public function adminIndex(Request $request)
    {
        $limit = (int) $request->query('limit', 20);

        $query = Notification::query()
            ->where('role_target', 'admin')
            ->latest();

        if ($request->filled('cursor')) {
            $query->where('id', '<', (int) $request->query('cursor'));
        }

        $notifications = $query->limit($limit)->get();
        $nextCursor = $notifications->count() === $limit
            ? $notifications->last()?->id
            : null;

        return response()->json([
            'notifications' => $notifications
                ->map(fn (Notification $notification) => ApiFormatter::notification($notification))
                ->values(),
            'meta' => [
                'nextCursor' => $nextCursor,
            ],
        ]);
    }

    public function adminShow(Notification $notification)
    {
        if ($notification->role_target !== 'admin') {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        return response()->json([
            'notification' => ApiFormatter::notification($notification),
        ]);
    }

    public function adminRead(Notification $notification)
    {
        if ($notification->role_target !== 'admin') {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'notification' => ApiFormatter::notification($notification->fresh()),
        ]);
    }

    public function adminReadAll()
    {
        Notification::query()
            ->where('role_target', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi admin telah dibaca',
        ]);
    }
}
