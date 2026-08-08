<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Pembayaran;
use App\Models\AuditTrail;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
        /*
        |--------------------------------------------------------------------------
        | RELATIONS
        |--------------------------------------------------------------------------
        */

        /**
         * Pembayaran yang diproses user.
         */
        public function pembayarans()
        {
            return $this->hasMany(
                Pembayaran::class
            );
        }

        /**
         * Audit Trail.
         */
        public function auditTrails()
        {
            return $this->hasMany(
                AuditTrail::class
            );
        }
    /**
     * AdminLTE User Image
     */
    public function adminlte_image()
    {
        return asset('images/user.png');
    }

    /**
     * AdminLTE User Description
     */
    public function adminlte_desc()
    {
        return 'Administrator GNS';
    }

    /**
     * AdminLTE User Profile URL
     */
    public function adminlte_profile_url()
    {
        return route('profile.edit');
    }

        /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isKasir(): bool
    {
        return $this->hasRole('Kasir');
    }

    public function isTeknisi(): bool
    {
        return $this->hasRole('Teknisi');
    }

    public function isViewer(): bool
    {
        return $this->hasRole('Viewer');
    }
}
