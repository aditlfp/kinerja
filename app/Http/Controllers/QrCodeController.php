<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Client;
use App\Models\QrCode;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as GenerateQr;

class QrCodeController extends Controller
{
    
    public function index()
    {
        $kerjasama = Kerjasama::all();
        $qr = QrCode::paginate(50);
        return view('admin.qrcode.index', compact('qr', 'kerjasama'));
    }

    public function create()
    {
        $qr = QrCode::all();
        $ruangan = Ruangan::all();
        $kerjasama = Kerjasama::all();
        return view('admin.qrcode.create', compact('ruangan', 'kerjasama', 'qr'));
    }
    
    public function edit($id)
    {
        $ruangan = Ruangan::all();
        $kerjasama = Kerjasama::all();
        $qr = QrCode::findOrFail($id);
        return view('admin.qrcode.edit', compact('qr', 'ruangan', 'kerjasama'));
    }
    
    public function update(Request $request, $id)
    {
        $qrId = QrCode::where('id', $id)->first();
        
        $kerjasama = Kerjasama::find($request->kerjasama_id ? $request->kerjasama_id : $qrId->kerjasama_id);
        $client = Client::find($kerjasama->client_id);
        
        $qrCode = GenerateQr::format('png')
            ->mergeString(Storage::disk('public')->get('images/' . $client->logo), 0.41)
            ->errorCorrection('H')
            ->generate($request->ruangan_id . '/' . $request->kerjasama_id);
        
        // Generate a random number for the filename
        $randomNumber = mt_rand(1, 999999999999);
        
        // Specify the storage path and filename
        $storagePath = 'images';
        $filename = $randomNumber . '.png';
        
        // Save the QR code image to storage
        Storage::disk('public')->delete('images/'.$qrId->qr_code);
        Storage::disk('public')->put($storagePath . '/' . $filename, $qrCode);
        $qr = [
            'qr_code' => $filename,
            'ruangan_id' => $request->ruangan_id,
            'kerjasama_id' => $request->kerjasama_id,    
        ];
        
        $qrId->update($qr);
        
        toastr()->success('Qr Code Has Update & Generate');
        return to_route('qrcode.index');
    }

    public function store(Request $request)
    {
        
        // Fetch the Kerjasama and Client data
        $kerjasama = Kerjasama::find($request->kerjasama_id);
        $client = Client::find($kerjasama->client_id);
        
        // Generate the QR code with the client's logo
       $qrCode = GenerateQr::format('png')
            ->mergeString(Storage::disk('public')->get('images/' . $client->logo), 0.41)
            ->errorCorrection('H')
            ->generate($request->ruangan_id . '/' . $request->kerjasama_id);
        
        // Generate a random number for the filename
        $randomNumber = mt_rand(1, 999999999999);
        
        // Specify the storage path and filename
        $storagePath = 'images';
        $filename = $randomNumber . '.png';
        
        // Save the QR code image to storage
        Storage::disk('public')->put($storagePath . '/' . $filename, $qrCode);
        
        // Save the QR code and related data
        QrCode::create([
            'qr_code' => $filename,
            'ruangan_id' => $request->ruangan_id,
            'kerjasama_id' => $request->kerjasama_id,
        ]);
        
        toastr()->success('QR Code has been generated successfully');
        return redirect()->route('qrcode.index');

    }
    
    public function destroy($id)
    {
        $qrId = QrCode::findOrFail($id);
        Storage::disk('public')->delete('images/'.$qrId->qr_code);
        $qrId->delete();
        toastr()->warning('Qr Code Has Deleted');
        return redirect()->back();
    }
    
    public function exportPDF(Request $request)
    {
        $selectedItems = $request->input('selected_items');
        
        if($request->has(['type_export']) && $request->type_export == 1 && $selectedItems){
            $qr = QrCode::whereIn('id', $selectedItems)->get();
        }elseif($request->has(['kerjasama_id']) && $request->type_export == 0){
            $qr = QrCode::where('kerjasama_id', $request->kerjasama_id)->get();
        }else{
            toastr()->error('Mohon Masukkan Filter Export');
            return redirect()->back();
        }
        
        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $pdf = new Dompdf($options);
        $html = view('admin.qrcode.export', compact('qr'))->render();
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $output = $pdf->output();
        $filename = 'qr_code.pdf';

        if ($request->input('action') == 'download') {
            return response()->download($output, $filename);
        }

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
                    
       
    }
}
