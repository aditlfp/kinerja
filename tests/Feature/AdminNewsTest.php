<?php

namespace Tests\Feature;

use App\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Menguji alur admin berita (routes/web.php → NewsController) tanpa database MySQL:
 * tabel `news` dibuat sendiri di sqlite in-memory supaya perilaku controller
 * (simpan tanpa ganti gambar, hapus, route) bisa diverifikasi langsung.
 */
class AdminNewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Halaman admin dilindungi middleware auth; izin & berita diuji terpisah.
        $this->withoutMiddleware();

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->date('tanggal_lihat');
            $table->date('tanggal_tutup');
            $table->json('tanggal_muncul')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $override
     */
    private function buatBerita(array $override = []): int
    {
        return DB::table('news')->insertGetId(array_merge([
            'image' => 'data111.jpg',
            'tanggal_lihat' => '2026-09-01',
            'tanggal_tutup' => '2026-09-30',
            'tanggal_muncul' => json_encode(['21', '22']),
            'created_at' => now(),
            'updated_at' => now(),
        ], $override));
    }

    public function test_edit_tanpa_ganti_gambar_tetap_menyimpan_perubahan(): void
    {
        $id = $this->buatBerita();

        // Persis seperti yang dikirim form edit: hanya tanggal + tanggal_muncul.
        $response = $this->patch('/news/'.$id, [
            'tanggal_lihat' => '2026-09-05',
            'tanggal_tutup' => '2026-10-05',
            'tanggal_muncul' => ['2', '9'],
        ]);

        $response->assertRedirect();

        $row = DB::table('news')->where('id', $id)->first();
        $this->assertSame('2026-09-05', (string) $row->tanggal_lihat);
        $this->assertSame('2026-10-05', (string) $row->tanggal_tutup);
        $this->assertSame('data111.jpg', (string) $row->image);
    }

    public function test_hapus_berita_yang_tidak_ada_tidak_menghasilkan_error_500(): void
    {
        $response = $this->delete('/news/99999');

        $response->assertStatus(302);
        $this->assertDatabaseCount('news', 0);
    }

    public function test_hapus_berita_menghapus_barisnya(): void
    {
        $id = $this->buatBerita();

        $response = $this->delete('/news/'.$id);

        $response->assertStatus(302);
        $this->assertDatabaseCount('news', 0);
    }

    public function test_route_show_berita_tidak_menghasilkan_error_500(): void
    {
        $id = $this->buatBerita();

        // Tidak ada view/method show untuk admin berita, jadi route ini harus tidak ada.
        $this->assertNotSame(500, $this->get('/news/'.$id)->getStatusCode());
    }

    public function test_berita_tampil_menghitung_tanggal_muncul_bentuk_string_dan_angka(): void
    {
        // Checkbox mengirim string, jadi data lama tersimpan sebagai ["21"].
        $sebagaiString = $this->buatBerita(['tanggal_muncul' => json_encode(['21'])]);
        // Sebagian data bisa tersimpan sebagai angka: [22].
        $sebagaiAngka = $this->buatBerita(['tanggal_muncul' => json_encode([22])]);
        // Di luar rentang tayang.
        $this->buatBerita([
            'tanggal_lihat' => '2026-10-01',
            'tanggal_tutup' => '2026-10-31',
            'tanggal_muncul' => json_encode(['21']),
        ]);

        $this->assertSame(
            [$sebagaiString],
            News::query()->tampilPada(Carbon::parse('2026-09-21'))->pluck('id')->all()
        );

        $this->assertSame(
            [$sebagaiAngka],
            News::query()->tampilPada(Carbon::parse('2026-09-22'))->pluck('id')->all()
        );
    }
}
