<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('filament-excel/{path}', function (string $path) {
    try {
        $diskName = config('filament-excel.disk', 'filament-excel');
        $diskDriver = config('filament-excel.disk_driver', 'local');
        $autoDelete = config('filament-excel.auto_delete_after_download', true);
        $filename = substr($path, 37);
        
        // Handle S3 storage differently than local storage
        if ($diskDriver === 's3') {
            // For S3, generate a temporary URL and redirect to it
            $expirationMinutes = (int)config('filament-excel.temporary_url_expiration', 24 * 60);
            
            $temporaryUrl = Storage::disk($diskName)->temporaryUrl(
                $path, 
                now()->addMinutes($expirationMinutes)
            );
            
            // If auto-delete is enabled, schedule the file for deletion
            if ($autoDelete) {
                // Delete after the user has had time to download
                dispatch(function () use ($diskName, $path) {
                    try {
                        Storage::disk($diskName)->delete($path);
                    } catch (\Exception $e) {
                        Log::error("Failed to delete file after download: " . $e->getMessage());
                    }
                })->delay(now()->addMinutes(5)); // 5-minute delay to ensure download completes
            }
            
            return redirect()->away($temporaryUrl);
        }
        
        // Handle local storage (original behavior)
        $path = Storage::disk($diskName)->path($path);
        
        $response = response()
            ->download($path, $filename);
            
        if ($autoDelete) {
            $response->deleteFileAfterSend();
        }
        
        return $response;
    } catch (\Exception $e) {
        Log::error('Error in filament-excel download route: ' . $e->getMessage());
        abort(404, 'File not found or could not be accessed');
    }
})
    ->middleware(['web', 'signed'])
    ->where('path', '.*')
    ->name('filament-excel-download');