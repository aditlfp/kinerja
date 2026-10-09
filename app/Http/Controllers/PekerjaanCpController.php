<?php

namespace App\Http\Controllers;

use App\Exports\PekerjaanTemplateExport;
use App\Http\Requests\PekerjaanCpRequest;
use App\Models\PekerjaanCp;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Kerjasama;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PekerjaanImport;
use Maatwebsite\Excel\Validators\ValidationException;

use Illuminate\Http\Request;

class PekerjaanCpController extends Controller
{
    public function index()
    {
        $pcp = PekerjaanCp::paginate(50);
        return view('admin.pekerjaanCp.index', compact('pcp'));
    }

    public function create()
    {
        $user = User::all();
        $divisi = Divisi::all();
        $kerjasama = Kerjasama::all();
        return view('admin.pekerjaanCp.create', compact('kerjasama', 'user', 'divisi'));
    }

    public function store(PekerjaanCpRequest $request)
    {
        //   dd($request->all());
        $pcp = new PekerjaanCp;

        $pcp = [
            'user_id' => $request->user_id,
            'divisi_id' => $request->devisi_id,
            'kerjasama_id' => $request->kerjasama_id,
            'name' => $request->name,
            'type_check' => $request->type_check

        ];
        PekerjaanCp::create($pcp);
        toastr()->success('Data Berhasil Di Tambahkan');
        return to_route('admin.pekerjaan-cp.index');
    }

    public function edit($id)
    {
        $user = User::all();
        $kerjasama = Kerjasama::all();
        $pcpId = PekerjaanCp::findOrFail($id);
        $divisi = Divisi::all();
        return view('admin.pekerjaanCp.edit', compact('pcpId', 'kerjasama', 'user', 'divisi'));;
    }

    public function update(Request $request, $id)
    {
        $pcp = [
            'user_id' => $request->user_id,
            'divisi_id' => $request->devisi_id,
            'kerjasama_id' => $request->kerjasama_id,
            'name' => $request->name,
            'type_check' => $request->type_check

        ];
        $Datapcp = PekerjaanCp::findOrFail($id);
        $Datapcp->update($pcp);
        toastr()->success('Data Berhasil Di Edit');
        return to_route('admin.pekerjaan-cp.index');
    }

    public function destroy($id)
    {
        $pcpId = PekerjaanCp::findOrFail($id);
        $pcpId->delete();
        toastr()->warning('Data Berhasil Di Deleted');
        return redirect()->back();
    }

    public function import(Request $request)
    {
        try {
            Excel::import(
                new PekerjaanImport,
                $request->file('file')
            );

            toastr()->success('Data Berhasil Di Upload');

            return redirect()->back();
        } catch (\Throwable $e) {

            dd([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function exportTemplate()
    {
        return Excel::download(
            new PekerjaanTemplateExport,
            'template-pekerjaan.xlsx'
        );
    }
}
