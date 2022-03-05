<?php

namespace Pauldstar\NovaCompactUi\Traits;

trait ReactiveField
{
    use InlineField { InlineField::jsonSerialize as inlineSerialize; }

    /**
     * The event this fields broadcasts
     * @var string
     */
    public string $broadcastFrom = '';

    /**
     * The event this fields handles
     * @var string|array
     */
    public $listensTo;

    /**
     * Custom fields with values derived from broadcast input
     * After which these fields will also be broadcast
     *
     * @var callable
     */
    public $customFields;

    /**
     * The function to call when input is detected
     * @var callable
     */
    public $eventHandler;

    /**
     * Tells the client side component which event to broadcast
     *
     * @param string $event
     * @param bool $emitOnLoad will emit its value on load if field is pre-filled
     * @return $this
     */
    public function emit(string $event): self
    {
        $this->broadcastFrom = $event;
        return $this;
    }

    /**
     * @param string|string[] $event event(s) that this component handles
     * @param mixed $callback returns response to event
     * @return $this
     */
    public function on($event, $callback): self
    {
        $this->listensTo = $event;
        $this->handle($callback);

        return $this;
    }

    /**
     * @param string|array $event
     * @return bool
     */
    public function handlesEvent($event): bool
    {
        if (is_array($event) && is_array($this->listensTo)) {
            return !empty(array_intersect($event, $this->listensTo));
        }

        if (is_array($event)) {
            return in_array($this->listensTo, $event);
        }

        if (is_array($this->listensTo)) {
            return in_array($event, $this->listensTo);
        }

        return $this->listensTo === $event;
    }

    /**
     * A pre-broadcast callback to create new fields with values derived from broadcast input
     *
     * @param string $field
     * @param callable $callback
     * @return $this
     */
    public function customField(string $field, callable $callback): self
    {
        $this->customFields || $this->customFields = [];
        $this->customFields[$field] = $callback;
        return $this;
    }

    /**
     * The callback that handles broadcast values
     *
     * @param mixed $callback
     * @return $this
     */
    protected function handle($callback): self
    {
        $this->eventHandler = $callback;
        return $this;
    }

    /**
     * Don't fill this attribute on the model when saving
     *
     * @return $this
     */
    public function dontFill(): self
    {
        $this->fillUsing(fn() => null);
        return $this;
    }

    /**
     * Serialize the field to JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'broadcastFrom' => $this->broadcastFrom,
            'listensTo' => $this->listensTo,
        ], $this->inlineSerialize(), parent::jsonSerialize());
    }
}
