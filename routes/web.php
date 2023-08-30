<?php

Route::get('filament-excel/{path}', function (string $path) {
    if (config('excel.temporary_files.remote_disk')) {
        app()->terminating(function () use ($path) {
            Storage::disk(config('excel.temporary_files.remote_disk'))->delete($path);
        });

        return Storage::disk(config('excel.temporary_files.remote_disk'))
            ->download($path);
    } else {
        return response()->download(
            Storage::disk('filament-excel')->path($path),
            substr($path, 37)
        )->deleteFileAfterSend($shouldDelete = true);
    }
})
    ->where('path', '.*')
    ->name('filament-excel-download');
