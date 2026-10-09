<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\Kerjasama;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index()
    {
        $client = Client::paginate(20);
        return view('admin.client.index', compact('client'));
    }

    public function create()
    {
        $client = Client::all();
        $user = User::all();
        return view('admin.client.create', compact('client', 'user'));
    }

    public function store(ClientRequest $request)
    {
        $client = new Client();

        $client = [
            'name' => $request->name,
            'panggilan' => $request->panggilan,
            'address' => $request->address,
            'province' => $request->province,
            'kabupaten' => $request->kabupaten,
            'zipcode' => $request->zipcode,
            'email' => $request->email,
            'phone' => $request->phone,
            'fax' => $request->fax,
            'logo' => $request->logo,
        ];

        if ($request->hasFile('logo')) {
            $client['logo'] = UploadImage($request, 'logo');
        }else{
            toastr()->error('Logo harus ditambahkan');
        }
        // dd($request->all());
        try {
            Client::create($client);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
            toastr()->success('Client Berhasil Ditambahkan');
            return redirect()->to(route('admin.client.index'));

    }

    public function show($id)
    {
        $client = Client::find($id);
        if ($client != null) {
            return view('admin.client.show', compact('client'));
        }
        toastr()->error('Data Tidak Ditemukan');
        return redirect()->back();
    }

    public function edit($id)
    {
        $client = Client::find($id);
        if ($client != null) {
            return view('admin.client.edit', compact('client'));
        }
        toastr()->error('Data Tidak Ditemukan');
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $client = [
            'name' => $request->name,
            'panggilan' => $request->panggilan,
            'address' => $request->address,
            'province' => $request->province,
            'kabupaten' => $request->kabupaten,
            'zipcode' => $request->zipcode,
            'email' => $request->email,
            'phone' => $request->phone,
            'fax' => $request->fax,
            // 'logo' => $request->logo,

        ];

        if($request->hasFile('logo'))
        {
            if($request->oldimage)
            {
                Storage::disk('public')->delete('images/' . $request->oldimage);
            }

            $client['logo'] = UploadImage($request, 'logo');
        }else{
            $client['logo'] = $request->oldimage;
        }
         try {
            Client::findOrFail($id)->update($client);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada');
           return redirect()->back();
        }
        toastr()->success('Client berhasil diedit');
        return redirect()->to(route('admin.client.index'));
    }

    public function destroy($id)
    {
        // Use a database transaction to ensure both deletes succeed or fail together.
        DB::beginTransaction();

        try {
            // Find the client or fail with a 404 error
            $client = Client::findOrFail($id);

            // Delete all related 'kerjasama' records first.
            // This is efficient and runs a single DELETE query.
            $client->kerjasama()->delete();

            // Now, delete the client itself.
            $client->delete();

            // If we got here, commit the transaction to make the changes permanent.
            DB::commit();

            toastr()->success('Client dan semua data kerjasamanya berhasil dihapus.');
            return redirect()->route('admin.client.index');

        } catch (ModelNotFoundException $e) {
            // This will catch the error from findOrFail if the client doesn't exist.
            DB::rollBack(); // Rollback any changes (though none were made)
            toastr()->error('Data klien tidak ditemukan.');
            return redirect()->back();

        } catch (\Exception $e) {
            // Catch any other unexpected exceptions (e.g., database connection issues).
            DB::rollBack(); // Rollback any changes made during the transaction.
            // Log the detailed error for debugging
            Log::error('Failed to delete client and kerjasama: ' . $e->getMessage());
            toastr()->error('Terjadi kesalahan saat menghapus data.');
            return redirect()->back();
        }
    }
}
