<?php

namespace App\Http\Controllers;

use App\Models\ListPekerjaan;
use Illuminate\Http\Request;
use App\Models\Ruangan;
use App\Imports\ListImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ListPekerjaanRequest;
use Maatwebsite\Excel\Validators\ValidationException;

class ListPekerjaanController extends Controller
{
    public function index()
    {
        $listPekerjaans = ListPekerjaan::orderBy('ruangan_id', 'asc')->paginate(50);
        $ruangan = Ruangan::all();
        return view('admin.list_pekerjaans.index', compact('listPekerjaans', 'ruangan'));
    }

    public function create()
    {
        $ruangan = Ruangan::all();
        $listPekerjaans = ListPekerjaan::all();
        return view('admin.list_pekerjaans.create', compact('ruangan', 'listPekerjaans'));
    }

    public function store(ListPekerjaanRequest $request)
    {
        $pekerjaan = new ListPekerjaan();
        
        $pekerjaan = [
            'name' => json_encode($request->name),
            'ruangan_id' => $request->ruangan_id,
        ];
        

        // Decode the JSON string to an array
        $nameArray = json_decode($pekerjaan['name'], true);
        
        // Find the index of null in the array
        
        $nullIndex = array_search(null, $nameArray);
        
        // Remove null if found
        if ($nullIndex !== false) {
            unset($nameArray[$nullIndex]);
        }
        
        // Encode the modified array back to JSON
        $pekerjaan['name'] = json_encode($nameArray);

        ListPekerjaan::create($pekerjaan);
        toastr()->success('Data List Berhasil Disimpan');
        return to_route('admin.list-pekerjaan.index');
    }

    public function edit(ListPekerjaan $listPekerjaan)
    {
        // Add logic to fetch ruangan data if needed
        $ruangans = Ruangan::all();
        // return view('list_pekerjaans.edit', compact('listPekerjaan', 'ruangans'));
        return view('admin.list_pekerjaans.edit', compact('listPekerjaan', 'ruangans'));
    }

    public function update(Request $request, ListPekerjaan $listPekerjaan)
    {
        $pekerjaan = [
            'name' => json_encode($request->name),
            'ruangan_id' => $request->ruangan_id,
        ];
        
        if($request->has('name'))
        {
            // Decode the JSON string to an array
            $nameArray = json_decode($pekerjaan['name'], true);
            
            // Find the index of null in the array
            $nullIndex = array_search(null, $nameArray);
            
            // Remove null if found
            if ($nullIndex !== false) {
                unset($nameArray[$nullIndex]);
            }
            
            // Encode the modified array back to JSON
            $pekerjaan['name'] = json_encode($nameArray);
            
            
            $listPekerjaan->update($pekerjaan);
            toastr()->success('Data List Berhasil Di Update');
            return to_route('admin.list-pekerjaan.index');
        }else{
            toastr()->error('Eitss Input nya Kok Kosong!');
            return redirect()->back();
        }
        
    }

    public function destroy(ListPekerjaan $listPekerjaan)
    {
        $listPekerjaan->delete();
        toastr()->warning('Data List Berhasil Di Deleted');
        return to_route('admin.list-pekerjaan.index');
    }
    
    public function importExcel(Request $request)
    {
         try {
            Excel::import(new ListImport,  $request->file);
            toastr()->success('Data Berhasil Di Upload');
            return redirect()->back();
        } catch (\Exception $e) {
            // Handle other exceptions
                $errorMessage = $e->getMessage();
                return $errorMessage;
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
