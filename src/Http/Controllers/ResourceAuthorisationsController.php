<?php

namespace Pauldstar\NovaCompactUi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResourceAuthorisationsController extends Controller
{
    /**
     * Get resource authorisations
     *
     * @param NovaRequest $request
     * @return JsonResponse
     */
    public function __invoke(NovaRequest $request)
    {
        $resource = $request->newResourceWith(
            tap($request->findModelQuery(), function ($query) use ($request) {
                $request->newResource()->detailQuery($request, $query);
            })->firstOrFail()
        );

        return response()->json([
            'authorizedToView' => $resource->authorizedToView($request),
            'authorizedToCreate' => $resource->authorizedToCreate($request),
            'authorizedToUpdate' => $resource->authorizedToUpdate($request),
            'authorizedToDelete' => $resource->authorizedToDelete($request),
            'authorizedToRestore' => $resource::softDeletes() && $resource->authorizedToRestore($request),
            'authorizedToForceDelete' => $resource::softDeletes() && $resource->authorizedToForceDelete($request),
            'softDeletes' => $resource::softDeletes(),
            'softDeleted' => $resource->isSoftDeleted()
        ]);
    }
}
