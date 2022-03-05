import CustomIndex from './views/CustomIndex';
import CustomUpdate from './views/CustomUpdate';
import CustomDetail from './views/CustomDetail';
import ViewDetail from './views/ViewDetail';
import UpdateDetail from './views/UpdateDetail';
import CustomAttach from './views/CustomAttach';
import CustomUpdateAttached from './views/CustomUpdateAttached';
import CustomCreate from './views/CustomCreate';

import './field';

Nova.booting((Vue, router) => {
    Vue.component('index-view', CustomIndex);
    Vue.component('detail-view', CustomDetail);
    Vue.component('view-detail', ViewDetail);
    Vue.component('update-detail', UpdateDetail);
    Vue.component('create-view', CustomCreate);
    Vue.component('edit-view', CustomUpdate);
    Vue.component('attach-view', CustomAttach);
    Vue.component('edit-attached-view', CustomUpdateAttached);
    Vue.component('resource-index', CustomIndex);
    Vue.component('create-form', require('./components/CustomCreateForm'));
    Vue.component('custom-header', require('./components/CustomHeader'));
    Vue.component('custom-resource-table', require('./components/index/CustomResourceTable'));

    Vue.component('form-morph-to-field', require('./components/form/CustomMorphToField'));
    Vue.component('index-id-field', require('@nova/components/Index/IdField'));

    Vue.component('panel', require('./components/detail/CustomDetailPanel'));
    Vue.component('panel-item', require('./components/detail/CustomPanelItem'));
    Vue.component('relationship-panel', require('./components/detail/CustomRelationshipPanel'));
    Vue.component('form-panel', require('./components/form/CustomFormPanel'));
    Vue.component('resource-table-row', require('./components/index/CustomResourceTableRow'));
    Vue.component('default-field', require('./components/form/CustomDefaultField'));
    Vue.component('value-metric', require('./components/metrics/CustomValueMetric'));
    Vue.component('card-wrapper', require('./components/CustomCardWrapper'));
    Vue.component('create-resource-button', require('./components/index/CustomCreateResourceButton'));
    Vue.component('confirm-action-modal', require('./components/modal/CustomConfirmActionModal'));
    Vue.component('create-relation-modal', require('./components/modal/CustomCreateRelationModal'));
    Vue.component('date-time-picker', require('./components/CustomDateTimePicker'));
    Vue.component('action-selector', require('./components/CustomActionSelector'));
    Vue.component('inline-action-selector', require('./components/index/CustomInlineActionSelector'));
    Vue.component('sortable-icon', require('./components/index/CustomSortableIcon'));

    // nova bug..for some reason these doesn't link to the default nova component
    Vue.component('custom-dashboard-header', require('./components/CustomDashboardHeader'));
    Vue.component('custom-create-header', require('@nova/components/CustomCreateHeader'));

    router.beforeEach(function (to, from, next) {
        let customComponent = null;

        let globalViews = [
            'index',
            'detail',
            'create',
            'edit',
            'attach',
            'edit-attached'
        ];

        if (globalViews.includes(to.name)) {
            customComponent = to.name + '-view';
        }

        if (customComponent && Vue.options.components[customComponent]) {
            next({
                name: 'custom-' + to.name,
                params: Object.assign({}, to.params, {component: customComponent}),
                query: to.query
            });
        } else {
            next();
        }
    });

    router.addRoutes([
        {
            name: 'custom-index',
            path: '/resources/:resourceName',
            component: CustomIndex,
            props: true
        },
        {
            name: 'custom-detail',
            path: '/resources/:resourceName/:resourceId',
            component: CustomDetail,
            props: function props(route) {
                return {
                    component: route.params.component,
                    resourceName: route.params.resourceName,
                    resourceId: route.params.resourceId,
                };
            }
        },
        {
            name: 'custom-create',
            path: '/resources/:resourceName/new',
            component: CustomCreate,
            props: route => {
                return {
                    resourceName: route.params.resourceName,
                    viaResource: route.query.viaResource || '',
                    viaResourceId: route.query.viaResourceId || '',
                    viaRelationship: route.query.viaRelationship || '',
                }
            }
        },
        {
            name: 'custom-edit',
            path: '/resources/:resourceName/:resourceId/edit',
            component: UpdateDetail,
            props: function props(route) {
                return {
                    component: route.params.component,
                    resourceName: route.params.resourceName,
                    resourceId: route.params.resourceId,
                    viaResource: route.query.viaResource || '',
                    viaResourceId: route.query.viaResourceId || '',
                    viaRelationship: route.query.viaRelationship || '',
                };
            }
        },
        {
            name: 'custom-attach',
            path: '/resources/:resourceName/:resourceId/attach/:relatedResourceName',
            component: CustomAttach,
            props: function props(route) {
                return {
                    component: route.params.component,
                    resourceName: route.params.resourceName,
                    resourceId: route.params.resourceId,
                    relatedResourceName: route.params.relatedResourceName,
                    viaRelationship: route.query.viaRelationship,
                    polymorphic: route.query.polymorphic === '1',
                };
            }
        },
        {
            name: 'custom-edit-attached',
            path: '/resources/:resourceName/:resourceId/edit-attached/:relatedResourceName/:relatedResourceId',
            component: CustomUpdateAttached,
            props: function props(route) {
                return {
                    component: route.params.component,
                    resourceName: route.params.resourceName,
                    resourceId: route.params.resourceId,
                    relatedResourceName: route.params.relatedResourceName,
                    relatedResourceId: route.params.relatedResourceId,
                    viaRelationship: route.query.viaRelationship,
                };
            }
        }
    ]);
});
