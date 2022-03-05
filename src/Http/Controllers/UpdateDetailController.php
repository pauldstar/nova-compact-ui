<?php

namespace Pauldstar\NovaCompactUi\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;

class UpdateDetailController extends Controller
{
    /**
     * List the update and detail fields for the given resource.
     *
     * @param NovaRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function __invoke(NovaRequest $request)
    {
        $resource = $request->newResourceWith(
            tap($request->findModelQuery(), function ($query) use ($request) {
                $request->newResource()->detailQuery($request, $query);
            })->firstOrFail()
        );

        $resource->authorizeToUpdate($request);

        $resourceDetails = $resource->serializeForDetail($request, $resource);

        $listableFields = array_filter($resourceDetails['fields'], function ($field) {
            return in_array(
                $field->component,
                ['has-many-field', 'belongs-to-many-field']
            );
        });

        $resourceDetails['fields'] = array_merge(
            $resource->updateFieldsWithinPanels($request)->toArray(),
            $listableFields
        );

        $panels = $resource->availablePanelsForUpdate($request);
        $panels[0]->component = 'form-panel';

        return response()->json([
            'title' => Str::limit($resource->title(), 15),
            'resource' => $resourceDetails,
            'panels' => $panels
        ]);
    }
}
