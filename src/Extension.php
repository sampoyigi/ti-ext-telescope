<?php

declare(strict_types=1);

namespace SamPoyigi\Telescope;

use Igniter\System\Classes\BaseExtension;
use Igniter\User\Facades\AdminAuth;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Override;

/**
 * Telescope Extension Information File
 */
class Extension extends BaseExtension
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/telescope.php', 'telescope'
        );

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function(IncomingEntry $entry): bool {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    #[Override]
    public function boot(): void
    {
        $this->authorization();
    }

    public function registerSchedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune')->daily();
    }

    /**
     * Registers any back-end permissions used by this plugin.
     */
    #[Override]
    public function registerPermissions(): array
    {
        return [
            'SamPoyigi.Telescope.Access' => [
                'group' => 'igniter::system.permissions.name',
                'label' => 'Access to the Telescope dashboard',
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     */
    #[Override]
    public function registerNavigation(): array
    {
        return [
            'tools' => [
                'child' => [
                    'telescope' => [
                        'title' => 'Telescope',
                        'class' => 'telescope',
                        'href' => url(config('telescope.path')),
                        'priority' => 500,
                        'permissions' => ['SamPoyigi.Telescope.Access'],
                        'target' => '_blank',
                    ],
                ],
            ],
        ];
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Configure the Telescope authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        Telescope::auth(function($request) {
            if (!AdminAuth::check()) {
                return false;
            }

            return AdminAuth::getUser()->hasPermission('SamPoyigi.Telescope.Access');
        });
    }
}
