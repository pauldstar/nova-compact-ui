<?php

namespace Pauldstar\NovaCompactUi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReactiveFieldController extends Controller
{
    public function __invoke(NovaRequest $request, string $resource, string $event): JsonResponse
    {
        $listeners = $this->getSortedListeners($request, $event);
        $request->merge($this->customFields($request, $listeners));

        $results = [];
        $emittedEvents = [$event];
        $emittedValues = collect($request->all());

        foreach ($listeners as $field) {
            if ($field->handlesEvent($emittedEvents)) {
                $emittedValues->forget($field->attribute);

                $results[$field->attribute] = is_callable($field->eventHandler)
                    ? call_user_func($field->eventHandler, $emittedValues, $request)
                    : $field->eventHandler;

                $results[$field->attribute] === null && $results[$field->attribute] = '';

                $emittedValues->put($field->attribute, $field instanceof  BelongsTo
                    ? $results[$field->attribute]['value']: $results[$field->attribute]
                );

                if ($field->broadcastFrom) {
                    $emittedEvents[] = $field->broadcastFrom;
                }
            }
        }

        return response()->json($results);
    }

    /**
     *  Get listeners sorted by prioritising broadcasting fields over listener fields
     *
     * @param NovaRequest $request
     * @param string $event
     * @return FieldCollection
     */
    private function getSortedListeners(NovaRequest $request, string $event): FieldCollection
    {
        $novaResource = $request->get('resourceId')
            ? $request->newResourceWith($request->findModelOrFail())
            : $request->newResource();

        $listeners = $novaResource->availableFields($request)->filter(
            function ($field) use ($event) {
                return !empty($field->listensTo) && $field->broadcastFrom !== $event;
            }
        );

        return $listeners->sort(fn($a, $b) => $this->compareReactiveFields($a, $b));
    }

    public static function compareReactiveFields($a, $b): int
    {
        if (empty($a->broadcastFrom) && empty($b->broadcastFrom)) {
            return 0;
        }

        if ($a->broadcastFrom && $b->broadcastFrom) {
            if ($b->handlesEvent($a->broadcastFrom)) {
                return -1;
            }

            if ($a->handlesEvent($b->broadcastFrom)) {
                return 1;
            }

            return 0;
        }

        return $a->broadcastFrom ? -1 : 1;
    }

    private function customFields(NovaRequest $request, FieldCollection $listeners): array
    {
        return $listeners
            ->whereIn('attribute', array_keys($request->except('resourceId')))
            ->flatMap($this->customFieldValues($request))
            ->toArray();
    }

    private function customFieldValues(NovaRequest $request): callable
    {
        return function ($field) use ($request) {
            $customFields = [];

            if (isset($field->customFields)) {
                foreach ($field->customFields as $custom => $callback) {
                    $customFields[$custom] = call_user_func(
                        $callback,
                        $request->get($field->attribute),
                        $request
                    );
                }
            }

            return $customFields;
        };
    }
}
