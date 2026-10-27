<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckPointItem extends Model
{
    /**
     * Quick approval reactions offered to a reviewer in the verdict modal.
     * The key is what the form posts; the emoji + label are what the stored
     * note and thread message read. The chip draws `icon`, a solid Remix Icon
     * from the set the whole app already ships — a plain glyph instead of a
     * 3D emoji picture, so the row stays crisp at any size and needs no extra
     * asset. `tone` is the glyph colour. The list lives here once instead of
     * being repeated in Blade and the controller.
     */
    public const APPROVAL_REACTIONS = [

        'cukup' => [
            'emoji' => 'ℹ️',
            'label' => 'Cukup',
            'icon' => 'ri-information-2-fill',
            'tone' => 'text-slate-500',
        ],
        'baik' => [
            'emoji' => '👍',
            'label' => 'Baik',
            'icon' => 'ri-thumb-up-fill',
            'tone' => 'text-sky-500',
        ],
        'excellent' => [
            'emoji' => '⭐',
            'label' => 'Excellent',
            'icon' => 'ri-star-fill',
            'tone' => 'text-amber-500',
        ],
    ];

    /**
     * The quick reaction a stored note opens with, if any. The verdict modal
     * writes "👍 Baik" (or "👍 Baik — komentar"), and both the thread bubble
     * and the "Catatan Evaluasi" banner should draw the same solid icon the
     * chip shows instead of the device's own emoji glyph.
     *
     * @return array{icon: string, tone: string, label: string, rest: string}|null
     */
    public static function reactionFromText(?string $text): ?array
    {
        $text = trim((string) $text);

        foreach (self::APPROVAL_REACTIONS as $reaction) {
            $lead = $reaction['emoji'] . ' ' . $reaction['label'];

            if ($text !== $lead && ! str_starts_with($text, $lead . ' — ')) {
                continue;
            }

            return [
                'icon' => $reaction['icon'],
                'tone' => $reaction['tone'],
                'label' => $reaction['label'],
                'rest' => trim((string) substr($text, strlen($lead . ' — '))),
            ];
        }

        return null;
    }

    protected $fillable = [
        'check_point_id',
        'pekerjaan_cp_id',
        'input_manual',
        'deskripsi',
        'tanggal',
        'latitude',
        'longtitude',
        'approve_status',
        'note',
        'urutan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'urutan' => 'integer',
    ];

    public function checkPoint(): BelongsTo
    {
        return $this->belongsTo(CheckPoint::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CheckPointImage::class)->orderBy('urutan');
    }

    /**
     * Two-way conversation: management review notes and the employee's replies.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(CheckPointMessage::class)->orderBy('id');
    }

    public function pekerjaanCp(): BelongsTo
    {
        return $this->belongsTo(PekerjaanCp::class, 'pekerjaan_cp_id');
    }

    /** First image path, handy for single-thumbnail listings. */
    public function getFirstImageAttribute(): ?string
    {
        return $this->images->first()->path ?? null;
    }

    public function isApproved(): bool
    {
        return $this->approve_status === 'accept';
    }

    public function isDenied(): bool
    {
        return $this->approve_status === 'denied';
    }

    /**
     * Direksi/Manager CS wrote an evaluation note on this row. It may still be
     * corrected after the weekly reporting window has closed.
     */
    public function hasEvaluationNote(): bool
    {
        return filled($this->note);
    }
}
