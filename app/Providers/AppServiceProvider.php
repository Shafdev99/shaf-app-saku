<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Walkel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /// Get the AliasLoader instance
        $loader = AliasLoader::getInstance();

        // Add your aliases
        $loader->alias('Excel', Excel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (env('APP_ENV') !== 'local') {
        // }
        // URL::forceScheme('https');

        // Paginasi menggunakan style bootstrap
        Paginator::useBootstrapFive();

        // Gate untuk admin
        Gate::define('admin', function (User $user) {
            return  $user->role == 'admin';
        });

        // Gate untuk staff
        Gate::define('staff_dan_admin', function (User $user) {
            return  $user->role == 'staff' || $user->role == 'admin';
        });

        Gate::define('kepala-sekolah', fn (User $user) => $user->role === 'kepala-sekolah');

        Gate::define('siswa.read', fn (User $user) => in_array($user->role, ['admin', 'staff', 'guru', 'kepala-sekolah'], true));
        Gate::define('siswa.write', fn (User $user) => in_array($user->role, ['admin', 'staff'], true));

        Gate::define('absensi.read', fn (User $user) => in_array($user->role, ['admin', 'staff', 'guru', 'kepala-sekolah'], true));
        Gate::define('absensi.scan', fn (User $user) => in_array($user->role, ['admin', 'staff', 'guru'], true));

        Gate::define('rekap.read', fn (User $user) => in_array($user->role, ['admin', 'staff', 'guru', 'kepala-sekolah'], true));
        Gate::define('rekap.write', fn (User $user) => in_array($user->role, ['admin', 'staff', 'guru'], true));

        // Gate untuk wali_kelas
        Gate::define('wali_kelas', function (User $user) {
            //    $iniWalkel = 
            return Walkel::cekWalkel()
                ->where('users.id', $user->id)
                ->first()?->guru_id;;
        });
    }
}
