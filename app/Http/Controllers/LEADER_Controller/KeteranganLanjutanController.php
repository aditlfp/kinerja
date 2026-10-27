<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Models\KeteranganLanjutan;
use Illuminate\Http\Request;

class KeteranganLanjutanController extends Controller
{
    use LocksRekapByDueDate, UsesToastRedirects;

    public function index(Request $request, $kerjasama = null)
    {
        // If called via API route, return JSON data filtered by kerjasama id
        if ($request->is('api/*') || $request->wantsJson()) {
            $query = KeteranganLanjutan::with([
                'user:id,nama_lengkap,kerjasama_id',
                'createdBy:id,nama_lengkap',
            ])
                ->latest();

            if ($kerjasama) {
                $query->whereHas('user', fn($q) => $q->where('kerjasama_id', $kerjasama));
            }

            // optional month filter
            if ($request->has('month') && $request->month) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('Y-m', $request->month);
                    $query->whereBetween('created_at', monthBounds($date));
                } catch (\Throwable $e) {
                    // ignore invalid month format
                }
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Keterangan lanjutan fetched'
            ]);
        }

        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        return view('leader_view.data_rekap.keterangan_lanjutan.index');
    }

    public function history(Request $request)
    {
        $keteranganLanjutans = KeteranganLanjutan::with([
            'user:id,nama_lengkap',
            'createdBy:id,nama_lengkap',
        ])
            ->whereHas('user', fn($q) => $q->where('kerjasama_id', auth()->user()->kerjasama_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader_view.data_rekap.keterangan_lanjutan.history', [
            'keteranganLanjutans' => $keteranganLanjutans,
        ]);
    }

    public function store(Request $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $validated = $request->validate([
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.periode' => ['required', 'string', 'max:255'],
            'entries.*.judul' => ['required', 'string', 'max:255'],
            'entries.*.keterangan' => ['required', 'string'],
        ]);
        $keterangan = collect($validated['entries'])
            ->map(function ($entry) {
                return [
                    'periode' => trim((string) ($entry['periode'] ?? '')),
                    'judul' => trim((string) ($entry['judul'] ?? '')),
                    'keterangan' => trim((string) ($entry['keterangan'] ?? '')),
                ];
            })
            ->filter(fn($entry) => $entry['periode'] !== '' && $entry['judul'] !== '' && $entry['keterangan'] !== '')
            ->values()
            ->all();

        if (count($keterangan) === 0) {
            return $this->redirectBackWithToast('warning', 'Minimal isi 1 judul dan keterangan.');
        }

        KeteranganLanjutan::create([
            'user_id' => auth()->id(),
            'created_by_user_id' => auth()->id(),
            'keterangan' => $keterangan,
        ]);

        return $this->redirectBackWithToast('success', 'Keterangan lanjutan berhasil disimpan.');
    }

}
