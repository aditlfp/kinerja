<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What the employee did with one management reply: when they read it, and —
 * when they answered the checklist — which answers they picked and which message
 * carried them back to management.
 *
 * One row per management message, so the row itself is the answer to "sudah
 * dipastikan dibaca?" for that message.
 */
class CheckPointMessageAcknowledgement extends Model
{
    protected $fillable = [
        'check_point_message_id',
        'confirmation_message_id',
        'user_id',
        'read_at',
        'confirmed_at',
        'choices',
        'note',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'choices' => 'array',
    ];

    /** The management message this acknowledges. */
    public function message(): BelongsTo
    {
        return $this->belongsTo(CheckPointMessage::class, 'check_point_message_id');
    }

    /** The employee reply that carried the checklist back. */
    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(CheckPointMessage::class, 'confirmation_message_id');
    }

    /** Users are soft-deleted, so the reader stays resolvable afterwards. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    /**
     * Answers as stored (`paham`, `siap`, `lainnya`), never null, so the view can
     * iterate without a guard.
     *
     * @return array<int, string>
     */
    public function answers(): array
    {
        return array_values(array_filter((array) $this->choices));
    }
}
