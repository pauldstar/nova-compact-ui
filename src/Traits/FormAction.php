<?php

namespace Pauldstar\NovaCompactUi\Traits;

trait FormAction
{
    /**
     * Indicates if this action is available on the resource form view.
     *
     * @var bool
     */
    public $showWhenCreating  = false;

    /**
     * Indicates if this action is available on the resource form view.
     *
     * @var bool
     */
    public $showWhenUpdating  = false;

    /**
     * Show the action on all form views.
     *
     * @return $this
     */
    public function showOnForm()
    {
        $this->showWhenCreating = true;
        $this->showWhenUpdating = true;

        return $this;
    }

    /**
     * Show the action on the creating form view.
     *
     * @return $this
     */
    public function showWhenCreating()
    {
        $this->showWhenCreating = true;
        return $this;
    }

    /**
     * Show the action on the updating form view.
     *
     * @return $this
     */
    public function showWhenUpdating()
    {
        $this->showWhenUpdating = true;
        return $this;
    }

    public function jsonSerialize()
    {
        return array_merge([
            'showWhenCreating' => $this->showWhenCreating,
            'showWhenUpdating' => $this->showWhenUpdating,
        ], parent::jsonSerialize());
    }
}
