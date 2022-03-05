<?php

namespace Pauldstar\NovaCompactUi\Traits;

use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Inspired from pdmfc/nova-inline-text
 * https://github.com/pdmfc/nova-inline-text
 *
 * @package Pdmfc\NovaFields
 */
trait InlineField
{
    /**
     * @var bool
     */
    protected bool $inlineOnIndex = false;

    /**
     * @var bool
     */
    protected bool $inlineRefreshOnSaving = false;

    /**
     * The event trigger used to save the field value,
     *
     * @var string
     */
    protected string $inlineSaveTrigger = 'keyup.enter';

    /**
     * Allows field to be editable on index view.
     *
     * @param callable|bool $callback
     * @return self
     */
    public function inlineOnIndex($callback = true): self
    {
        if (is_callable($callback)) {
            $callback = call_user_func($callback, resolve(NovaRequest::class));
        }

        $this->inlineOnIndex = $callback;

        return $this;
    }

    /**
     * It updates the resource table when saving the field value.
     *
     * @return self
     */
    public function inlineRefreshOnSaving(): self
    {
        $this->inlineRefreshOnSaving = true;

        return $this;
    }

    /**
     * @param string $event
     * @return InlineField
     */
    public function inlineSaveOn(string $event): self
    {
        $this->inlineSaveTrigger = $event;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'inlineOnIndex' => $this->inlineOnIndex,
            'inlineRefreshOnSaving' => $this->inlineRefreshOnSaving,
            'inlineSaveTrigger' => $this->inlineSaveTrigger
        ], parent::jsonSerialize());
    }
}
