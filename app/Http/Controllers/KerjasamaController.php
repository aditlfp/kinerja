<?php

namespace App\Http\Controllers;

use App\Http\Requests\KerjasamaRequest;
use App\Models\Client;
use App\Models\Kerjasama;
use Illuminate\Http\Request;

class KerjasamaController extends Controller
{
    public function index()
    {
        $kerjasama = Kerjasama::with('client')->orderBy('experied')->paginate(10);
        $allKerjasama = Kerjasama::with('client')->orderBy('experied')->get();
        return view('admin.kerjasama.index', compact('kerjasama', 'allKerjasama'));
    }

    public function create()
    {
        $client = Client::all();
        $kerjasama = Kerjasama::all();
        return view('admin.kerjasama.create', compact('client', 'kerjasama'));
    }

    public function store(KerjasamaRequest $request)
    {

        $kerjasama = new Kerjasama();
        $kerjasama = [
            'client_id' => $request->client_id,
            'value' => $request->value,
            'experied' => $request->experied,
            'approve1' => $request->approve1,
            'approve2' => $request->approve2,
            'approve3' => $request->approve3,
        ];

        if ($kerjasama) {
        try {
            Kerjasama::create($kerjasama);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
            toastr()->success('Kerjasama Berhasil Dibuat');
            return redirect()->back();
        }
            toastr()->error('Some fields Error');
            return view('admin.kerjasama.create');
    }

    public function show($id)
    {
        $kerjasama = Kerjasama::find($id);
        if ($kerjasama != null) {
            return view('admin.kerjasama.show', ['kerjasama' => $kerjasama]);
        }
        toastr()->error('Data Tidak Ditemukan');
        return view('admin.kerjasama.index');
    }

    public function edit($id)
    {
        $client = Client::all();
        $kerjasama = Kerjasama::find($id);
        if ($kerjasama != null) {
            return view('admin.kerjasama.edit', ['kerjasama' => $kerjasama, 'client' => $client]);
        }
            toastr()->error('Data Tidak Ditemukan');
            return view('admin.kerjasama.index');

    }

    public function update(Request $request, $id)
    {
        $kerjasama = [
            'client_id' => $request->client_id,
            'value' => $request->value,
            'experied' => $request->experied,
            'approve1' => $request->approve1,
            'approve2' => $request->approve2,
            'approve3' => $request->approve3,
        ];
        try {
            Kerjasama::findOrFail($id)->update($kerjasama);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
            toastr()->success('Data Berhasil Di Update');
            return redirect()->to(route('admin.kerjasama.index'));
    }

    public function destroy($id)
    {
        $kerjasama = Kerjasama::find($id);
        if ($kerjasama != null) {
            $kerjasama->delete();
            toastr()->warning('Data Telah Dihapus');
            return redirect()->back();
        }
            toastr()->error('Data Tidak Ditemukan');
            return view('admin.kerjasama.index');
    }
}
