<?php

namespace Pauldstar\NovaCompactUi;

use App\Helpers;
use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Currency;

class ReactiveCurrency extends Currency
{
    use ReactiveField;

    public $component = 'reactive-currency-field';

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $displayUsing = function ($value) {
            return ! $this->isNullValue($value) ? Helpers::currencyFormat($value) : null;
        };

        $this->currency('GBP')->resolveUsing($displayUsing)->displayUsing($displayUsing);
    }

    public function handle(callable $fn)
    {
        $this->eventHandler = function (Collection $col) use ($fn) {
            return Helpers::currencyFormat($fn($col));
        };

        return $this;
    }
}
