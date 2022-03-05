<?php

namespace Pauldstar\NovaCompactUi;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class ColorCode
{
    /**
     * @param Request|NovaRequest $request
     *
     * @param array $options
     * Using popular bootstrap css colour classes
     * Only works with fields that can be displayed as HTML
     *
     * Example
     *
     * Field::make('name')->displayUsing(ColorCode::set($request, [
     *     'val1' => 'success'
     *     'val2' => 'info'
     *     'val3' => 'warning'
     *     'val4' => 'danger'
     * ]))
     *
     * No need to set value for 'primary' since it's the default
     *
     * @return callable
     */
    public static function set(Request $request, array $options): callable
    {
        return function ($value) use ($options, $request) {
            $value instanceof Resource && $value = $value->title();

            if ($request->isResourceIndexRequest() && isset($options[$value])) {
                return "<span class='text-{$options[$value]} font-bold'>{$value}</span>";
            }

            return $value;
        };
    }
}
