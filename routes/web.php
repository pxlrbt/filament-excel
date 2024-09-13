<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('filament-excel/{path}', function (string $path) {
    $filename = substr($path, 37);
    $path = Storage::disk('filament-excel')->path($path);

    return
        response()
            ->download($path, $filename)
            ->deleteFileAfterSend();
})
    ->middleware(['web', 'signed'])
    ->where('path', '.*')
    ->name('filament-excel-download');
