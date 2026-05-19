<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiFormatter
{
    public static function user(User $user): array
    {
        $user->loadMissing('role');
        $role = $user->role?->name ?? 'user';

        return [
            'id' => $user->id,
            'nama' => $user->nama ?? $user->name,
            'name' => $user->name ?? $user->nama,
            'nim' => $user->nim,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon,
             'phone' => $user->no_telepon,
            'role' => $role,
            'roleLabel' => $role === 'admin' ? 'Super Admin' : 'Mahasiswa',
        ];
    }

    public static function report(Report $report, ?User $viewer = null): array
    {
        $report->loadMissing(['user', 'category', 'status']);

        $status = $report->status?->name ?? '';
        $imageUrl = $report->image_path
            ? url(Storage::url($report->image_path))
            : null;

        return [
            'id' => $report->id,
            'code' => $report->report_code,
            'reportId' => $report->report_code,
            'title' => $report->title,
            'description' => $report->description,
            'status' => $status,
            'statusClass' => self::statusClass($status),
            'category' => $report->category?->name ?? '',
            'location' => $report->location,
            'reporter' => $report->user?->nama ?? $report->user?->name ?? '',
            'reporterInitial' => self::initials($report->user?->nama ?? $report->user?->name ?? ''),
            'date' => optional($report->created_at)->translatedFormat('d M Y, H:i'),
            'responses' => $report->admin_response ? '1 Tanggapan' : 'Belum ada tanggapan',
            'adminResponse' => $report->admin_response ?: 'Belum ada tanggapan dari admin.',
            'imagePath' => $report->image_path,
            'imageUrl' => $imageUrl,
            'imageType' => $imageUrl ? 'uploaded' : 'placeholder',
            'imageLabel' => $report->category?->name ?? $status,
            'imageClass' => $imageUrl ? '' : 'bg-slate-800',
            'canCancel' => self::canCancelReport($report, $viewer),
            'action' => in_array($status, ['Selesai', 'Dibatalkan'], true) ? 'history' : 'active',
            'createdAt' => optional($report->created_at)->toISOString(),
        ];
    }

    public static function notification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'description' => $notification->message,
            'message' => $notification->message,
            'time' => optional($notification->created_at)->diffForHumans(),
            'type' => $notification->type,
            'tag' => self::notificationTag($notification->type),
            'unread' => $notification->read_at === null,
            'createdAt' => optional($notification->created_at)->toISOString(),
            'data' => $notification->data ?? [],
        ];
    }

    public static function statusClass(?string $status): string
    {
        return match ($status) {
            'Dikirim' => 'bg-yellow-100 text-yellow-700',
            'Diproses' => 'bg-blue-100 text-blue-700',
            'Selesai' => 'bg-green-100 text-green-700',
            'Dibatalkan' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public static function initials(?string $name): string
    {
        return Str::of($name ?? '')
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    private static function canCancelReport(Report $report, ?User $viewer): bool
    {
        if (!$viewer) {
            return false;
        }

        return $report->user_id === $viewer->id
            && ($report->status?->name === 'Dikirim');
    }

    private static function notificationTag(?string $type): string
    {
        return match ($type) {
            'status_update' => 'STATUS UPDATE',
            'report_created' => 'LAPORAN BARU',
            'report_cancelled' => 'LAPORAN DIBATALKAN',
            default => $type ? Str::upper(str_replace('_', ' ', $type)) : '',
        };
    }
}
