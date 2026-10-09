<?php

namespace App\Http\Controllers;

use App\Http\Requests\JabatanRequest;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Kerjasama;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        
        $jabatan = Jabatan::paginate(10);
        return view('admin.jabatan.index', compact('jabatan'));
    }

    public function create()
    {
        $mitra = Kerjasama::all();
        $jabatan = Jabatan::all();
        $divisi = Divisi::all();
        return view('admin.jabatan.create', compact('jabatan', 'divisi', 'mitra'));
    }

    public function store(JabatanRequest $request)
    {

        $jabatan = new Jabatan();
        
        $jabatan = [
            'divisi_id' => $request->divisi_id,
            'code_jabatan' => $request->code_jabatan,
            'type_jabatan' => $request->type_jabatan,
            'name_jabatan' => $request->name_jabatan,
            'kerjasama_id' => $request->kerjasama_id
        ];
        
        // $datas = json_encode($request->kerjasama_id);
        
        // Save $jabatan to the database
        // dd($datas);
        Jabatan::create($jabatan);

        toastr()->success('Jabatan Berhasil Di Buat');
        return to_route('admin.jabatan.index');
    }

    public function edit($id)
    {
        $divisi = Divisi::all();
        $jabatan = Jabatan::findOrFail($id);
        return view('admin.jabatan.edit', compact('jabatan', 'divisi'));
    }

    public function update(Request $request, $id)
    {
        $jabatan = [
            'divisi_id' => $request->divisi_id,
            'code_jabatan' => $request->code_jabatan,
            'type_jabatan' => $request->type_jabatan,
            'name_jabatan' => $request->name_jabatan,
            'kerjasama_id' => $request->input('kerjasama_id')
        ];

        $dataJabatan = Jabatan::findOrFail($id);
        $dataJabatan->update($jabatan);
        toastr()->success('Jabatan Berhasil Di Update');
        return to_route('admin.jabatan.index');
    }

    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $jabatan->delete();
        toastr()->warning('Data Jabatan Dihapus');
        return redirect()->back();
    }
}
