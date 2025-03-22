<?php

namespace pxlrbt\FilamentExcel;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FilamentExport
{
    public static ?Closure $createExportUrlUsing = null;

    public static function createExportUrlUsing(Closure $closure): void
    {
        static::$createExportUrlUsing = $closure;
    }

    protected function sendDatabaseNotification(array $export, string $url): void
    {
        $previousLocale = app()->getLocale();

        if (isset($export['locale'])) {
            app()->setLocale($export['locale']);
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
            ->sendToDatabase(Filament::auth()->user());

        app()->setLocale($previousLocale);
    }

    protected function sendPersistentNotification(array $export, string $url): void
    {
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

    public function sendNotification(): void
    {
        $exports = cache()->pull(static::getNotificationCacheKey(Filament::auth()->id()));

        if (! filled($exports)) {
            return;
        }

        foreach ($exports as $export) {
            if (! Storage::disk('filament-excel')->exists($export['filename'])) {
                continue;
            }

            $url = $this->createUrl($export);

            if (Filament::getCurrentPanel()->hasDatabaseNotifications()) {
                $this->sendDatabaseNotification($export, $url);
            } else {
                $this->sendPersistentNotification($export, $url);
            }
        }
    }

    public static function getNotificationCacheKey($userId): string
    {
        return 'filament-excel:exports:'.$userId;
    }

    protected function createUrl(array $export): string
    {
        if (static::$createExportUrlUsing !== null) {
            return (static::$createExportUrlUsing)($export);
        }

        return URL::temporarySignedRoute(
            'filament-excel-download',
            now()->addHours(24),
            ['path' => $export['filename']]
        );
    }
}
