<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsRequest;
use App\Http\Requests\NewsUpdateRequest;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::paginate(50);
        return view('admin.news.index', compact('news'));
    }
    
    public function create()
    {
        return view('admin.news.create');
    }
    
    public function store(NewsRequest $request)
    {
        $news = new News();
        $news = [
            'image' => $request->image,
            'tanggal_lihat' => $request->tanggal_lihat,
            'tanggal_tutup' => $request->tanggal_tutup,
            'tanggal_muncul' => $request->tanggal_muncul,
        ];
        
         if ($request->hasFile('image')) {
            $news['image'] = UploadImage($request, 'image');
        }else{
            toastr()->error('Image harus ditambahkan', [], 'error');
        }
        
         try {
            News::create($news);
        } catch(\Illuminate\Database\QueryException $e){
           toastr()->error('Data Sudah Ada', [], 'error');
           return redirect()->back();
        }
            toastr()->success('News Berhasil Ditambahkan', [], 'success');
            return redirect()->to(route('admin.news.index'));
    }
    
    public function edit($id)
    {
        $newsId = News::findOrFail($id);
        if ($newsId != null) {
            return view('admin.news.edit', compact('newsId'));
        }
        toastr()->error('Data Tidak Ditemukan', [], 'error');
        return redirect()->back();
    }
    
    public function update(NewsUpdateRequest $request, $id)
    {
        $news = News::findOrFail($id);

        // Tanpa gambar baru, kolom image tidak ikut diubah. Sebelumnya nilai ini
        // diambil dari input `oldimage` yang tidak pernah dikirim form edit,
        // sehingga simpan berita selalu gagal karena image menjadi null.
        $data = [
            'tanggal_lihat' => $request->tanggal_lihat,
            'tanggal_tutup' => $request->tanggal_tutup,
            'tanggal_muncul' => $request->tanggal_muncul,
        ];

        if ($request->hasFile('image')) {
            // Nama file lama diambil dari baris database milik berita ini,
            // bukan dari input form, supaya yang terhapus selalu file yang benar.
            if ($news->image) {
                Storage::disk('public')->delete('images/' . $news->image);
            }

            $data['image'] = UploadImage($request, 'image');
        }

        try {
            $news->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Tersimpan', [], 'error');
            return redirect()->back();
        }

        toastr()->success('Data berhasil diedit', [], 'success');
        return redirect()->to(route('admin.news.index'));
    }
    
    public function destroy($id)
    {
        $news = News::find($id);

        if ($news === null) {
            toastr()->error('Data Tidak Ditemukan', [], 'error');
            return redirect()->back();
        }

        if ($news->image) {
            Storage::disk('public')->delete('images/' . $news->image);
        }

        $news->delete();
        toastr()->success('Berita berhasil dihapus', [], 'success');
        return redirect()->back();
    }
    
    public function NewsBefore()
    {
        $newsId = News::query()->tampilPada()->get();

        return view('tes.index', compact('newsId'));
    }
    
      
    public function NewsDownload($id)
    {
        $newsId = News::findOrFail($id);
        
        $filepath = public_path('storage/images/').$newsId->image;
        return Response::download($filepath);
        
    }

}
