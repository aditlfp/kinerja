<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Services\CheckPointImageUploader;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The create/edit form sends every job row and its photos in one request, and
 * the photos are decoded with GD before being downscaled. A single 100+ MP
 * phone photo needs ~430 MB for its bitmap, which exhausts a 512 MB
 * `memory_limit` — a *fatal* error the surrounding try/catch cannot catch, so
 * the whole form used to die with "Allowed memory size exhausted".
 *
 * These tests pin the replacement behaviour: ordinary photos are still
 * downscaled, photos that cannot be decoded safely are stored untouched, and
 * the multi-row form saves with each row's photo.
 */
class CheckPointImageUploaderTest extends TestCase
{
    use DatabaseTransactions;

    /** Temp sources and stored files to remove after each test. */
    private array $paths = [];

    private array $createdPaths = [];

    private ?string $memoryLimit = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memoryLimit = (string) ini_get('memory_limit');
    }

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        foreach ($this->createdPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        if ($this->memoryLimit !== null) {
            ini_set('memory_limit', $this->memoryLimit);
        }

        parent::tearDown();
    }

    private function uploader(): CheckPointImageUploader
    {
        return app(CheckPointImageUploader::class);
    }

    /** A real JPEG on disk, wrapped as an upload. */
    private function jpeg(int $width, int $height, string $name): UploadedFile
    {
        $path = sys_get_temp_dir() . '/' . uniqid('cp-upload-', true) . '.jpg';

        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 40, 90, 160));
        imagefilledrectangle(
            $image,
            0,
            0,
            (int) ($width / 3),
            (int) ($height / 3),
            imagecolorallocate($image, 255, 255, 255),
        );
        imagejpeg($image, $path, 80);
        imagedestroy($image);

        $this->paths[] = $path;

        // Test mode: skip the "was it really uploaded" check.
        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    private function storedPath(string $name): string
    {
        $path = storage_path('app/public/images/' . $name);
        $this->createdPaths[] = $path;

        return $path;
    }

    private function user(): User
    {
        $jabatan = Jabatan::query()->value('id') ?? 1;
        $divisi = Divisi::query()->value('id') ?? 1;

        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => $divisi,
            'jabatan_id' => $jabatan,
            'name' => 'img-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Upload',
            'image' => 'default.png',
            'email' => 'img-test-' . uniqid() . '@example.com',
            'password' => bcrypt('secret'),
            'status_id' => null,
        ]);
    }

    public function test_an_ordinary_photo_is_still_downscaled(): void
    {
        $name = $this->uploader()->store($this->jpeg(900, 600, 'biasa.jpg'));

        [$width, $height] = getimagesize($this->storedPath($name));

        $this->assertSame(450, $width);
        $this->assertSame(300, $height);
    }

    public function test_a_photo_too_big_for_the_memory_left_is_stored_untouched(): void
    {
        $file = $this->jpeg(900, 600, 'besar.jpg');
        $original = file_get_contents($file->getRealPath());

        // Squeeze the request down to what it has now plus a little more: not
        // enough for the decode budget, but enough for the test itself to run.
        ini_set('memory_limit', (string) (int) ceil((memory_get_usage() + 40 * 1024 * 1024) / 1048576) . 'M');

        $name = $this->uploader()->store($file);
        $stored = $this->storedPath($name);

        // Untouched means byte for byte: no decode, no re-encode, and — most
        // importantly — no fatal error.
        $this->assertSame($original, Storage::disk('public')->get('images/' . $name));
        $this->assertSame([900, 600], array_slice(getimagesize($stored), 0, 2));
    }

    public function test_an_upload_gd_cannot_read_is_stored_untouched(): void
    {
        $path = sys_get_temp_dir() . '/' . uniqid('cp-notimage-', true) . '.jpg';
        file_put_contents($path, 'ini bukan gambar');
        $this->paths[] = $path;

        $file = new UploadedFile($path, 'bukan-gambar.jpg', 'image/jpeg', null, true);

        $name = $this->uploader()->store($file);

        $this->assertSame('ini bukan gambar', Storage::disk('public')->get('images/' . $name));
        $this->storedPath($name);
    }

    public function test_the_decode_budget_rejects_huge_photos_and_allows_ordinary_ones(): void
    {
        $uploader = $this->uploader();

        // 144 MP is ~576 MB of bitmap — the reported crash.
        $this->assertFalse($uploader->canDecodePixels(12_000 * 12_000));

        // 12 MP (4000x3000) is fine, and so is a 24 MP phone photo.
        $this->assertTrue($uploader->canDecodePixels(4_000 * 3_000));
        $this->assertTrue($uploader->canDecodePixels(6_000 * 4_000));

        $this->assertFalse($uploader->canDecodePixels(0));
    }

    public function test_the_create_form_saves_several_job_rows_with_their_photos(): void
    {
        $user = $this->user();
        $today = now()->toDateString();

        $this->actingAs($user)
            ->post('/checkpoint-user', [
                'user_id' => $user->id,
                'divisi_id' => $user->devisi_id,
                'original_index' => ['0' => '0', '1' => '1'],
                'pekerjaan_id' => ['0' => 'manual', '1' => 'manual'],
                'input_manual' => ['0' => 'Pekerjaan A', '1' => 'Pekerjaan B'],
                'deskripsi' => ['0' => 'Bukti A', '1' => 'Bukti B'],
                'tanggal' => ['0' => $today, '1' => $today],
                'approve_status' => ['0' => 'proccess', '1' => 'proccess'],
                'img' => [
                    '0' => [$this->jpeg(900, 600, 'a.jpg')],
                    '1' => [$this->jpeg(1200, 900, 'b.jpg')],
                ],
            ])
            ->assertRedirect(route('checkpoint-user.index'));

        $checkpoint = CheckPoint::where('user_id', $user->id)->latest('id')->first();

        $this->assertNotNull($checkpoint);
        $this->assertSame(2, $checkpoint->items()->count());

        foreach ($checkpoint->items()->with('images')->get() as $item) {
            $this->assertCount(1, $item->images);

            foreach ($item->images as $image) {
                [$width, $height] = getimagesize($this->storedPath($image->path));

                $this->assertLessThanOrEqual(450, max($width, $height));
            }
        }
    }
}
