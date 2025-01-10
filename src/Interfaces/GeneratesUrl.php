<?php

namespace pxlrbt\FilamentExcel\Interfaces;

interface GeneratesUrl
{
    public function generateUrl(array $export): string;
}
