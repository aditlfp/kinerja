<?php

namespace App\Http\Controllers\Admin\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Kerjasama;
use Illuminate\Http\Request;

class DashboardRekapController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get()
            ->groupBy('data.kerjasama_id');

        $query = Kerjasama::query()
            ->select('id', 'client_id')
            ->with('client:id,name,email,phone');

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('client', function ($c) use ($searchTerm) {
                    $c->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        $client = $query->paginate(15);

        return view('admin.rekap.index', [
            'client' => $client,
            'notifications' => $notifications,
        ]);
    }

    public function indexOvertimes($kerjasama)
    {
        $kerja = Kerjasama::findOrFail($kerjasama);
        $client = Client::findOrFail($kerja->client_id);

        return view('admin.rekap.overtimes.index', compact('client'));
    }

    public function indexPersonOut($kerjasama)
    {
        $kerja = Kerjasama::findOrFail($kerjasama);
        $client = Client::findOrFail($kerja->client_id);

        return view('admin.rekap.person_out.index', compact('client'));
    }

    public function indexPersonIn($kerjasama)
    {
        $kerja = Kerjasama::findOrFail($kerjasama);
        $client = Client::findOrFail($kerja->client_id);

        return view('admin.rekap.person_in.index', compact('client'));
    }

    public function indexCutting($kerjasama)
    {
        $kerja = Kerjasama::findOrFail($kerjasama);
        $client = Client::findOrFail($kerja->client_id);

        return view('admin.rekap.cutting.index', compact('client'));
    }

    public function indexFinishedTraining($kerjasama)
    {
        $kerja = Kerjasama::findOrFail($kerjasama);
        $client = Client::findOrFail($kerja->client_id);

        return view('admin.rekap.finished_training.index', compact('client'));
    }
}
