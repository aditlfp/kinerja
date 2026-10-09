<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Absensi;
use App\Models\Laporan;
use App\Models\Rating;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index(){
        if(Auth::user()->role_id == 2)
        {
            $user = User::all();
        }
        else{
            if(Auth::user()->divisi->jabatan->code_jabatan == "CO-CS"){
                $codeJabatan = ['OCS', 'CO-CS'];
                $user = User::where('kerjasama_id', Auth::user()->kerjasama_id)->whereHas('divisi.jabatan', function ($query) use ($codeJabatan) {
                    $query->whereIn('code_jabatan', $codeJabatan);
                })->get();
            }else if(Auth::user()->divisi->jabatan->code_jabatan == "CO-SCR"){
                $codeJabatan = ['SCR', 'CO-SCR'];
                $user = User::where('kerjasama_id', Auth::user()->kerjasama_id)->whereHas('divisi.jabatan', function ($query) use ($codeJabatan) {
                    $query->whereIn('code_jabatan', $codeJabatan);
                })->get();
            }else{
                $user = User::where('kerjasama_id', Auth::user()->kerjasama_id)->get();
            }
        }
        $absen = Absensi::paginate(5);
        $rating = Rating::all();
        return view('rating.index', compact('user', 'absen', 'rating'));
    }

    public function store(RatingRequest $request){

        $rate = new Rating();

        $rate = [
            'leader_name' => $request->leader_name,
            'mitra_name' => $request->mitra_name,
            'isLeader' => $request->isLeader,
            'isMitra' => $request->isMitra,
            'user_id' => $request->user_id,
            'rate_leader' => $request->rate_leader,
            'rate_mitra' => $request->rate_mitra
        ];
        // dd($rate);

        Rating::create($rate);
        toastr()->success('Berhasil Memberikan Rating');
        return redirect()->back();
    }
    
    public function update(Request $request, $id){
        $rate = [
            'leader_name' => $request->leader_name,
            'mitra_name' => $request->mitra_name,
            'isLeader' => $request->isLeader,
            'isMitra' => $request->isMitra,
            'user_id' => $request->user_id,
            'rate_leader' => $request->rate_leader,
            'rate_mitra' => $request->rate_mitra
        ];
        
        // dd($rate);
        
        $rateId = Rating::findOrFail($id);
        $rateId->update($rate);
        toastr()->success('Berhasil update Rating');
        return redirect()->back();
    }

    
    public function myRate($id)
    {
        try {
            $user_id = Auth::user()->id;
            $rating = Rating::firstWhere('user_id', $id);
            return view('rating.myrate', compact('rating'));
        } catch (ModelNotFoundException $e) {
            return view('rating.err404');
        }

    }
    public function rateKerja($id) {
        $i = Carbon::now()->month;
        $user = User::findOrFail($id);
        $absensi = Absensi::where('user_id', $id)->whereMonth('tanggal_absen', $i)->paginate(100);
        $laporan = Laporan::where('user_id', $id)->whereMonth('created_at', $i)->paginate(200);
        $rating = Rating::where('user_id', $id)->get();
        return view('rating.riwayat-kerja', compact('absensi', 'laporan', 'rating', 'user'));
    }
}
