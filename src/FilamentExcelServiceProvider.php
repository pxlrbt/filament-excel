<?php

namespace pxlrbt\FilamentExcel;

use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Commands\PruneExportsCommand;
use pxlrbt\FilamentExcel\Events\ExportFinishedEvent;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentExcelServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        // Publish and merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-excel.php', 'filament-excel'
        );

        // Get disk settings from config or use defaults
        $diskName = config('filament-excel.disk', 'filament-excel');
        $diskDriver = config('filament-excel.disk_driver', 'local');
        $diskConfig = [];

        // If using local disk, set default local disk config
        if ($diskDriver === 'local') {
            $diskConfig = [
                'driver' => 'local',
                'root' => storage_path('app/filament-excel'),
                'url' => config('app.url').'/filament-excel',
            ];
        }
        // If using S3, inherit the S3 configuration from the existing s3 disk
        elseif ($diskDriver === 's3' && config()->has('filesystems.disks.s3')) {
            $s3Config = config('filesystems.disks.s3');
            $diskConfig = array_merge($s3Config, [
                'root' => config('filament-excel.s3_path', 'filament-excel'),
            ]);
        }

        // Set the disk configuration
        config()->set("filesystems.disks.{$diskName}", $diskConfig);

        parent::register();
    }

    public function configurePackage(Package $package): void
    {
        $package->name('filament-excel')
            ->hasConfigFile()
            ->hasCommands([PruneExportsCommand::class])
            ->hasRoutes(['web'])
            ->hasTranslations();
    }

    public function bootingPackage()
    {
        Filament::serving(fn () => app(FilamentExport::class)->sendNotification());

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(PruneExportsCommand::class)->daily();
        });

        Event::listen(ExportFinishedEvent::class, [$this, 'cacheExportFinishedNotification']);
    }

    public function cacheExportFinishedNotification(ExportFinishedEvent $event): void
    {
        if ($event->userId === null) {
            return;
        }

        $key = FilamentExport::getNotificationCacheKey($event->userId);

        $exports = cache()->pull($key, []);
        $exports[] = [
            'id' => Str::uuid(),
            'filename' => $event->filename,
            'userId' => $event->userId,
            'locale' => $event->locale,
        ];

        cache()->put($key, $exports);
    }
}