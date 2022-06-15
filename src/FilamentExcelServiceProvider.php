<?php

namespace pxlrbt\FilamentExcel;

use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use pxlrbt\FilamentExcel\Commands\PruneExportsCommand;

class FilamentExcelServiceProvider extends ServiceProvider
{
    public function register()
    {
        config()->set('filesystems.disks.filament-excel', [
            'driver' => 'local',
            'root' => storage_path('app/filament-excel'),
            'url' => config('app.url') . '/filament-excel'
        ]);

        parent::register();
    }

    public function boot()
    {
        $this->commands([
            PruneExportsCommand::class,
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(PruneExportsCommand::class)->daily();
        });

        Filament::serving(function () {
            $notifications = cache()->pull('filament-excel:notifications:' . auth()->id());

            if (! filled($notifications)) {
                return;
            }

            foreach ($notifications as $notification) {
                Filament::notify(
                    'success',
                    new HtmlString($notification)
                );
            }
        });
    }
}
