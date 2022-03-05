<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Number;

class ReactiveNumber extends Number
{
    use ReactiveField;

    public $component = 'reactive-number-field';
}
