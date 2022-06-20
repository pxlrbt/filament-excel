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
        collect(Storage::disk('filament-excel')->listContents('', false))
            ->each(function (FileAttributes $file) {
                if ($file->type() === 'file' && $file->lastModified() < now()->subDay()->getTimestamp()) {
                    Storage::disk('filament-excel')->delete($file->path());
                }
            });
    }
}
