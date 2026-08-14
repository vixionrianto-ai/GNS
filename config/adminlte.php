<?php

return [

    'title' => 'GNS Billing',
    'title_prefix' => '',
    'title_postfix' => ' | GNS Network',

    'use_ico_only' => false,
    'usermenu_image' => true,
    'use_full_favicon' => false,

    'google_fonts' => [
        'allowed' => true,
    ],

    'logo' => '<b>GNS</b> Network
    <br><small style="font-size:11px;font-weight:400;">
    Billing Management System
    </small>',
    'logo_img' => 'images/logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_alt' => 'GNS Network',

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'images/logo.png',
            'alt' => 'GNS Network',
            'effect' => 'animation__pulse',
            'width' => 90,
            'height' => 90,
        ],
    ],

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => 'Administrator GNS',

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => false,

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-dark navbar-primary',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    'use_route_url' => true,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    'menu' => [

        ['header' => 'MASTER DATA'],

        [
            'text' => 'Router',
            'route' => 'router.index',
            'icon' => 'fas fa-network-wired',
        ],
        [
            'text' => 'Paket Internet',
            'route' => 'paket.index',
            'icon' => 'fas fa-wifi',
        ],
        [
            'text' => 'Pelanggan',
            'route' => 'pelanggan.index',
            'icon' => 'fas fa-users',
        ],

        ['header' => 'TRANSAKSI'],

        [
            'text' => 'Tagihan',
            'route' => 'tagihan.index',
            'icon' => 'fas fa-file-invoice',
        ],
        [
            'text' => 'Pembayaran',
            'route' => 'pembayaran.index',
            'icon' => 'fas fa-money-check-alt',
        ],
        [
            'text' => 'Riwayat WhatsApp',
            'route' => 'whatsapp.index',
            'icon' => 'fab fa-whatsapp',
        ],

        ['header' => 'LAPORAN'],

        [
            'text' => 'Dashboard Analitik',
            'route' => 'dashboard',
            'icon' => 'fas fa-chart-line',
        ],
        [
            'text' => 'Laporan',
            'route' => 'laporan.index',
            'icon' => 'fas fa-chart-bar',
        ],

        ['header' => 'MIKROTIK'],

        [
            'text' => 'Monitoring MikroTik',
            'route' => 'mikrotik.monitor',
            'icon' => 'fas fa-network-wired',
        ],

        ['header' => 'SYSTEM'],

        [
            'text' => 'User Management',
            'route' => 'users.index',
            'icon' => 'fas fa-users-cog',
            'can' => 'user.view',
        ],

        [
            'text' => 'Role Management',
            'route' => 'roles.index',
            'icon' => 'fas fa-user-shield',
        ],

        [
            'text' => 'Audit Trail',
            'route' => 'audit.index',
            'icon' => 'fas fa-history',
        ],
        [
            'text' => 'Pengaturan',
            'route' => 'settings.index',
            'icon' => 'fas fa-cogs',
        ],
        [
            'text' => 'Profile',
            'route' => 'profile.edit',
            'icon' => 'fas fa-user',
        ],

        ['header' => 'SUPER ADMIN'],

        [
            'text' => 'Backup Database',
            'url' => '#',
            'icon' => 'fas fa-database',
        ],
        [
            'text' => 'Restore Database',
            'url' => '#',
            'icon' => 'fas fa-upload',
        ],
        [
            'text' => 'Reset Data',
            'route' => 'superadmin.index',
            'icon' => 'fas fa-trash-alt',
        ],
        [
            'text' => 'Factory Reset',
            'url' => '#',
            'icon' => 'fas fa-radiation',
        ],
        [
            'text' => 'Informasi Sistem',
            'url' => '#',
            'icon' => 'fas fa-info-circle',
        ],

        ['header' => 'ACCOUNT'],

        [
            'text' => 'Logout',
            'url' => 'logout',
            'method' => 'post',
            'icon' => 'fas fa-sign-out-alt',
        ],
    ],

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    'livewire' => false,
];