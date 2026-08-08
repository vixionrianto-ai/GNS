<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditTrailService
{
    /*
    |--------------------------------------------------------------------------
    | Constant
    |--------------------------------------------------------------------------
    */

    public const MODULE_AUTH = 'Auth';

    public const MODULE_PELANGGAN = 'Pelanggan';

    public const MODULE_TAGIHAN = 'Tagihan';

    public const MODULE_PEMBAYARAN = 'Pembayaran';

    public const MODULE_ROUTER = 'Router';

    public const MODULE_PAKET = 'Paket';

    public const MODULE_USER = 'User';

    public const MODULE_ROLE = 'Role';

    public const MODULE_PERMISSION = 'Permission';

    public const MODULE_SETTING = 'Setting';

    public const MODULE_BACKUP = 'Backup';

    public const MODULE_SCHEDULER = 'Scheduler';

    public const MODULE_MIKROTIK = 'MikroTik';

    /*
    |--------------------------------------------------------------------------
    | Dependency
    |--------------------------------------------------------------------------
    */

    protected ?Request $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    protected function getIpAddress(): ?string
    {
        return $this->request?->ip();
    }

    protected function getUserAgent(): ?string
    {
        return $this->request?->userAgent();
    }

    protected function getUserId(): ?int
    {
        return Auth::id();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Log
    |--------------------------------------------------------------------------
    */
        /**
     * Simpan audit trail.
     */
    public function log(
        string $module,
        string $action,
        string $description,
        array $properties = [],
        ?int $userId = null
    ): AuditTrail {

        return AuditTrail::create([

            'user_id' => $userId ?? $this->getUserId(),

            'module' => $module,

            'action' => strtolower($action),

            'description' => $description,

            'ip_address' => $this->getIpAddress(),

            'user_agent' => $this->getUserAgent(),

            'properties' => empty($properties)
                ? null
                : $properties,

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Shortcut Method
    |--------------------------------------------------------------------------
    */

    /**
     * Log Create.
     */
    public function created(
        string $module,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            $module,
            'create',
            $description,
            $properties
        );

    }

    /**
     * Log Update.
     */
    public function updated(
        string $module,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            $module,
            'update',
            $description,
            $properties
        );

    }

    /**
     * Log Delete.
     */
    public function deleted(
        string $module,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            $module,
            'delete',
            $description,
            $properties
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
        /**
     * Log Login.
     */
    public function login(
        string $description = 'Login berhasil',
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_AUTH,
            'login',
            $description,
            $properties
        );

    }

    /**
     * Log Logout.
     */
    public function logout(
        string $description = 'Logout berhasil',
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_AUTH,
            'logout',
            $description,
            $properties
        );

    }

    /**
     * Log Login Gagal.
     *
     * Digunakan ketika autentikasi gagal.
     */
    public function failedLogin(
        string $username,
        array $properties = []
    ): AuditTrail {

        return $this->log(

            self::MODULE_AUTH,

            'failed_login',

            'Login gagal untuk user : ' . $username,

            array_merge([
                'username' => $username,
            ], $properties),

            null

        );

    }

    /*
    |--------------------------------------------------------------------------
    | MikroTik
    |--------------------------------------------------------------------------
    */
        /**
     * Log aktivitas MikroTik.
     */
    public function mikrotik(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_MIKROTIK,
            $action,
            $description,
            $properties
        );

    }

    /**
     * Log aktivitas Router.
     */
    public function router(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_ROUTER,
            $action,
            $description,
            $properties
        );

    }

    /**
     * Log aktivitas Pelanggan.
     */
    public function pelanggan(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_PELANGGAN,
            $action,
            $description,
            $properties
        );

    }

    /**
     * Log aktivitas Tagihan.
     */
    public function tagihan(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_TAGIHAN,
            $action,
            $description,
            $properties
        );

    }

    /**
     * Log aktivitas Pembayaran.
     */
    public function pembayaran(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_PEMBAYARAN,
            $action,
            $description,
            $properties
        );

    }

    /**
     * Log aktivitas Paket.
     */
    public function paket(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_PAKET,
            $action,
            $description,
            $properties
        );

    }
        /**
     * Log aktivitas User.
     */
    public function user(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_USER,
            $action,
            $description,
            $properties
        );
    }
    
    /**
     * Log aktivitas Role.
     */
    public function role(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_ROLE,
            $action,
            $description,
            $properties
        );

    }
    /**
     * Log aktivitas Permission.
     */
    public function permission(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_PERMISSION,
            $action,
            $description,
            $properties
        );

    }
    /**
     * Log aktivitas Setting.
     */
    public function setting(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_SETTING,
            $action,
            $description,
            $properties
        );

    }
    /**
     * Log aktivitas Backup.
     */
    public function backup(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_BACKUP,
            $action,
            $description,
            $properties
        );

    }
    /**
     * Log aktivitas Scheduler.
     */
    public function scheduler(
        string $action,
        string $description,
        array $properties = []
    ): AuditTrail {

        return $this->log(
            self::MODULE_SCHEDULER,
            $action,
            $description,
            $properties
        );

    }
    
    /**
     * Ambil aktivitas terbaru.
     */
    public function latest(int $limit = 10)
    {
        return AuditTrail::with('user')
            ->latest()
            ->take($limit)
            ->get();
    }
}