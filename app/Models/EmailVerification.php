<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Generate a 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create or update verification code for email
     */
    public static function createOrUpdate(string $email): self
    {
        $code = self::generateCode();
        $expiresAt = now()->addMinutes(15); // Code expires in 15 minutes

        return self::updateOrCreate(
            ['email' => $email, 'verified' => false],
            [
                'code' => $code,
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Verify code
     */
    public function verify(string $code): bool
    {
        if ($this->verified) {
            return false;
        }

        if ($this->expires_at->isPast()) {
            return false;
        }

        if ($this->code !== $code) {
            return false;
        }

        $this->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
