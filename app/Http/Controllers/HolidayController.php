<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
   public function index()
   {

        $holiday = Holiday::orderBy('tanggal_libur', 'desc')->paginate(15);
        return view('admin.holiday.index', ['holiday' => $holiday]);
   }

   public function create()
   {
        return view('admin.holiday.create');
   }

   public function store(HolidayRequest $request)
   {
        $holiday = new Holiday();

        $holiday = [
            'name' => $request->name,
            'tanggal_libur' => $request->tanggal_libur
        ];

        Holiday::create($holiday);
        toastSuccess('Berhasil Menambahkan Hari Libur');
        return to_route('admin.holiday.index');
   }

   public function edit($id)
   {
    try {
        $holidayId = Holiday::findorFail($id);
        return view('admin.holiday.edit', ['holiday' => $holidayId]);
    } catch (ModelNotFoundException $th) {
        toastError('Hari Libur Tidak Ditemukan');
        return redirect()->back();
    }
   }

   public function update(Request $request, $id)
   {
        $holiday = [
            'name' => $request->name,
            'tanggal_libur' => $request->tanggal_libur
        ];

        $holidayId = Holiday::findorFail($id);
        $holidayId->update($holiday);
        toastSuccess('Hari Libur Telah Di Update');
        return to_route('admin.holiday.index');

   }

   public function destroy($id)
   {
        try {
            $holidayId = Holiday::findorFail($id);
            $holidayId->delete();
            toastWarning('Hari Libur Telah Di Delete');
            return redirect()->back();
        } catch (ModelNotFoundException $th) {
            toastError('Hari Libur Tidak Ditemukan');
            return redirect()->back();
        }
   }
}
