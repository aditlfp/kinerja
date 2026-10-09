<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Client;
use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\Kerjasama;
use App\Models\User;
use App\Models\TempUser;
use App\Models\Jabatan;
use App\Notifications\OtpNotif;
use App\Notifications\RegSukses;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SadiqSalau\LaravelOtp\Facades\Otp;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $kerjasama = Kerjasama::all();
        $client = Client::all();
        $dev = Divisi::all();
        $user = User::with('kerjasama')->orderBy('name', 'desc');
        $user->when($request->filterKerjasama, function ($query) use ($request) {
            return $query->where('kerjasama_id', $request->filterKerjasama);
        });
        $user->when($request->search, function ($query) use ($request) {
            $search = trim($request->search);
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        });

        $user = $user->paginate(100);
        $user->appends($request->only(['filterKerjasama', 'search']));

        if ($request->boolean('ajax')) {
            $items = $user->getCollection()->map(function ($item) {
                $defaultImg = asset('/logo/person.png');
                if ($item->image === 'no-image.jpg') {
                    $imageUrl = $defaultImg;
                } elseif (Storage::disk('public')->exists('images/' . $item->image)) {
                    $imageUrl = asset('storage/images/' . $item->image);
                } elseif (Storage::disk('public')->exists('user/' . $item->image)) {
                    $imageUrl = asset('storage/user/' . $item->image);
                } else {
                    $imageUrl = $defaultImg;
                }

                $nik = '---';
                if ($item->nik) {
                    try {
                        $nik = Crypt::decryptString($item->nik);
                    } catch (\Throwable $th) {
                        $nik = '---';
                    }
                }

                $kerjasamaName = optional(optional($item->kerjasama)->client)->panggilan
                    ? optional(optional($item->kerjasama)->client)->panggilan
                    : (optional(optional($item->kerjasama)->client)->name ?? 'kosong');

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'nama_lengkap' => $item->nama_lengkap,
                    'email' => $item->email,
                    'nik' => $nik,
                    'no_hp' => $item->no_hp ? '+62' . $item->no_hp : '---',
                    'kerjasama_id' => $item->kerjasama_id,
                    'kerjasama_name' => $kerjasamaName,
                    'image_url' => $imageUrl,
                    'edit_url' => url('users/' . $item->id . '/edit'),
                ];
            });

            return response()->json([
                'data' => $items,
                'pagination' => [
                    'current_page' => $user->currentPage(),
                    'last_page' => $user->lastPage(),
                    'per_page' => $user->perPage(),
                    'total' => $user->total(),
                    'from' => $user->firstItem() ?? 0,
                    'to' => $user->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.user.index', ['user' => $user, 'kerjasama' => $kerjasama, 'client' => $client, 'dev' => $dev, 'filterKerjasama' => $request->filterKerjasama, 'search' => $request->search]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dev = Divisi::all();
        $data = Kerjasama::all();
        $jabatan = Jabatan::all();

        $lastUser = $this->userService->generateSacUsername(); 

        return view('admin.user.create', compact('data', 'dev', 'lastUser', 'jabatan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $user = new User();
        $user = [
            'kerjasama_id' => $request->kerjasama_id,
            'devisi_id' => $request->devisi_id,
            'jabatan_id' => $request->jabatan_id,
            'name' => $request->name,
            'email' => $request->email,
            'image' => $request->image,
            'nama_lengkap' => $request->nama_lengkap,
            'password' => Hash::make($request->password),
            'nik' => Crypt::encryptString($request->nik),
            'no_hp' => normalizePhone($request->no_hp)
        ];

        if ($request->hasFile('image')) {
            $user['image'] = UploadImageV2($request, 'image');
        }
        try {
            User::create($user);
        } catch (\Illuminate\Database\QueryException $e) {
            // dd($e);
            toastr()->error('Data Sudah Ada');
            return redirect()->back();
        }
        toastr()->success('User Berhasil Ditambahkan');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $dev = Divisi::all();
        $kerjasama = Kerjasama::all();
        $user = User::find($id);
        $dataUser = User::findOrFail($id);
        $jabatan = Jabatan::all();

        if ($user != null) {
            return view('admin.user.edit', compact('user', 'kerjasama', 'dev', 'dataUser', 'jabatan'));
        }
        toastr()->error('Data tidak tidak ditemukan');
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = [
            'kerjasama_id' => $request->kerjasama_id,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'devisi_id' => $request->devisi_id,
            'jabatan_id' => $request->jabatan_id,
            'email' => $request->email,
            'image' => $request->image,
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => Crypt::encryptString($request->nik),
            'no_hp' => normalizePhone($request->no_hp)
        ];

        if ($request->password == null) {
            unset($user['password']);
        }

        if ($request->hasFile('image')) {
            if ($request->oldimage) {
                Storage::disk('public')->delete('images/' . $request->oldimage);
            }

            $user['image'] = UploadImageV2($request, 'image');
        } else {
            $user['image'] = $request->oldimage;
        }
        try {
            User::findOrFail($id)->update($user);
        } catch (\Illuminate\Database\QueryException $e) {
            // dd($e);
            toastr()->error($e);
            return redirect()->back();
        }


        toastr()->success('Data Berhasil diupdate');
        return to_route('admin.user.index');
        // return redirect()->back();
    }

    public function massUpdate(Request $request)
    {
        $kerjasama = $request->input('kerjasama');
        $devisi = $request->input('devisi');
        $field = $request->input('field');
        $oldValue = $request->input('old_value');
        $newValue = $request->input('new_value');

        // dd($request->all());

        // Perform the mass update
        if ($kerjasama) {
            User::where($field, $oldValue)->where('devisi_id', $devisi)->where('kerjasama_id', $kerjasama)->update([$field => $newValue]);
        } else {
            User::where($field, $oldValue)->where('devisi_id', $devisi)->update([$field => $newValue]);
        }

        toastr()->success('Data ' . $field . ' Berhasil diupdate');
        return redirect()->back();
    }

    public function show($id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $absen = Absensi::firstWhere('user_id', $id);
        // dd($user, $id);

        if ($user != null) {
            if ($user->image == null) {
                toastr()->error('Image Tidak Ditemukan');
            }
            if ($user->image) {
                Storage::disk('public')->delete('images/' . $user->image);
            }
            if ($absen != null) {
                if ($absen->image) {
                    Storage::disk('public')->delete('images/' . $absen->image);
                }
                $absen->delete();
            }

            $user->delete();
            toastr()->warning('Data User Telah Dihapus');
            return to_route('admin.user.index');
        } else {
            toastr()->error('Data Tidak Ditemukan');
            return redirect()->back();
        }
    }

    public function addKaryawanIndex()
    {
        $kerjasama = Kerjasama::whereNotIn('id', [1])->get();
        $devisi = Divisi::whereNotIn('id', [2, 3, 4, 7, 8, 9, 10, 11, 12, 14, 18, 20, 24, 26])->get();
        // sendOtpReg('syafimq00@gmail.com');
        return view('admin.user.addKaryawan.index', compact('kerjasama', 'devisi'));
    }

    public function addKaryawanStore(Request $request)
    {
        try {
            $newUsername = $this->userService->generateSacUsername();
        } catch (\Throwable $th) {
            Log::error('Username generation failed. Ensure your cache driver supports atomic operations (e.g., Redis). ' . $th->getMessage());
            toastr()->error('System is busy, please try again in a moment.');
            return redirect()->back();
        }

        try {
            $imagePath = $request->hasFile('image') ? UploadImageUser($request, 'image') : null;
            $ktpPath = $request->hasFile('ktp') ? UploadImageUser($request, 'ktp') : null;

            $userData = [
                'username'      => $newUsername,
                'nama_lengkap'  => $request->nama_lengkap,
                'password'      => Hash::make($request->password),
                'pw'            => $request->password,
                'email'         => $request->email,
                'alamat'        => $request->alamat,
                'image'         => $imagePath,
                'img_ktp_dpn'   => $ktpPath,
                'ttl'           => $request->tpt . ', ' . $request->tgl,
                'nik'           => Crypt::encryptString($request->nik),
                'no_kk'         => Crypt::encryptString($request->kk),
                'no_hp'         => normalizePhone($request->no_hp),
                'client_id'     => $request->client_id,
                'devisi_id'     => $request->devisi_id,
                'jabatan_id'    => Divisi::find($request->devisi_id)->jabatan_id,
            ];

            TempUser::create([
                'data'   => json_encode($userData),
                'status' => 0,
            ]);

            Notification::route('mail', $request->email)
                ->notify(new RegSukses($newUsername, $request->password));

            Cache::forget("otp_{$request->email}");
            return view('admin.user.addKaryawan.wait');
        } catch (\Throwable $th) {
            Log::error('Failed to create temp user: ' . $th->getMessage());
            toastr()->error('An error occurred while saving your data. Please try again.');
            return redirect()->back();
        }
    }

    public function addKaryawanAdminIndex()
    {
        $kerjasama = Kerjasama::all();
        $devisi = Divisi::whereNotIn('id', [2, 3, 4, 7, 8, 9, 10, 11, 12, 14, 18, 20, 24, 26])->get();
        return view('admin.user.addKaryawan.adminIndex', compact('kerjasama', 'devisi'));
    }

    public function addKaryawanStatus(Request $request)
    {
        $kerjasama = Kerjasama::all();
        $devisi = Divisi::whereNotIn('id', [2, 3, 4, 7, 8, 9, 10, 11, 12, 14, 18, 20, 24, 26])->get();
        return view('admin.user.addKaryawan.adminIndex', compact('kerjasama', 'devisi'));
    }
}
