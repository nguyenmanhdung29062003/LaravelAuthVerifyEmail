<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordReset extends Model
{
    use HasFactory;
    // Khai báo tên bảng
    protected $table = 'password_resets';

    // Xác định khóa chính là 'email' thay vì 'id'
    protected $primaryKey = 'email';

    // Không sử dụng timestamps mặc định của Laravel
    public $timestamps = false;

    // Chỉ có created_at, không có updated_at
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the password reset.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Create a new password reset token
     *
     * @param string $email
     * @return string
     */
    public static function createToken($email)
    {
        $token = Str::random(60);

        self::updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        return $token;
    }

    /**
     * Find valid token
     *
     * @param string $token
     * @param string $email
     * @return bool
     */
    public static function findValidToken($token, $email)
    {
        $reset = self::where('email', $email)
            ->where('created_at', '>', now()->subHours(config('auth.passwords.users.expire', 60)))
            ->first();

        if ($reset && Hash::check($token, $reset->token)) {
            return $reset;
        }

        return null;
    }

    /**
     * Invalidate token
     *
     * @param string $email
     * @return void
     */
    public static function invalidateToken($email)
    {
        self::where('email', $email)->delete();
    }
}
