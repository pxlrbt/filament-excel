<?php

namespace pxlrbt\FilamentExcel\Jobs\Middleware;

use Closure;
use Illuminate\Support\Facades\Context;

class SetAuthenticatedUser
{
    public function handle(object $job, Closure $next): void
    {
        $userId = Context::get('filament_excel_user_id');
        $guard = Context::get('filament_excel_auth_guard');

        if ($userId !== null && $guard !== null) {
            $guardInstance = auth()->guard($guard);
            $user = $guardInstance->getProvider()->retrieveById($userId);

            if ($user !== null) {
                $guardInstance->setUser($user);
            }
        }

        $next($job);
    }
}
