<?php

namespace pxlrbt\FilamentExcel;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use pxlrbt\FilamentExcel\Commands\PruneExportsCommand;
use pxlrbt\FilamentExcel\Events\ExportFinishedEvent;

class FilamentExcelServiceProvider extends ServiceProvider
{
    public function register()
    {
        config()->set('filesystems.disks.filament-excel', [
            'driver' => 'local',
            'root' => storage_path('app/filament-excel'),
            'url' => config('app.url') . '/filament-excel',
        ]);

        parent::register();
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'filament-excel');

        $this->commands([PruneExportsCommand::class]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(PruneExportsCommand::class)->daily();
        });

        Event::listen(ExportFinishedEvent::class, [$this, 'cacheExportFinishedNotification']);

        Filament::serving(Closure::fromCallable([$this, 'sendExportFinishedNotification']));
    }

    public function sendExportFinishedNotification()
    {
        $exports = cache()->pull($this->getNotificationCacheKey(auth()->id()));

        if (! filled($exports)) {
            return;
        }

        foreach ($exports as $export) {
            $url = URL::temporarySignedRoute(
                'filament-excel-download',
                now()->addHours(24),
                ['path' => $export['filename']]
            );

            Notification::make('filament-excel:exports:' . md5($export['filename']))
                ->title(__('filament-excel::notifications.download_ready.title'))
                ->body(__('filament-excel::notifications.download_ready.body'))
                ->success()
                ->icon('heroicon-o-download')
                ->actions([
                    Action::make('download')
                        ->label(__('filament-excel::notifications.download_ready.download'))
                        ->url($url, shouldOpenInNewTab: true)
                        ->button()
                        ->close(),
                ])
                ->persistent()
                ->send();
        }
    }

    public function cacheExportFinishedNotification(ExportFinishedEvent $event)
    {
        if ($event->userId === null) {
            return;
        }

        $key = $this->getNotificationCacheKey($event->userId);

        $exports = cache()->pull($key, []);
        $exports[] = ['filename' => $event->filename, 'userId' => $event->userId];

        cache()->put($key, $exports);
    }

    protected function getNotificationCacheKey($userId)
    {
        return 'filament-excel:exports:' . $userId;
    }
}
