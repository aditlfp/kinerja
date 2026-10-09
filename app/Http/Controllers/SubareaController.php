<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Subarea;
use App\Models\Area;

class SubareaController extends Controller
{
    public function index()
    {
        $sub = Subarea::paginate(50);
        return view('admin.subarea.index', compact('sub'));
    }
    
    public function create()
    {
        return view('admin.subarea.create');
    }
    
    public function store(Request $request)
    {
        
        $rules = [
            'name' => 'required'    
        ];
        
         $customMessages = [
                'required' => 'Kolom :attribute tidak boleh kosong.',
            ];
        
        // Melakukan validasi
        $validator = Validator::make($request->all(), $rules, $customMessages);
    
        if ($validator->fails()) {
            toastr()->error('Formulir tidak lengkap. Mohon isi semua kolom.');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $names = $request->name;
        foreach ($names as $name) 
        {
            Subarea::create([
                'name' => $name,
            ]);
        }
        toastr()->success('Sub Area Berhasil ditambahkan');
        return redirect()->back();
    }
    
    public function edit($id)
    {
        $subId = Subarea::findOrFail($id);
        return view('admin.subarea.edit', compact('subId'));
    }
    
    public function Update(Request $request, $id)
    {
        $subData = [
            'name' => $request->name,
        ];
        
        $subId = Subarea::findOrFail($id);
        $subId->update($subData);
        toastr()->success('Sub Area Berhasil Di Update');
        return to_route('admin.subarea.index');
    }
    
    public function destroy($id)
    {
        $subId = Subarea::findOrFail($id);
        $subId->delete();
        toastr()->warning('Sub Area Dihapus Permanent');
    }
    
    public function editSubarea($areaId)
    {
        
        $area = Area::findOrFail($areaId);
        $sub = Subarea::all();
        
        return view('admin.area.add',compact('sub', 'area'));
    }

    public function addSub(Request $request, $areaId)
    {
        $area = Area::findOrFail($areaId);
        $equipmentIds = $request->input('subarea_id');

        $area->Subarea()->attach($equipmentIds);
        // dd($divisi);
        toastr()->success('Devisi berhasil dibuat');
        return redirect()->back();
    }
    
    
    
    
}
