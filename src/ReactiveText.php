<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Text;

class ReactiveText extends Text
{
    use ReactiveField;

    public $component = 'reactive-text-field';
}
