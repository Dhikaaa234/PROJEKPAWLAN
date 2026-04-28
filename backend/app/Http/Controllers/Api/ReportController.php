<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // GET ALL REPORTS
    public function index(Request $request)
    {
        $reports = Report::with([
            'user',
            'category',
            'status'
        ])
            ->when($request->search, function ($query) use ($request) {
                $query->where('judul', 'like', '%' . $request->search . '%');
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->latest()
            ->get();

        return response()->json($reports);
    }

    // STORE REPORT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'judul' => 'required|max:150',
            'deskripsi' => 'required',
            'lokasi' => 'required|max:150',
            'gambar' => 'nullable|string',
        ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'status_id' => 1, // dikirim
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'gambar' => $validated['gambar'] ?? null,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'aktivitas' => 'Membuat laporan baru'
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dibuat',
            'data' => $report
        ], 201);
    }

    // DETAIL REPORT
    public function show(string $id)
    {
        $report = Report::with([
            'user',
            'category',
            'status'
        ])->findOrFail($id);

        return response()->json($report);
    }

    // UPDATE STATUS
    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:statuses,id'
        ]);

        $report = Report::findOrFail($id);

        $report->update([
            'status_id' => $validated['status_id']
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'aktivitas' => 'Mengubah status laporan ID ' . $report->id
        ]);

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui',
            'data' => $report
        ]);
    }

    // DELETE REPORT
    public function destroy(string $id)
    {
        $report = Report::findOrFail($id);

        $report->delete();

        return response()->json([
            'message' => 'Laporan berhasil dihapus'
        ]);
    }
}
