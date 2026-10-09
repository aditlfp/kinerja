<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanRequest;
use App\Models\Laporan;
use App\Models\Ruangan;
use App\Models\Kerjasama;
use App\Models\User;
use App\Models\ListPekerjaan;
use App\Models\LaporanMitra;
use App\Models\UploadImage;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{

    public function __construct(Request $request)
    {
        $this->str = $request->input('str1');
        $this->ended = $request->input('end1');
    }
    
    public function getcode($ruanganId, $kerjasamaId)
    {
        // echo $ruanganId, $kerjasamaId;
        return $this->create($ruanganId, $kerjasamaId);
    }

    public function index()
    {
        try {
            if (Auth::user()->role_id == 1) {
                $laporan = Laporan::orderBy('created_at', 'desc')->where('client_id', Auth::user()->kerjasama->client_id)->paginate(90);
                return view('laporan.index', ['laporan' => $laporan]);
            }elseif(Auth::user()->role_id == 2)
            {
                $min1 = Laporan::orderBy('created_at', 'asc')->first();
                $min = $min1->created_at->format('Y-m-d');
                
                $max1 = Laporan::orderBy('created_at', 'desc')->first();
                $max = $max1->created_at->subMonth(3)->format('Y-m-d');
                
                // dd($min, $max);
                
                $mitra = Kerjasama::all();
                $ruangan = Ruangan::all();
                $laporan = UploadImage::orderBy('created_at', 'desc')->paginate(30);
                return view('laporan.index', ['laporan' => $laporan, 'mitra' => $mitra, 'ruangan' => $ruangan, 'min' => $min, 'max' => $max]);
            }elseif(Auth::user()->divisi->jabatan->code_jabatan == 'MITRA'){
                $ker = Auth::user()->kerjasama->client_id;
                $laporan = Laporan::where('client_id', $ker)->paginate(25);
                return view('laporan.index', ['laporan' => $laporan]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

    }
    public function create($ruanganId, $kerjasamaId)
    {
        $kerjasama = Kerjasama::where('id', $kerjasamaId)->first();
        $ruangan = Ruangan::where('kerjasama_id', $kerjasamaId)->where('id', $ruanganId)->first();
        $listPekerjaan = ListPekerjaan::where('ruangan_id', $ruanganId)->get();
        return view('laporan.create', ['ruangan' => $ruangan, 'listPekerjaan' => $listPekerjaan, 'kerjasama' => $kerjasama]);
    }

    public function store(LaporanRequest $request)
    {
        $laporan = new Laporan();

        $laporan = [
            'user_id' => $request->user_id,
            'client_id' => $request->client_id,
            'ruangan_id' => $request->ruangan_id,
            'image1' => $request->image1,
            'image2' => $request->image2,
            'image3' => $request->image3,
            'image4' => $request->image4,
            'image5' => $request->image5,
            'keterangan' => $request->keterangan,
            'pekerjaan' => json_encode($request->pekerjaan),
            'nilai' => $request->nilai
        ];
        
        // dd($laporan);
            $allowedImages = ['image1', 'image2', 'image3', 'image4', 'image5'];

        try {
            
            foreach ($allowedImages as $imageField) {
                if ($request->hasFile($imageField)) {
                    $laporan[$imageField] = UploadImage($request, $imageField);
                }
            }
            
            Laporan::create($laporan);
            toastr()->success('Laporan Berhasil Disimpan');
            return to_route('laporan.index');
        } catch (\Throwable $th) {
            // dd($th);
            toastr()->error('Image Must Be Insert Or Large Image');
            return redirect()->back();
        }

    }

    public function destroy($id) // Admin
    {

        try {
            $laporan = Laporan::findOrFail($id);
            $imageFields = ['image1', 'image2', 'image3', 'image4', 'image5'];

            foreach ($imageFields as $imageField) {
                if ($laporan->$imageField) {
                    Storage::disk('public')->delete('images/' . $laporan->$imageField);
                }
            }
            
            $laporan->delete();
            toastr()->warning('Laporan Berhasil Dihapus');
            return redirect()->back();
        } catch (\Throwable $th) {
            toastr()->error('Laporan Tidak Ditemukan');
        }
    }
    public function exportWith(Request $request)
    {
        $tanggalSekarang = Carbon::now();
        $currentMonth = Carbon::parse($this->ended)->month;
        $currentYear = Carbon::parse($this->str)->year;
        $str1 = $this->str;
        $end1 = $this->ended;
        
        $mitra = $request->input('client_id');
        $ruangan = $request->input('ruangan_id');
        $nilai = $request->nilai;
        
        $kerjasama = Kerjasama::firstWhere('id', $mitra);
        
        // dd($nilai);
        
        $totalHari =  Carbon::parse($this->ended)->diffInDays(Carbon::parse($this->str));
        
        if($request->has(['client_id', 'end1', 'str1'])) {
            
        //  $expPDF = User::with(['laporan' => function ($query) use ($str1, $end1) {
        //     return $query->whereBetween('created_at', [$str1, $end1]);
        // }])->when($mitra, function($query) use ($mitra) {
        //      $query->whereHas('kerjasama', function ($subQuery) use ($mitra) {
        //         $subQuery->where('client_id', $mitra);
        //     });
        // })->get();
        
        if($str1 != $end1 && !$request->has('ruangan_id') && !$request->has('nilai')){
            // $expPDF = Laporan::with(['User'])->whereBetween('created_at', [$str1, $end1])->where('client_id', $mitra)->where('nilai', '!=', 'baik')->get();
            $expPDF = Laporan::with(['user'])->whereBetween('created_at', [$str1, $end1])->where('client_id', $mitra)->get();
        }else if($str1 != $end1 && $request->has('ruangan_id') && !$request->has('nilai')){
            $expPDF = Laporan::with(['user'])->whereBetween('created_at', [$str1, $end1])->where('client_id', $mitra)->where('ruangan_id', $ruangan)->get();
        }else if($str1 != $end1 && !$request->has('ruangan_id') && $request->has('nilai')){
            $expPDF = Laporan::with(['user'])->whereBetween('created_at', [$str1, $end1])->where('client_id', $mitra)->whereIn('nilai', $nilai)->get();
        }else if($str1 != $end1 && $request->has('ruangan_id') && $request->has('nilai')){
            $expPDF = Laporan::with(['user'])->whereBetween('created_at', [$str1, $end1])->where('client_id', $mitra)->where('ruangan_id', $ruangan)->get();
        }else{
            $expPDF = Laporan::with(['user'])->whereDate('created_at', $str1)->where('client_id', $mitra)->get();
        }
        
        // dd($expPDF);

        $path = 'logo/sac.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('compression', true);
        // $options->set('imageQuality', 1);

        $pdf = new Dompdf($options);
        $html = view('laporan.export', compact('expPDF', 'base64', 'totalHari', 'currentYear', 'currentMonth', 'str1', 'end1', 'mitra', 'kerjasama'))->render();
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $output = $pdf->output();
        $filename = 'laporan '. $kerjasama?->client?->name . '_' . $str1 .'-'. $end1 .'.pdf';

        if ($request->input('action') == 'download') {
            return response()->stream(function () use ($output) {
                echo $output;
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                
            ]);
        }

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
                    
        }else{
            toastr()->error('Mohon Masukkan Filter Export');
            return redirect()->back();
        }
    }
    
    public function hapusFotoLaporan(Request $request)
    {
        $mulai = $request->mulai;
        $selesai = $request->selesai;
        
        $laporan = Laporan::whereBetween('created_at', [$mulai, $selesai])->get();
        // dd($mulai, $selesai, $laporan);
        
        // dd($laporan, $laporan[0]->image5, Storage::disk('public')->exists('images/' . $laporan[0]->image5), public_path('storage/images/' . $laporan[0]->image5));
        
        foreach($laporan as $abs){
            if ($abs->image1 != null) {
                Storage::disk('public')->delete('images/' . $abs->image1);
            }
            if ($abs->image2 != null) {
                Storage::disk('public')->delete('images/' . $abs->image2);
            }
            if ($abs->image3 != null) {
                Storage::disk('public')->delete('images/' . $abs->image3);
            }
            if ($abs->image4 != null) {
                Storage::disk('public')->delete('images/' . $abs->image4);
            }
            if ($abs->image5 != null) {
                Storage::disk('public')->delete('images/' . $abs->image5);
            }
            
            $abs->delete();
        }
        
        toastr()->warning('Data Sudah Dihapus');
        return redirect()->back();
    }
    
    // laporan mitra
    public function indexLaporanMitra() {
        if(Auth::user()->divisi->jabatan->code_jabatan == "MITRA"){
            $laporanMitra = LaporanMitra::where('kerjasama_id', Auth::user()->kerjasama_id)->get();
        }else{
            $laporanMitra = LaporanMitra::all();
        }
        return view('admin.laporanMitra.index', compact('laporanMitra'));
    }
    public function createLaporanMitra() {
        $laporanMitra = LaporanMitra::all();
        $kerjasama = Kerjasama::all();
        return view('admin.laporanMitra.create', compact('laporanMitra', 'kerjasama'));
    }
    public function storeLaporanMitra(Request $request) {
        
        $laporan = new LaporanMitra();

        $laporan = [
            'kerjasama_id' => $request->kerjasama_id,
            'file_pdf' => $request->file_pdf,
        ];

        if ($request->hasFile('file_pdf')) {
            $laporan['file_pdf'] = UploadFile($request, 'file_pdf');
        }else{
            toastr()->error('File harus ditambahkan');
        }
        // dd($request->all(), $laporan);
        try {
            LaporanMitra::create($laporan);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
            toastr()->success('Laporan Berhasil Ditambahkan');
            return redirect()->to(route('laporanMitra.index'));
    }
    public function editLaporanMitra($id) {
        $laporanMitra = LaporanMitra::findOrFail($id);
        $kerjasama = Kerjasama::all();
        return view('admin.laporanMitra.edit', compact('laporanMitra', 'kerjasama'));
    }
    public function updateLaporanMitra(Request $request, $id) {
        $laporan = [
            'kerjasama_id' => $request->kerjasama_id,
            'file_pdf' => $request->file_pdf,
        ];

        if($request->hasFile('file_pdf'))
        {
            if($request->oldfile)
            {
                Storage::disk('public')->delete('pdf/' . $request->oldfile);
            }

            $laporan['file_pdf'] = UploadFile($request, 'file_pdf');
        }else{
            $laporan['file_pdf'] = $request->oldfile;
        }
        // dd($laporan, $request->all());
         try {
            LaporanMitra::findOrFail($id)->update($laporan);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
        toastr()->success('Laporan berhasil diedit');
        return redirect()->back();
        
        // $laporanMitra = LaporanMitra::all();
        // return view('admin.laporanMitra.update', compact('laporanMitra'));
    }
    public function deleteLaporanMitra($id) {
        $laporan = LaporanMitra::find($id);
        if ($laporan != null) {
            if ($laporan->file_pdf == null) {
                toastr()->error('File Tidak Ditemukan');
            }
                if ($laporan->logo) {
                    Storage::disk('public')->delete('pdf/'.$laporan->file_pdf);
                }
        }
        $laporan->delete();
        toastr()->error('Data Telah Dihapus');
        return redirect()->back();
    }
}
