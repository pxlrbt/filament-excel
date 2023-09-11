<?php

namespace pxlrbt\FilamentExcel;

use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Commands\PruneExportsCommand;
use pxlrbt\FilamentExcel\Events\ExportFinishedEvent;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentExcelServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        config()->set('filesystems.disks.filament-excel', [
            'driver' => 'local',
            'root' => storage_path('app/filament-excel'),
            'url' => config('app.url').'/filament-excel',
        ]);

        parent::register();
    }

    public function configurePackage(Package $package): void
    {
        $package->name('filament-excel')
            ->hasCommands([PruneExportsCommand::class])
            ->hasRoutes(['web'])
            ->hasTranslations();
    }

    public function bootingPackage()
    {
        Filament::serving($this->sendExportFinishedNotification(...));

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(PruneExportsCommand::class)->daily();
        });

        Event::listen(ExportFinishedEvent::class, [$this, 'cacheExportFinishedNotification']);
    }

    public function sendExportFinishedNotification(): void
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

            if (! Storage::disk('filament-excel')->exists($export['filename'])) {
                continue;
            }

            Notification::make(data_get($export, 'id'))
                ->title(__('filament-excel::notifications.download_ready.title'))
                ->body(__('filament-excel::notifications.download_ready.body'))
                ->success()
                ->icon('heroicon-o-arrow-down-tray')
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

    public function cacheExportFinishedNotification(ExportFinishedEvent $event): void
    {
        if ($event->userId === null) {
            return;
        }

        $key = $this->getNotificationCacheKey($event->userId);

        $exports = cache()->pull($key, []);
        $exports[] = [
            'id' => Str::uuid(),
            'filename' => $event->filename,
            'userId' => $event->userId,
        ];

        cache()->put($key, $exports);
    }

    protected function getNotificationCacheKey($userId): string
    {
        return 'filament-excel:exports:'.$userId;
    }
}
