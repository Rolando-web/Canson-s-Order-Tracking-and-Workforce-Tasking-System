<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helper: create notification(s) ──────────

    /**
     * Send a notification to a single user.
     */
    public static function send(int $userId, string $type, string $title, string $message, array $data = []): self
    {
        return self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data ?: null,
        ]);
    }

    /**
     * Send a notification to multiple users.
     */
    public static function sendToMany(array $userIds, string $type, string $title, string $message, array $data = []): void
    {
        foreach ($userIds as $uid) {
            self::send($uid, $type, $title, $message, $data);
        }
    }
}
