<?php

namespace pxlrbt\FilamentExcel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileAttributes;

class PruneExportsCommand extends Command
{
    protected $signature = 'filament-excel:prune';

    protected $description = 'Prune dangling exports';

    public function handle()
    {
        $diskName = config('filament-excel.disk', 'filament-excel');
        $s3Path = config('filament-excel.s3_path', '');
        
        collect(Storage::disk($diskName)->listContents($s3Path, false))
            ->each(function (FileAttributes $file) use ($diskName) {
                if ($file->type() === 'file' && $file->lastModified() < now()->subDay()->getTimestamp()) {
                    Storage::disk($diskName)->delete($file->path());
                }
            });
            
        $this->info("Successfully pruned old exports from '{$diskName}' disk.");
    }
}