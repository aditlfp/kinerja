<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\CheckPointMessageAcknowledgement;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The direksi note history answers one question: what did I tell my staff in
 * the last two weeks? It reads the conversation thread, so it must not leak
 * another reviewer's notes, nor notes that were retracted or older than the
 * chosen window.
 */
class DireksiCatatanBawahanTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 1900;

    protected function setUp(): void
    {
        parent::setUp();

        // The suite shares one database, so a fixed id range eventually lands
        // on rows another test already left behind. Start past everything
        // that exists instead of guessing a safe constant.
        $this->seq = max(
            (int) DB::table('divisis')->max('id'),
            (int) DB::table('jabatans')->max('id'),
        ) + 1000;
    }

    private function divisiWithJabatan(string $code, string $name): array
    {
        $divisiId = ++$this->seq;
        $jabatanId = ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'CATATAN ' . $divisiId,
            'jabatan_id' => $jabatanId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('jabatans')->insert([
            'id' => $jabatanId,
            'divisi_id' => $divisiId,
            'code_jabatan' => $code,
            'type_jabatan' => 'staff',
            'name_jabatan' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [Divisi::find($divisiId), Jabatan::find($jabatanId)];
    }

    private function makeUser(Jabatan $jabatan, Divisi $divisi, string $nama): User
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $nama)) . $this->seq . rand(100, 999);

        return User::create([
            'name' => $slug,
            'nama_lengkap' => $nama,
            'email' => $slug . '@test.local',
            'password' => bcrypt('secret'),
            'image' => 'no-image.jpg',
            'jabatan_id' => $jabatan->id,
            'devisi_id' => $divisi->id,
            'kerjasama_id' => 1,
        ]);
    }

    private function direksi(string $nama = 'Catatan Direksi Tester'): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('DIREKSI', 'Direktur');

        return $this->makeUser($jabatan, $divisi, $nama);
    }

    private function employee(string $nama): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service');

        return $this->makeUser($jabatan, $divisi, $nama);
    }

    /**
     * One job row plus one management note on it, dated $daysAgo.
     */
    private function noteFrom(User $author, User $employee, string $body, int $daysAgo = 0, string $status = 'accept', array $message = []): CheckPointMessage
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => 'dikerjakan',
            'tanggal' => now()->subDays($daysAgo)->toDateString(),
        ]);

        $item = CheckPointItem::create([
            'check_point_id' => $checkpoint->id,
            'input_manual' => 'Pekerjaan catatan',
            'tanggal' => now()->subDays($daysAgo)->toDateString(),
            'approve_status' => $status,
            'urutan' => 0,
        ]);

        $note = CheckPointMessage::create($message + [
            'check_point_item_id' => $item->id,
            'user_id' => $author->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => $body,
        ]);

        DB::table('check_point_messages')->where('id', $note->id)->update([
            'created_at' => now()->subDays($daysAgo),
        ]);

        return $note->fresh();
    }

    private function page(User $direksi, string $query = '')
    {
        return $this->actingAs($direksi)->get('/direksi-checkpoint-notes' . ($query ? '?' . $query : ''));
    }

    public function test_only_the_signed_in_direksi_notes_are_listed(): void
    {
        $me = $this->direksi('Direksi Pemilik Catatan');
        $other = $this->direksi('Direksi Lain');
        $employee = $this->employee('Karyawan Satu');

        $this->noteFrom($me, $employee, 'Catatan milik saya sendiri');
        $this->noteFrom($other, $employee, 'Catatan direksi lain');

        $this->page($me)
            ->assertOk()
            ->assertSee('Catatan milik saya sendiri')
            ->assertDontSee('Catatan direksi lain');
    }

    public function test_notes_older_than_two_weeks_are_hidden_by_default(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Dua');

        $this->noteFrom($me, $employee, 'Catatan hari ini', 0);
        $this->noteFrom($me, $employee, 'Catatan tiga minggu lalu', 21);

        $this->page($me)
            ->assertOk()
            ->assertSee('Catatan hari ini')
            ->assertDontSee('Catatan tiga minggu lalu')
            ->assertSee('data-note-total>1<', false);
    }

    public function test_the_period_filter_widens_the_window(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Tiga');

        $this->noteFrom($me, $employee, 'Catatan dua puluh hari lalu', 20);

        $this->page($me, 'periode=14')
            ->assertOk()
            ->assertDontSee('Catatan dua puluh hari lalu');

        $this->page($me, 'periode=90')
            ->assertOk()
            ->assertSee('Catatan dua puluh hari lalu');
    }

    public function test_categories_follow_the_job_status(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Empat');

        $this->noteFrom($me, $employee, 'Apresiasi pekerjaan rapi', 1, 'accept');
        $this->noteFrom($me, $employee, 'Tolong rapikan dokumentasi', 1, 'proccess');
        $this->noteFrom($me, $employee, 'Laporan tidak sesuai', 1, 'denied');

        $this->page($me)->assertOk()->assertSee('data-note-category="apresiasi"', false);

        $this->page($me, 'jenis=apresiasi')
            ->assertOk()
            ->assertSee('Apresiasi pekerjaan rapi')
            ->assertDontSee('Tolong rapikan dokumentasi')
            ->assertDontSee('Laporan tidak sesuai');

        $this->page($me, 'jenis=perbaikan')
            ->assertOk()
            ->assertSee('Tolong rapikan dokumentasi')
            ->assertDontSee('Apresiasi pekerjaan rapi')
            ->assertDontSee('Laporan tidak sesuai');

        $this->page($me, 'jenis=teguran')
            ->assertOk()
            ->assertSee('Laporan tidak sesuai')
            ->assertDontSee('Apresiasi pekerjaan rapi')
            ->assertDontSee('Tolong rapikan dokumentasi');
    }

    public function test_search_matches_employee_name_and_note_body(): void
    {
        $me = $this->direksi();
        $ayu = $this->employee('Karyawan Ayu Lestari');
        $budi = $this->employee('Karyawan Budi Santoso');

        $this->noteFrom($me, $ayu, 'Perbaiki bagian depan', 1);
        $this->noteFrom($me, $budi, 'Pekerjaan sudah rapi', 1);

        $this->page($me, 'search=' . urlencode('Ayu'))
            ->assertOk()
            ->assertSee('Perbaiki bagian depan')
            ->assertDontSee('Pekerjaan sudah rapi');

        $this->page($me, 'search=' . urlencode('rapi'))
            ->assertOk()
            ->assertSee('Pekerjaan sudah rapi')
            ->assertDontSee('Perbaiki bagian depan');
    }

    public function test_retracted_notes_are_not_listed(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Lima');

        $this->noteFrom($me, $employee, 'Catatan yang ditarik', 1, 'accept', ['retracted_at' => now()]);
        $this->noteFrom($me, $employee, 'Catatan yang masih ada', 1);

        $this->page($me)
            ->assertOk()
            ->assertSee('Catatan yang masih ada')
            ->assertDontSee('Catatan yang ditarik');
    }

    public function test_total_counts_the_period_before_filters(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Enam');

        $this->noteFrom($me, $employee, 'Satu', 1, 'accept');
        $this->noteFrom($me, $employee, 'Dua', 1, 'accept');
        $this->noteFrom($me, $employee, 'Tiga', 1, 'denied');
        $this->noteFrom($me, $employee, 'Lama', 40, 'accept');

        $this->page($me, 'jenis=teguran')
            ->assertOk()
            ->assertSee('Tiga')
            ->assertDontSee('Satu')
            ->assertSee('data-note-total>3<', false);
    }

    public function test_confirmation_summary_counts_confirmed_and_pending_notes(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Delapan');

        $confirmed = $this->noteFrom($me, $employee, 'Catatan yang sudah dikonfirmasi', 1, 'proccess', ['requires_confirmation' => true]);
        $readOnly = $this->noteFrom($me, $employee, 'Catatan yang baru dibaca', 1, 'proccess', ['requires_confirmation' => true]);
        $this->noteFrom($me, $employee, '👍 Baik', 1, 'accept');
        $this->noteFrom($me, $employee, 'Catatan yang ditarik', 1, 'proccess', [
            'requires_confirmation' => true,
            'retracted_at' => now(),
        ]);

        CheckPointMessageAcknowledgement::create([
            'check_point_message_id' => $confirmed->id,
            'user_id' => $employee->id,
            'read_at' => now(),
            'confirmed_at' => now(),
        ]);

        // Merely opening the thread stamps `read_at`. That is not a confirmation.
        CheckPointMessageAcknowledgement::create([
            'check_point_message_id' => $readOnly->id,
            'user_id' => $employee->id,
            'read_at' => now(),
        ]);

        $response = $this->page($me)->assertOk();

        $response->assertSee('data-note-confirmed>1<', false);
        $response->assertSee('data-note-pending>1<', false);
        $response->assertSee('1 catatan hanya berisi reaksi singkat', false);

        // The summary describes the window, so filtering the list leaves it be.
        $this->page($me, 'jenis=teguran')
            ->assertOk()
            ->assertSee('data-note-confirmed>1<', false)
            ->assertSee('data-note-pending>1<', false);
    }

    public function test_confirmation_badge_and_filter_follow_the_employee_answer(): void
    {
        $me = $this->direksi();
        $employee = $this->employee('Karyawan Sembilan');

        $confirmed = $this->noteFrom($me, $employee, 'Catatan yang sudah dikonfirmasi', 1, 'proccess', ['requires_confirmation' => true]);
        $this->noteFrom($me, $employee, 'Catatan yang belum dikonfirmasi', 1, 'proccess', ['requires_confirmation' => true]);
        $this->noteFrom($me, $employee, '👍 Baik', 1, 'accept');

        CheckPointMessageAcknowledgement::create([
            'check_point_message_id' => $confirmed->id,
            'user_id' => $employee->id,
            'read_at' => now(),
            'confirmed_at' => now(),
        ]);

        $all = $this->page($me)->assertOk();

        // Every card carries its own state, including the reaction-only note
        // that has nothing to confirm.
        $all->assertSee('data-note-confirmation="sudah"', false);
        $all->assertSee('data-note-confirmation="belum"', false);
        $all->assertSee('data-note-confirmation="tanpa"', false);
        $all->assertSee('Dikonfirmasi');
        $all->assertSee('Menunggu konfirmasi');

        $this->page($me, 'konfirmasi=belum')
            ->assertOk()
            ->assertSee('Catatan yang belum dikonfirmasi')
            ->assertDontSee('Catatan yang sudah dikonfirmasi')
            ->assertDontSee('👍 Baik');

        $this->page($me, 'konfirmasi=sudah')
            ->assertOk()
            ->assertSee('Catatan yang sudah dikonfirmasi')
            ->assertDontSee('Catatan yang belum dikonfirmasi')
            ->assertDontSee('👍 Baik');
    }

    public function test_only_direksi_can_open_the_page(): void
    {
        $employee = $this->employee('Karyawan Tujuh');

        $this->actingAs($employee)
            ->get('/direksi-checkpoint-notes')
            ->assertRedirect(route('login'));
    }
}
