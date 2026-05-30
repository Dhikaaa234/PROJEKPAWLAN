<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Status;
use App\Support\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Semua laporan publik yang bisa dilihat user login.
        $query = Report::query()->with(['user', 'category', 'status']);
        $this->applyFilters($query, $request);

        return $this->reportsResponse($query, $request);
    }

    public function myReports(Request $request)
    {
        // Laporan saya hanya mengambil report milik user dari token aktif.
        $query = Report::query()
            ->with(['user', 'category', 'status'])
            ->where('user_id', $request->user()->id);
        $this->applyFilters($query, $request);

        return $this->reportsResponse($query, $request);
    }

    public function options(Request $request)
    {
        $recentReports = Report::query()
            ->with(['user', 'category', 'status'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Report $report) => ApiFormatter::report($report, $request->user()))
            ->values();

        return response()->json([
            'categories' => Category::orderBy('name')->pluck('name')->values(),
            'similarReports' => $recentReports,
            'reportSummary' => [
                'monthlyTotal' => Report::where('user_id', $request->user()->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
            ],
        ]);
    }

    public function similar(Request $request)
    {
        // Laporan mirip terdekat ditampilkan maksimal 5 item di halaman CreateReport.
        $limit = min(max((int) $request->query('limit', 5), 1), 5);

        $reports = Report::query()
            ->with(['user', 'category', 'status'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Report $report) => ApiFormatter::report($report, $request->user()))
            ->values();

        return response()->json([
            'reports' => $reports,
        ]);
    }

    public function store(Request $request)
    {
        // Create report menerima data form dan optional file gambar via multipart/form-data.
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'category' => ['nullable', 'string', 'max:150'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $category = $this->categoryFromRequest($request);
        $submittedStatus = Status::where('name', 'Dikirim')->firstOrFail();

        $report = DB::transaction(function () use ($request, $validated, $category, $submittedStatus) {
            $report = Report::create([
                'report_code' => $this->generateReportCode(),
                'user_id' => $request->user()->id,
                'category_id' => $category->id,
                'status_id' => $submittedStatus->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'image_path' => $this->storeUploadedImage($request),
            ]);

            $report->logs()->create([
                'user_id' => $request->user()->id,
                'action' => 'created',
                'new_status_id' => $submittedStatus->id,
                'message' => 'Laporan dibuat',
            ]);

            Notification::create([
                'role_target' => 'admin',
                'title' => 'Laporan baru masuk',
                'message' => 'Laporan baru menunggu peninjauan admin.',
                'type' => 'report_created',
                'data' => ['report_id' => $report->id],
            ]);

            return $report;
        });

        return response()->json([
            'message' => 'Laporan berhasil dibuat',
            'report' => ApiFormatter::report($report->fresh(['user', 'category', 'status']), $request->user()),
        ], 201);
    }

    public function show(Request $request, Report $report)
    {
        return response()->json([
            'report' => ApiFormatter::report($report->load(['user', 'category', 'status']), $request->user()),
        ]);
    }

    public function cancel(Request $request, Report $report)
    {
        // User hanya boleh membatalkan laporan miliknya sendiri.
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak berhak membatalkan laporan ini'], 403);
        }

        $report->load('status');

        if ($report->status?->name !== 'Dikirim') {
            return response()->json([
                'message' => 'Laporan hanya dapat dibatalkan ketika masih berstatus Dikirim',
            ], 422);
        }

        $cancelledStatus = Status::where('name', 'Dibatalkan')->firstOrFail();
        $oldStatusId = $report->status_id;

        DB::transaction(function () use ($request, $report, $cancelledStatus, $oldStatusId) {
            $report->update([
                'status_id' => $cancelledStatus->id,
                'cancelled_at' => now(),
            ]);

            $report->logs()->create([
                'user_id' => $request->user()->id,
                'action' => 'cancelled',
                'old_status_id' => $oldStatusId,
                'new_status_id' => $cancelledStatus->id,
                'message' => 'Laporan dibatalkan oleh pelapor',
            ]);

            Notification::create([
                'role_target' => 'admin',
                'title' => 'Laporan dibatalkan',
                'message' => 'Pelapor membatalkan laporan fasilitas.',
                'type' => 'report_cancelled',
                'data' => ['report_id' => $report->id],
            ]);
        });

        return response()->json([
            'message' => 'Laporan berhasil dibatalkan',
            'report' => ApiFormatter::report($report->fresh(['user', 'category', 'status']), $request->user()),
        ]);
    }

    public function adminIndex(Request $request)
    {
        $query = Report::query()->with(['user', 'category', 'status']);
        $this->applyFilters($query, $request);

        return $this->reportsResponse($query, $request);
    }

    public function adminShow(Request $request, Report $report)
    {
        return $this->show($request, $report);
    }

    public function updateStatus(Request $request, Report $report)
    {
        // Admin mengubah status laporan dan optional catatan/tanggapan admin.
        $validated = $request->validate([
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string', 'exists:statuses,name'],
            'admin_response' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ]);

        $status = isset($validated['status_id'])
            ? Status::findOrFail($validated['status_id'])
            : Status::where('name', $validated['status'] ?? null)->first();

        if (!$status) {
            throw ValidationException::withMessages([
                'status' => ['Status wajib diisi.'],
            ]);
        }

        $oldStatusId = $report->status_id;
        $adminResponse = $validated['admin_response'] ?? $validated['note'] ?? $report->admin_response;

        DB::transaction(function () use ($request, $report, $status, $oldStatusId, $adminResponse) {
            $changes = [
                'status_id' => $status->id,
                'admin_response' => $adminResponse,
            ];

            if ($status->name === 'Diproses' && !$report->processed_at) {
                $changes['processed_at'] = now();
            }

            if ($status->name === 'Selesai' && !$report->completed_at) {
                $changes['completed_at'] = now();
            }

            $report->update($changes);

            $report->logs()->create([
                'user_id' => $request->user()->id,
                'action' => 'status_updated',
                'old_status_id' => $oldStatusId,
                'new_status_id' => $status->id,
                'message' => $adminResponse,
            ]);

            Notification::create([
                'user_id' => $report->user_id,
                'role_target' => 'user',
                'title' => 'Status laporan diperbarui',
                'message' => "Status laporan Anda berubah menjadi {$status->name}.",
                'type' => 'status_update',
                'data' => ['report_id' => $report->id],
            ]);
        });

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui',
            'report' => ApiFormatter::report($report->fresh(['user', 'category', 'status']), $request->user()),
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        // Filter dipakai oleh halaman semua laporan, laporan saya, dan admin management.
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $category = $request->query('category');

        $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('report_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query
                            ->where('nama', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status && $status !== 'Semua Status', fn ($query) => $query
                ->whereHas('status', fn ($query) => $query->where('name', $status)))
            ->when($category && $category !== 'Semua Kategori', fn ($query) => $query
                ->whereHas('category', fn ($query) => $query->where('name', $category)));
    }

    private function reportsResponse($query, Request $request)
    {
        $sort = $request->query('sort', 'latest');
        $total = (clone $query)->count();

        $sort === 'oldest' || $sort === 'Terlama'
            ? $query->oldest()
            : $query->latest();

        if ($request->filled('limit')) {
            $query->limit((int) $request->query('limit'));
        }

        $reports = $query->get();

        return response()->json([
            'reports' => $reports
                ->map(fn (Report $report) => ApiFormatter::report($report, $request->user()))
                ->values(),
            'categories' => Category::orderBy('name')->pluck('name')->values(),
            'statuses' => Status::orderBy('id')->pluck('name')->values(),
            'meta' => [
                'total' => $total,
            ],
        ]);
    }

    private function categoryFromRequest(Request $request): Category
    {
        if ($request->filled('category_id')) {
            return Category::findOrFail($request->input('category_id'));
        }

        if ($request->filled('category')) {
            $category = Category::where('name', $request->input('category'))->first();

            if ($category) {
                return $category;
            }
        }

        throw ValidationException::withMessages([
            'category' => ['Kategori wajib dipilih.'],
        ]);
    }

    private function generateReportCode(): string
    {
        $year = now()->year;
        $sequence = Report::where('report_code', 'like', "#REP-{$year}-%")->count() + 1;

        return sprintf('#REP-%s-%03d', $year, $sequence);
    }

    private function storeUploadedImage(Request $request): ?string
    {
        // File asli disimpan di storage public, database hanya menyimpan path-nya.
        if ($request->hasFile('image')) {
            return $request->file('image')->store('reports', 'public');
        }

        if ($request->hasFile('photo')) {
            return $request->file('photo')->store('reports', 'public');
        }

        if ($request->hasFile('file')) {
            return $request->file('file')->store('reports', 'public');
        }

        if ($request->hasFile('gambar')) {
            return $request->file('gambar')->store('reports', 'public');
        }

        $photos = $request->file('photos', []);
        $firstPhoto = is_array($photos) ? ($photos[0] ?? null) : null;

        return $firstPhoto ? $firstPhoto->store('reports', 'public') : null;
    }
}
