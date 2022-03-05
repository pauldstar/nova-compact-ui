<?php

namespace Pauldstar\NovaCompactUi;

use Pauldstar\NovaCompactUi\Traits\ReactiveField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReactiveBelongsTo extends BelongsTo
{
    use ReactiveField {
        ReactiveField::on as reactOn;
    }

    public $component = 'reactive-belongs-to-field';

    /**
     * The input field whose value this field depends on
     * Could be set to custom field
     */
    public string $dependsOn = '';

    /**
     * If the relation between this field and the input field is BelongsToMany
     * and NOT the default BelongsTo
     */
    private ?string $belongsToMany;

    /**
     * The field name in database table representing $dependsOn
     */
    public string $dependsOnKey;

    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute, $resource);
        $this->withoutTrashed();
    }

    /**
     * Set the depends on field and depends on key
     * Used to filter resources to be displayed in the dropdown list
     *
     * @param string $formField input field used to filter select options
     * @param string|null $key database column or relation name representing $dependsOn field
     * @param string|null $belongsToMany the relation name if the relationship to $formField is BelongsToMany
     * @return $this
     */
    public function dependsOn(string $formField, string $key = null, string $belongsToMany = null): self
    {
        $this->dependsOn = $formField;

        $this->dependsOnKey = $key
            ?? ($belongsToMany ? "{$belongsToMany}_id" : "{$formField}_id");

        $this->belongsToMany = $belongsToMany;

        return $this;
    }

    public function on($event, callable $callback = null): self
    {
        return $this->reactOn($event, $callback ?? null);
    }

    protected function handle($callback): self
    {
        $this->eventHandler = fn(Collection $values, NovaRequest $request) => [
            'dependsOnId' => $values->get($this->dependsOn),
            'value' => is_callable($callback) ? $callback($values, $request) : $callback
        ];

        return $this;
    }

    /**
     * Specify viaResource values for:
     * 1. creating this resource via modal
     *
     * @param string $viaResource
     * @param string $viaRelationship
     * @param int $viaResourceId
     * @return ReactiveBelongsTo
     */
    public function dependsOnViaResource(string $viaResource, string $viaRelationship, ?int $viaResourceId): self
    {
        return $this->withMeta([
            'dependsOnResource' => $viaResource,
            'dependsOnRelationship' => $viaRelationship,
            'dependsOnResourceId' => $viaResourceId ?? (
                request()->get('viaResource') === $viaResource
                    ? request()->get('viaResourceId') : null
            )
        ]);
    }

    /**
     * Specify if the relationship should be searchable using table format.
     *
     * @return ReactiveBelongsTo
     */
    public function searchableTable()
    {
        return $this->withMeta(['searchableTable' => true]);
    }

    /**
     * Build an associatable query for the field.
     * Here is where we add the depends on value and filter results
     *
     * @param NovaRequest $request
     * @param bool $withTrashed
     * @return Builder|\Laravel\Nova\Query\Builder
     */
    public function buildAssociatableQuery(NovaRequest $request, $withTrashed = false)
    {
        $query = parent::buildAssociatableQuery($request, $withTrashed);

        if ($request->missing('dependsOnValue')) {
            return $query;
        }

        $query = $query->toBase();

        if ($this->belongsToMany) {
            return $query->whereHas($this->belongsToMany,
                function (Builder $query) use ($request) {
                    $query->where($this->dependsOnKey, $request->dependsOnValue);
                }
            );
        }

        return $query->where($this->dependsOnKey, $request->dependsOnValue);
    }
}
