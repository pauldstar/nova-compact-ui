<?php

namespace Pauldstar\NovaCompactUi\Nova;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 25;

    /**
     *  If this resource can be partially saved
     *
     * @var bool
     */
    protected static bool $draftable = false;

    /**
     * Abbreviated label for display
     * e.g. PurchaseOrderItems = POI
     *
     * @return string
     */
    public static function labelAbbr()
    {
        $words = explode(' ', self::label());
        $acronym = '';

        foreach ($words as $w) {
            $acronym .= $w[0];
        }

        return $acronym;
    }

    /**
     * Group Icons (Material Icons)
     * From https://material.io/resources/icons/?style=baseline
     *
     * @param string $group
     *
     * @return string
     */
    public static function groupIcon(string $group): string
    {
        return [
                'Companies'      => 'business',
                'Finance'        => 'request_quote',
                'Marketing'      => 'rss_feed',
                'Procurement'    => 'shopping_cart',
                'Project'        => 'calendar_today',
                'Sales'          => 'shop_two',
                'Service Desk'   => 'local_phone',
                'System'         => 'settings',
                'Time & Expense' => 'access_time',
            ][$group] ?? '';
    }

    /**
     * Optional
     * Use if you want to restrict soft deletes to admin only
     *
     * @return mixed|bool
     */
    public static function softDeletes()
    {
        if (!Auth::user()->isAdmin()) {
            return false;
        }

        if (isset(static::$softDeletes[static::$model])) {
            return static::$softDeletes[static::$model];
        }

        return static::$softDeletes[static::$model] = in_array(
            SoftDeletes::class, class_uses_recursive(static::newModel())
        );
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $showDrafts = $request->viaResourceId === '0' && static::$draftable;

        return $showDrafts
            ? $query->doesntHave($request->viaResourceRelationship)
            : $query;
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->select('id', 'name');
    }

    protected function breadcrumbResourceLabel()
    {
        return $this->label();
    }

    protected function breadcrumbResourceTitle()
    {
        return $this->title();
    }

    protected function serializeWithId(Collection $fields)
    {
        $parent = parent::serializeWithId($fields);

        return array_merge($parent, [
            'label' => $this->breadcrumbResourceLabel(),
            'title' => $this->breadcrumbResourceTitle(),
        ]);
    }
}
