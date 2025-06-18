<?php

namespace App\Models;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CustomValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, mixed $value): bool
    {
        return parent::bindValue($cell, $value);
    }
}