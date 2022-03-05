<?php

namespace Pauldstar\NovaCompactUi;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Panel;

class Column extends Panel
{
    /**
     * This column's order
     *
     * @var int
     */
    private $order;

    /**
     * If column has full width (like a row)
     *
     * @var bool
     */
    private bool $columnRow = false;

    /**
     * Constants for specifying this column's order.
     * e.g. Column::make(Column::FIRST, $fields)
     *
     * @var int
     */
    public const FIRST = 0;
    public const LAST = 1;

    /**
     *
     * @param int $order
     * @param callable|array $fields
     * @param bool $isRow
     */
    public function __construct(int $order, $fields = [], bool $isRow = false)
    {
        $this->order = $order;
        $this->columnRow = $isRow;
        parent::__construct("column-{$order}", $this->prepareFields($fields));
    }

    /**
     * Create fields with column order
     *
     * @param callable|array $fields
     * @return array
     */
    public function prepareFields($fields = [])
    {
        return collect(is_callable($fields) ? $fields() : $fields)
            ->each(function (Field $field) {
                $field->withMeta([
                    'column' => $this->order,
                    'columnRow' => $this->columnRow
                ]);
            })->all();
    }

    /**
     * Create fields with column order
     *
     * @param string $panel
     * @return void
     */
    private function setPanel(string $panel)
    {
        array_walk($this->data, function (Field $field) use ($panel) {
            $field->panel = $panel;
        });
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'panel':
                $this->setPanel($value);
                break;
        }
    }
}
