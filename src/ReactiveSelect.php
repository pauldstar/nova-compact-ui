<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Select;

class ReactiveSelect extends Select
{
    use ReactiveField;

    public $component = 'reactive-select-field';

    /**
     * Set each option's value as it's key
     *
     * @param $options
     * @return ReactiveSelect
     */
    public function optionsCombined($options): self
    {
        $options = array_combine($options, $options);
        return parent::options($options);
    }

    public function asHtml(): self
    {
        return $this->withMeta(['asHtml' => true]);
    }
}
