<?php

namespace Pauldstar\NovaCompactUi\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class DeleteUnsavedChangesController extends Controller
{
    /**
     * Delete unsaved changes for the tabulated resource field
     *
     * @param NovaRequest $request
     */
    public function __invoke(NovaRequest $request)
    {
        $request->newQuery()->doesntHave($request->belongsTo)->forceDelete();
    }
}
