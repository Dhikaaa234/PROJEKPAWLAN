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
    public function index()
    {
        $user = Auth::user();

        if ($user->role->nama_role === 'admin') {

            $reports = Report::with([
                'user',
                'category',
                'status'
            ])->latest()->get();
        } else {

            $reports = Report::with([
                'category',
                'status'
            ])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'message' => 'Data laporan berhasil diambil',
            'data' => $reports
        ]);
    }

    // STORE REPORT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'judul' => 'required|max:150',
            'deskripsi' => 'required',
            'lokasi' => 'required|max:150',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')
                ->store('reports', 'public');
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'status_id' => 1,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'gambar' => $path,
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
    public function show($id)
    {
        $user = Auth::user();

        $report = Report::with([
            'user',
            'category',
            'status'
        ])->findOrFail($id);

        if (
            $user->role->nama_role !== 'admin'
            &&
            $report->user_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Akses ditolak'
            ], 403);
        }

        return response()->json([
            'message' => 'Detail laporan',
            'data' => $report
        ]);
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
