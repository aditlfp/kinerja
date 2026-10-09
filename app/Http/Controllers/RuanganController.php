<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuanganRequest;
use App\Models\Kerjasama;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RuanganImport;
use Maatwebsite\Excel\Validators\ValidationException;

class RuanganController extends Controller
{
    public function index()
    {
        $ruangan = Ruangan::paginate(10);
        return view('admin.ruangan.index', compact('ruangan'));
    }

    public function create()
    {
        $kerjasama = Kerjasama::all();
        return view('admin.ruangan.create', compact('kerjasama'));
    }

    public function store(RuanganRequest $request)
    {
        $ruangan = new Ruangan();

        $ruangan = [
            'kerjasama_id' => $request->kerjasama_id,
            'nama_ruangan' => $request->nama_ruangan
        ];

        Ruangan::create($ruangan);
        toastr()->success('Data Ruangan Dibuat');
        return to_route('admin.ruangan.index');
    }

    public function edit($id)
    {
        $kerjasama = Kerjasama::all();
        $ruanganId = Ruangan::findOrFail($id);
        return view('admin.ruangan.edit', compact('ruanganId', 'kerjasama'));
    }

    public function update(Request $request, $id)
    {
        $ruangan = [
            'kerjasama_id' => $request->kerjasama_id,
            'nama_ruangan' => $request->nama_ruangan
        ];

        $ruanganId = Ruangan::findOrFail($id);
        $ruanganId->update($ruangan);
        toastr()->success('Data Ruangan Ter Update');
        return to_route('admin.ruangan.index');
    }

    public function destroy($id)
    {
        $ruanganId = Ruangan::findOrFail($id);
        $ruanganId->delete();
        toastr()->warning('Data Ruangan Deleted');
        return redirect()->back();
    }
    
    public function import(Request $request)
    {
        try {
            Excel::import(new RuanganImport,  $request->file);
            toastr()->success('Data Berhasil Di Upload');
            return redirect()->back();
        } catch (\Exception $e) {
            // Handle other exceptions
             if ($e->errorInfo[1] === 1062) { // 1062 is the MySQL error code for a unique constraint violation
                $errorMessage = $e->getMessage();

                // Check if the error message contains information about a duplicate entry
                if (strpos($errorMessage, 'Duplicate entry') !== false) {
                    // Extract the relevant part of the error message
                    preg_match("/Duplicate entry '(.+)' for key/", $errorMessage, $matches);
        
                    if (isset($matches[1])) {
                        $duplicateValue = $matches[1];
        
                        // Handle the duplicate value as needed
                        toastr()->error('Data Gagal Di Upload');
                        return redirect()->back()->with('failures', $duplicateValue);
                    }
                }
             }
        }
        
        
    }
}
