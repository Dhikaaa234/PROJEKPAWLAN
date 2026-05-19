<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Status;
use App\Support\ApiFormatter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function userDashboard(Request $request)
    {
        $baseQuery = Report::where('user_id', $request->user()->id);

        return response()->json($this->dashboardPayload($baseQuery, $request, false));
    }

    public function adminDashboard(Request $request)
    {
        return response()->json($this->dashboardPayload(Report::query(), $request, true));
    }

    public function adminReportStats()
    {
        return response()->json([
            'stats' => $this->statCards(Report::query(), true),
        ]);
    }

    public function exportReports()
    {
        $reports = Report::with(['user', 'category', 'status'])->latest()->get();

        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Judul', 'Kategori', 'Status', 'Lokasi', 'Pelapor', 'Tanggal', 'Foto']);

            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->report_code,
                    $report->title,
                    $report->category?->name,
                    $report->status?->name,
                    $report->location,
                    $report->user?->nama ?? $report->user?->name,
                    optional($report->created_at)->toDateTimeString(),
                    $this->reportPhotoUrl($report),
                ]);
            }

            fclose($handle);
        }, 'filkomcare-reports.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function reportPhotoUrl(Report $report): string
    {
        if (!$report->image_path) {
            return '-';
        }

        return asset('storage/' . ltrim($report->image_path, '/'));
    }

    public function generateReport(Request $request)
    {
        return response()->json([
            'message' => 'Laporan dashboard berhasil dibuat',
            ...$this->dashboardPayload(Report::query(), $request, true),
        ]);
    }

    private function dashboardPayload($baseQuery, Request $request, bool $admin): array
    {
        $recentReports = (clone $baseQuery)
            ->with(['user', 'category', 'status'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Report $report) => ApiFormatter::report($report, $request->user()))
            ->values();

        return [
            'totalReports' => (clone $baseQuery)->count(),
            'submittedReports' => $this->countByStatus($baseQuery, 'Dikirim'),
            'processedReports' => $this->countByStatus($baseQuery, 'Diproses'),
            'completedReports' => $this->countByStatus($baseQuery, 'Selesai'),
            'cancelledReports' => $this->countByStatus($baseQuery, 'Dibatalkan'),
            'stats' => $this->statCards($baseQuery, $admin),
            'incomingReports' => $admin
                ? (clone $baseQuery)
                    ->with(['user', 'category', 'status'])
                    ->whereHas('status', fn ($query) => $query->where('name', 'Dikirim'))
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (Report $report) => ApiFormatter::report($report, $request->user()))
                    ->values()
                : [],
            'recentReports' => $recentReports,
        ];
    }

    private function statCards($baseQuery, bool $admin): array
    {
        return [
            [
                'key' => 'total',
                'title' => $admin ? 'TOTAL' : 'Total Laporan',
                'value' => (clone $baseQuery)->count(),
                'note' => $admin ? 'Laporan Masuk' : 'Semua',
                'subtitle' => $admin ? 'Laporan Masuk' : 'Semua',
            ],
            [
                'key' => 'dikirim',
                'title' => 'Dikirim',
                'value' => $this->countByStatus($baseQuery, 'Dikirim'),
                'note' => $admin ? 'Menunggu Review' : 'Baru',
                'subtitle' => $admin ? 'Menunggu Review' : 'Baru',
            ],
            [
                'key' => 'diproses',
                'title' => 'Diproses',
                'value' => $this->countByStatus($baseQuery, 'Diproses'),
                'note' => $admin ? 'Dalam Penanganan' : 'Aktif',
                'subtitle' => $admin ? 'Dalam Penanganan' : 'Aktif',
            ],
            [
                'key' => 'selesai',
                'title' => 'Selesai',
                'value' => $this->countByStatus($baseQuery, 'Selesai'),
                'note' => $admin ? 'Berhasil Diperbaiki' : 'Selesai',
                'subtitle' => $admin ? 'Berhasil Diperbaiki' : 'Selesai',
            ],
        ];
    }

    private function countByStatus($baseQuery, string $status): int
    {
        $statusId = Status::where('name', $status)->value('id');

        if (!$statusId) {
            return 0;
        }

        return (clone $baseQuery)->where('status_id', $statusId)->count();
    }
}
