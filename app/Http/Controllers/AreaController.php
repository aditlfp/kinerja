<?php

namespace App\Http\Controllers;

use App\Http\Requests\AreaRequest;
use App\Models\Area;
use App\Models\Subarea;
use App\Models\Kerjasama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AreaController extends Controller
{
    public function index()
    {
         $area = Area::with('subarea')->paginate(90);
        //  dd($area);
        // $area = Area::paginate(50);
        return view('admin.area.index', compact('area'));
    }

    public function create()
    {
        $kerjasama = Kerjasama::all();
        return view('admin.area.create', compact('kerjasama'));
    }

    public function store(AreaRequest $request)
    {
        $area = new Area();

        $area = [
            'kerjasama_id' => $request->kerjasama_id,
            'nama_area' => $request->nama_area
        ];

        Area::create($area);
        toastr()->success('Data Area Berhasil Dibuat');
        return to_route('area.index');
        
    }

    public function edit($id)
    {
        $kerjasama = Kerjasama::all();
        $area = Area::findOrFail($id);
        $subArea = Subarea::all();
        $sub = DB::table('area_sub')->where('area_id', $id)->get();
        return view('admin.area.edit', compact('area', 'kerjasama', 'sub', 'subArea'));
    }

    public function update(Request $request, $id)
    {
        // $area = [
        //     'kerjasama_id' => $request->kerjasama_id,
        //     'nama_area' => $request->nama_area
        // ];

        // $areaId = Area::findOrFail($id);
        // $areaId->update($area);
        // toastr()->success('Data Area Berhasil Di Edit');
        // return to_route('area.index');
        $dev = Area::findOrFail($id);
        $equipmentIds = $request->input('subarea_id');

        $devisi = [
            'kerjasama_id' => $request->kerjasama_id,
            'nama_area' => $request->nama_area
        ];
        if($request->input('delete_sub', [])){
            $alatDelete = $request->input('delete_sub', []);
            $dev->Subarea()->detach($alatDelete);    
        }
        $dev->Subarea()->attach($equipmentIds);
        // dd($devisi, $dev);

        $dev->update($devisi);
        toastr()->success('Data Telah Ter Update');
        return redirect()->to(route('area.index'));

    }

    public function destroy($id)
    {
        $areaId = Area::findOrFail($id);
        $areaId->delete();
        toastr()->warning('Data Area Berhasil delete');
        return redirect()->back();
    }
}
