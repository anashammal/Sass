<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// !! -- قمنا باستيراد الإشعار الجديد هنا -- !!
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * تعريف العلاقة: المستخدم (إذا كان 'store_owner') "يمتلك" متجرًا واحدًا.
     */
    public function store()
    {
        return $this->hasOne(Store::class, 'owner_id');
    }

    /**
     * !! -- هذا هو الكود الجديد الذي يتجاوز السلوك الافتراضي -- !!
     * * إرسال إشعار "نسيت كلمة المرور" المخصص.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

}
