<?php

namespace pxlrbt\FilamentExcel\Columns;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ColumnFormats
{
    const NUMBER = 'number';
    const DATE = 'date';
    const DATETIME = 'datetime';
    const PERCENTAGE = 'percentage';
    const ALL = 'all';

    public static function getAllOptions(): array
    {
        return array_merge(
            [
                NumberFormat::FORMAT_GENERAL => NumberFormat::FORMAT_GENERAL,
                NumberFormat::FORMAT_TEXT => NumberFormat::FORMAT_TEXT,
            ],
            static::getNumberOptions(),
            static::getPercentageOptions(),
            static::getDateOptions(),
            static::getDateTimeOptions(),
        );
    }

    public static function getDateTimeOptions(): array
    {
        $formats = [
            NumberFormat::FORMAT_DATE_XLSX22,
            NumberFormat::FORMAT_DATE_DATETIME,
            NumberFormat::FORMAT_DATE_TIME1,
            NumberFormat::FORMAT_DATE_TIME2,
            NumberFormat::FORMAT_DATE_TIME3,
            NumberFormat::FORMAT_DATE_TIME4,
            NumberFormat::FORMAT_DATE_TIME5,
            NumberFormat::FORMAT_DATE_TIME6,
            NumberFormat::FORMAT_DATE_TIME7,
            NumberFormat::FORMAT_DATE_TIME8,
            NumberFormat::FORMAT_DATE_YYYYMMDDSLASH,
        ];

        return array_combine($formats, $formats);
    }

    public static function getDateOptions(): array
    {
        $formats = [
            NumberFormat::FORMAT_DATE_YYYYMMDD2,
            NumberFormat::FORMAT_DATE_YYYYMMDD,
            NumberFormat::FORMAT_DATE_DDMMYYYY,
            NumberFormat::FORMAT_DATE_DMYSLASH,
            NumberFormat::FORMAT_DATE_DMYMINUS,
            NumberFormat::FORMAT_DATE_DMMINUS,
            NumberFormat::FORMAT_DATE_MYMINUS,
            NumberFormat::FORMAT_DATE_XLSX14,
            NumberFormat::FORMAT_DATE_XLSX15,
            NumberFormat::FORMAT_DATE_XLSX16,
            NumberFormat::FORMAT_DATE_XLSX17,
        ];

        return array_combine($formats, $formats);
    }

    public static function getPercentageOptions(): array
    {
        $formats = [
            NumberFormat::FORMAT_PERCENTAGE,
            NumberFormat::FORMAT_PERCENTAGE_0,
            NumberFormat::FORMAT_PERCENTAGE_00,
        ];

        return array_combine($formats, $formats);
    }

    public static function getNumberOptions(): array
    {

        $formats = [
            NumberFormat::FORMAT_NUMBER,
            NumberFormat::FORMAT_NUMBER_0,
            NumberFormat::FORMAT_NUMBER_00,
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];

        return array_combine($formats, $formats);
    }

}
