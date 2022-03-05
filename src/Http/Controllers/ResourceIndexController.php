<?php

namespace Pauldstar\NovaCompactUi\Http\Controllers;

use App\Nova\Resource;
use App\TableNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller;
use Pauldstar\NovaCompactUi\Http\Requests\ResourceIndexRequest;

class ResourceIndexController extends Controller
{
    public function __invoke(ResourceIndexRequest $request): JsonResponse
    {
        $resource = $request->resource();

        [$paginator, $total] = $request->searchIndex();

        $items = tap($paginator->getCollection(), $this->forTableGroup($request));
        $resources = $items->mapInto($resource)->map->serializeForIndex($request);

        return response()->json([
            'label' => $resource::label(),
            'resources' => $this->withTableNotes($request, $resources),
            'tableNotes' => $this->getTableNotes($request),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'per_page_options' => $resource::perPageOptions(),
            'total' => $total,
            'softDeletes' => $resource::softDeletes()
        ]);
    }

    private function forTableGroup(ResourceIndexRequest $request): \Closure
    {
        return function (&$items) use ($request) {
            $tabbedIndex = $request->route('resource') === 'table-groups'
                && $request->get('viaResource');

            if ($tabbedIndex) {
                $items = $items->filter(function ($group) use ($request) {
                    return $group->groupable_type === Resource::morphClassForKey(
                        $request->get('viaResource')
                    ) && $group->groupable_id === (int) $request->get('viaResourceId');
                })->sortBy('position')->values();
            }
        };
    }

    private function withTableNotes(ResourceIndexRequest $request, Collection $resources):  Collection
    {
        $resources = $resources->map(function ($res) {
            $res['tableNoteId'] = $res['id']->resource->table_note_id;
            return $res;
        });

        return $request->get('draggable')
            ? $resources->sortBy('id.resource.position')->values() : $resources;
    }

    private function getTableNotes(ResourceIndexRequest $request): ?Collection
    {
        if ($request->missing('notable')) {
            return null;
        }

        $query = TableNote::query()->where([
            'notable_type' => Resource::morphClassForKey($request->get('viaResource')),
            'notable_id' => $request->get('viaResourceId')
        ]);

        if ($request->filled('tableGroup')) {
            $query->where('table_group_id', $request->get('tableGroup'));
        }

        $notes = $query->orderBy('position')->get(['id', 'note']);

        if ($notes->isEmpty()) {
            return null;
        }

        return $notes;
    }
}
