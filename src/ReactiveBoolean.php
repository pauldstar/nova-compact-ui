<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Boolean;

class ReactiveBoolean extends Boolean
{
    use ReactiveField;

    public $component = 'reactive-boolean-field';
}
