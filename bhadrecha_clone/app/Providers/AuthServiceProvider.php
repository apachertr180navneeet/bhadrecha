<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return self::checkTripPermission($user, $ability);
        });
    }

    /**
     * Resolve permissions for Trips and Trip Details modules, ensuring aliases work interchangeably.
     */
    public static function checkTripPermission($user, string $ability): ?bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Permission aliases for Trips / Trip Details
        $aliases = [
            'view trips' => ['view trip details', 'view trip'],
            'view trip details' => ['view trips', 'view trip'],
            'create trips' => ['create trip details', 'create trip', 'add trips', 'add trip details'],
            'create trip details' => ['create trips', 'create trip', 'add trips', 'add trip details'],
            'edit trips' => ['edit trip details', 'edit trip'],
            'edit trip details' => ['edit trips', 'edit trip'],
            'delete trips' => ['delete trip details', 'delete trip'],
            'delete trip details' => ['delete trips', 'delete trip'],
        ];

        // 1. In-memory / eager loaded check (safe for testing or cached users)
        if ($user->relationLoaded('permissions') && $user->relationLoaded('roles')) {
            $allPerms = array_merge(
                $user->permissions->pluck('name')->toArray(),
                $user->roles->flatMap(function($r) {
                    return $r->relationLoaded('permissions') ? $r->permissions->pluck('name') : collect();
                })->toArray()
            );

            if (in_array($ability, $allPerms, true)) {
                return true;
            }
            if (isset($aliases[$ability])) {
                foreach ($aliases[$ability] as $alias) {
                    if (in_array($alias, $allPerms, true)) {
                        return true;
                    }
                }
            }
            return false;
        }

        // 2. Active database check
        if (isset($aliases[$ability]) && method_exists($user, 'getAllPermissions')) {
            try {
                $userPermNames = $user->getAllPermissions()->pluck('name')->toArray();
                if (in_array($ability, $userPermNames, true)) {
                    return true;
                }
                foreach ($aliases[$ability] as $alias) {
                    if (in_array($alias, $userPermNames, true)) {
                        return true;
                    }
                }
            } catch (\Throwable $e) {
                // Fall back if DB is unavailable
            }
        }

        return null;
    }
}
