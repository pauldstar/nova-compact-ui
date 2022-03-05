<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Laravel\Nova\Fields\Trix;

class ReactiveTrix extends Trix
{
    use ReactiveField;

    public $component = 'reactive-trix-field';

    public function largeTextArea(): self
    {
        return $this->withMeta(['largeTextArea' => true]);
    }
}
