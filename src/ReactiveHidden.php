<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Hidden;

class ReactiveHidden extends Hidden
{
    use ReactiveField;

    public $component = 'reactive-hidden-field';
}
