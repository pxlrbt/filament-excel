<?php

namespace pxlrbt\FilamentExcel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ExportFinishedEvent
{
    use Dispatchable;

    public function __construct(
        public string $filename,
        public int|string|null $userId,
        public string $locale,
    ) {
        //
    }
}
