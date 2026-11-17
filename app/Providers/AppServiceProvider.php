<?php

namespace App\Providers;

use App\Models\Peminjaman;
use App\Policies\PeminjamanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        Gate::policy(Peminjaman::class, PeminjamanPolicy::class);

        Gate::define('kelola-buku', function ($user) {
            return $user->hasAnyRole(['admin', 'petugas']);
        });
    }
}
