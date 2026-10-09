<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Models\AreaSub;
use App\Models\Finalisasi;

class ChecklistController extends Controller
{
    public function index(Request $request)
    { 
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $final = Finalisasi::all();
    
        $checklistQuery = Checklist::query();
    
        if ($startDate == $endDate) {
            // If start date and end date are the same, use whereDate
            $checklistQuery->whereDate('created_at', $startDate);
            $checklist = $checklistQuery->paginate(2000);
        } else {
            // If start date and end date are different, use whereBetween
            $checklist = Checklist::whereBetween('created_at', [$startDate, $endDate])->paginate(2000);
        }
        
        $checklistApproved = $checklistQuery->selectRaw('id')->get();
        if($request->has('allow'))
        {
            /**
             * *
             * 
             * @this return $request and checklist_id
             * approve checklist
             * int arr
             **/
            $this->signatureChecklist($request, $request->input('checklist_id', []));
        }

       
        return view('admin.checklist.index', compact('checklist', 'startDate', 'endDate', 'checklistApproved', 'final'));
    }
    
    public function signatureChecklist(Request $request, $checklistApproved)
    {
         foreach($checklistApproved as $i)
        {
            $finalisasi = new Finalisasi;
            $finalisasi->checklist_id = $i;
            $finalisasi->approve = $request->signature;
            $finalisasi->save();
            
        }
            if($request->signature != "Not Approve")
            {
                toastr()->success('Success to Approve Checklist');
                return redirect()->back();
            }else{
                toastr()->error('Checklist Not Approve');
            }
        
    }
    
    public function signatureChecklistAJX(Request $request)
    {
        $checklistApproved = $request->input('checklist_id');
         foreach($checklistApproved as $i)
        {
            $finalisasi = new Finalisasi;
            $finalisasi->checklist_id = $i;
            $finalisasi->approve = $request->signature;
            $finalisasi->save();
            
        }
            if($request->signature != "Not Approve")
            {
                toastr()->success('Success to Approve Checklist');
                return redirect()->back();
            }else{
                toastr()->error('Checklist Not Approve');
            }
        
    }
    
    public function create()
    {
        $areasub = AreaSub::all();
        return view('admin.checklist.create', compact('areasub'));
    }
    
    public function store(ChecklistRequest $request)
    {
        $check = new Checklist;
        
        try {
            $check = [
                'area' => $request->area,
                'sub_area' => $request->sub_area,
                'tingkat_bersih' => $request->tingkat_bersih
            ];
            
            Checklist::create($check);
            toastr()->success('Success to create Checklist');
            return to_route('admin.checklist.index');
            
        }catch (\Exception $e) 
        {
            toastr()->error('Some Field Cannot Blank');
            return redirect()->back();
        }
    }
    
    public function edit($id)
    {
        $checkId = Checklist::findOrFail($id);
        return view('admin.checklist.edit', compact('checkId'));
    }
    
    public function show($id)
    {
        $checkId = Checklist::findOrFail($id);
        return view('admin.checklist.show', compact('checkId'));
    }
    
    public function update(Request $request, $id)
    {
        try {
            $check = [
                 'area' => $request->area,
                'sub_area' => $request->sub_area,
                'tingkat_bersih' => $request->tingkat_bersih
            ];
            
            $checkId = Checklist::findOrFail($id);
            $checkId->update($check);
            toastr()->success('Success to update Checklist');
            return to_route('admin.checklist.index');
            
        }catch (\Exception $e) 
        {
            toastr()->error('Some Field Cannot Blank');
            return redirect()->back();
        }
    }
    
    public function destroy($id)
    {
        $checkId = Checklist::findOrFail($id);
        $checkId->delete();
        toastr()->warning('Data Chelist Has Been Remove');
        return redirect()->back();
    }
}
