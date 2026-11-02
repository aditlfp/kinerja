<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;
use Creagia\LaravelSignPad\Templates\PdfDocumentTemplate;
use Creagia\LaravelSignPad\SignatureDocumentTemplate;
use Creagia\LaravelSignPad\SignaturePosition;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements CanBeSigned
{
    use HasApiTokens, HasFactory, Notifiable, RequiresSignature, SoftDeletes;
    protected $connection = 'mysql';
    protected $table = 'users';

    /**
     * Akun non-personel yang tidak ikut dihitung pada statistik mitra.
     */
    public const PLACEHOLDER_ACCOUNT_NAMES = ['admin', 'user', 'MITRA SAC'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nama_lengkap',
        'kerjasama_id',
        'email',
        'password',
        'image',
        'devisi_id',
        'jabatan_id',
        'status_id',
        'temp_ban',
        'nik',
        'no_hp',
        'alamat'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getSignatureDocumentTemplate(): SignatureDocumentTemplate
    {
        return new SignatureDocumentTemplate(
            outputPdfPrefix: 'document', // optional
            // template: new BladeDocumentTemplate('pdf/my-pdf-blade-template'), // Uncomment for Blade template
            template: new PdfDocumentTemplate(storage_path('pdf/template.pdf')), // Uncomment for PDF template
            signaturePositions: [
                new SignaturePosition(
                    signaturePage: 1,
                    signatureX: 20,
                    signatureY: 25,
                ),
                new SignaturePosition(
                    signaturePage: 2,
                    signatureX: 25,
                    signatureY: 50,
                ),
            ]
        );
    }

    public function hasReceivedNotificationToday()
    {
        return $this->last_notification_date === Carbon::today()->toDateString();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function kerjasama()
    {
        return $this->belongsTo(Kerjasama::class, 'kerjasama_id', 'id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'devisi_id', 'id');
    }
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }
    public function jadwalUser(): HasMany
    {
        return $this->hasMany(JadwalUser::class);
    }

    public function checkPoint()
    {
        return $this->hasMany(User::class);
    }
    public function rating()
    {
        return $this->hasMany(Rating::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function slipGaji()
    {
        return $this->setConnection("mysql2")->hasMany(SlipGaji::class);
    }
}
