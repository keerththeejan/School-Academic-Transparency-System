<?php

namespace App\Providers;

use App\Events\DiscrepancyOpened;
use App\Events\SummaryPublished;
use App\Listeners\NotifyParentsOfSummary;
use App\Listeners\NotifySchoolReviewers;
use App\Models\User;
use App\Support\Navigation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->useLangPath(resource_path('lang'));
    }

    public function boot(): void
    {
        $this->hidePublicDirectory();
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Event::listen(SummaryPublished::class, NotifyParentsOfSummary::class);
        Event::listen(DiscrepancyOpened::class, NotifySchoolReviewers::class);

        View::composer(['layouts.app', 'layouts.portal'], function ($view) {
            $user = auth()->user();
            if (! $user instanceof User) {
                return;
            }

            $user->loadMissing('roles.permissions', 'parentProfile', 'school');
            $view->with('navItems', Navigation::items($user));
            $view->with('unreadCount', $user->portalNotifications()->whereNull('read_at')->count());
        });
    }

    /**
     * WAMP serves this project at /tmv and rewrites into public/.
     * Drop that internal folder from generated links and asset URLs.
     */
    private function hidePublicDirectory(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();
        $script = str_replace('\\', '/', (string) $request->server->get('SCRIPT_NAME', ''));
        if (! str_contains($script, '/public/index.php')) {
            return;
        }

        $front = str_replace('/public/index.php', '/index.php', $script);
        $request->server->set('SCRIPT_NAME', $front);
        $request->server->set('PHP_SELF', $front);
    }
}
