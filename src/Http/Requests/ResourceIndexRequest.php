<?php

namespace Pauldstar\NovaCompactUi\Http\Requests;

use App\Nova\Filters\TableGroupFilter;
use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Query\ApplyFilter;
use Laravel\Nova\Http\Requests\ResourceIndexRequest as NovaIndexRequest;

class ResourceIndexRequest extends NovaIndexRequest
{
    public function filters(): Collection
    {
        $filters = (new FilterDecoder($this->filters, $this->availableFilters()))->filters();

        if ($this->filled('tableGroup')) {
            $filters->push(
                new ApplyFilter(new TableGroupFilter(), $this->get('tableGroup'))
            );
        }

        return $filters;
    }
}
