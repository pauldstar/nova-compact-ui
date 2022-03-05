<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Date;

class ReactiveDate extends Date
{
    use ReactiveField;

    public $component = 'reactive-date-field';

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->format('DD/MM/YYYY')->pickerFormat('d/m/Y');
    }
}
