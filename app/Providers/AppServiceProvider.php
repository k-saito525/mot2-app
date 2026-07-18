<?php

namespace App\Providers;

use App\Http\View\Composers\HeaderComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->id;
            if (empty($key)) {
                $key = $request->ip();
            }
            return Limit::perMinute(60)->by($key);
        });

        // ログイン試行のブルートフォース対策(メールアドレス+IP単位で15分間に5回まで)
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            return Limit::perMinutes(15, 5)->by($email . '|' . $request->ip());
        });

        View::composer('components.l-header', HeaderComposer::class);
    }
}
