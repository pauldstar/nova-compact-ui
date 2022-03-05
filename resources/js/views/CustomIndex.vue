<template>
    <div>
        <loading-view
            :loading="initialLoading"
            :dusk="resourceName + '-index-component'"
        >
            <div v-if="shouldShowCards">
                <cards
                    v-if="smallCards.length > 0"
                    :cards="smallCards"
                    class="mb-3"
                    :resource-name="resourceName"
                />

                <cards
                    v-if="largeCards.length > 0"
                    :cards="largeCards"
                    size="large"
                    :resource-name="resourceName"
                />
            </div>

            <custom-header
                :headingTitle="resourceName.replace(/-/g, ' ')"
                :viaRelationship="viaRelationship"
                :resource-name="resourceName"
            />

            <card>
                <div class="flex items-center py-3 border-b border-50">
                    <div class="flex items-center px-3">
                        <!-- Create / Attach Button -->
                        <create-resource-button
                            :field-name="fieldName"
                            :singular-name="singularName"
                            :show-modal="isTabulatedField || isShownViaModal"
                            :resource-name="resourceName"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            :relationship-type="relationshipType"
                            :authorized-to-create="shouldShowCreateButton"
                            :authorized-to-relate="authorizedToRelate"
                            class="flex-no-shrink"
                        />

                        <create-resource-button
                            v-if="isNotable"
                            :field-name="fieldName"
                            singular-name="Note"
                            :show-modal="true"
                            resource-name="table-notes"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            :relationship-type="relationshipType"
                            :authorized-to-create="shouldShowCreateButton"
                            :authorized-to-relate="authorizedToRelate"
                            class="flex-no-shrink ml-3"
                        />

                        <button
                            v-if="isImportable && shouldShowCreateButton && authorizedToRelate"
                            class="btn btn-default text-white bg-success ml-3"
                            :authorized-to-create="shouldShowCreateButton"
                            @click.prevent="importTemplateAction"
                        >Import From Template
                        </button>
                    </div>

                    <div
                        v-if="resourceInformation.searchable && !viaHasOne"
                        class="flex items-center relative h-9 px-3 ml-auto"
                    >
                        <!-- Action Selector -->
                        <action-selector
                            ref="actionSelector"
                            v-if="! isShownViaModal"
                            :resource-name="resourceName"
                            :actions="actions"
                            :pivot-actions="pivotActions"
                            :pivot-name="pivotName"
                            :query-string="{
                                currentSearch,
                                encodedFilters,
                                currentTrashed,
                                viaResource,
                                viaResourceId,
                                viaRelationship,
                            }"
                            :selected-resources="selectedResourcesForActionSelector"
                            @actionExecuted="checkAction"
                        />

                        <!-- Lenses -->
                        <dropdown
                            class="bg-30 hover:bg-40 mr-3 rounded"
                            v-if="lenses.length > 0 && ! isShownViaModal"
                        >
                            <dropdown-trigger class="px-3">
                                <h3
                                    slot="default"
                                    class="flex items-center font-normal text-base text-90 h-9"
                                >
                                    {{ __('Lens') }}
                                </h3>
                            </dropdown-trigger>

                            <dropdown-menu slot="menu" width="240" direction="rtl">
                                <lens-selector :resource-name="resourceName" :lenses="lenses"/>
                            </dropdown-menu>
                        </dropdown>

                        <!-- Filters -->
                        <filter-menu
                            v-if="! isShownViaModal"
                            class="ml-3"
                            :resource-name="resourceName"
                            :soft-deletes="softDeletes"
                            :via-resource="viaResource"
                            :via-has-one="viaHasOne"
                            :trashed="trashed"
                            :per-page="perPage"
                            :per-page-options="perPageOptions"
                            @clear-selected-filters="clearSelectedFilters"
                            @filter-changed="filterChanged"
                            @trashed-changed="trashedChanged"
                            @per-page-changed="updatePerPageChanged"
                        />

                        <delete-menu
                            v-if="shouldShowDeleteMenu"
                            dusk="delete-menu"
                            :soft-deletes="softDeletes"
                            :resources="resources"
                            :selected-resources="selectedResources"
                            :via-many-to-many="viaManyToMany"
                            :all-matching-resource-count="allMatchingResourceCount"
                            :all-matching-selected="selectAllMatchingChecked"
                            :authorized-to-delete-selected-resources="
              authorizedToDeleteSelectedResources
            "
                            :authorized-to-force-delete-selected-resources="
              authorizedToForceDeleteSelectedResources
            "
                            :authorized-to-delete-any-resources="authorizedToDeleteAnyResources"
                            :authorized-to-force-delete-any-resources="
              authorizedToForceDeleteAnyResources
            "
                            :authorized-to-restore-selected-resources="
              authorizedToRestoreSelectedResources
            "
                            :authorized-to-restore-any-resources="
              authorizedToRestoreAnyResources
            "
                            @deleteSelected="deleteSelectedResources"
                            @deleteAllMatching="deleteAllMatchingResources"
                            @forceDeleteSelected="forceDeleteSelectedResources"
                            @forceDeleteAllMatching="forceDeleteAllMatchingResources"
                            @restoreSelected="restoreSelectedResources"
                            @restoreAllMatching="restoreAllMatchingResources"
                            @close="deleteModalOpen = false"
                        />

                        <!-- Search -->
                        <div class="ml-3">
                            <icon type="search" class="absolute search-icon-center ml-3 text-70"/>

                            <input
                                data-testid="search-input"
                                dusk="search"
                                class="appearance-none form-search w-search pl-search shadow"
                                :placeholder="__('Search')"
                                type="search"
                                v-model="search"
                                @keydown.stop="performSearch"
                                @search="performSearch"
                                style="background: #f4f7fa;"
                            />
                        </div>
                    </div>
                </div>

                <loading-view :loading="loading">
                    <div
                        v-if="resources.length"
                        class="overflow-hidden overflow-x-auto relative">
                        <!-- Resource Table -->
                        <custom-resource-table
                            :mode="mode"
                            :fieldName="fieldName"
                            :draggable="isDraggable"
                            :notable="isNotable"
                            :table-group-id="tableGroupId"
                            :authorized-to-relate="authorizedToRelate"
                            :resource-name="resourceName"
                            :resources="resources"
                            :notes-with-resources="notes"
                            :singular-name="singularName"
                            :selected-resources="selectedResources"
                            :selected-resource-ids="selectedResourceIds"
                            :actions-are-available="allActions.length > 0"
                            :should-show-checkboxes="shouldShowCheckBoxes"
                            :show-modal="isTabulatedField"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            :relationship-type="relationshipType"
                            :update-selection-status="updateSelectionStatus"
                            :order-by="tableOrderByParameter"
                            :order-by-direction="tableOrderByDirectionParameter"
                            @order="orderByField"
                            @reset-order-by="resetOrderBy"
                            @delete="_deleteResources"
                            @restore="restoreResources"
                            @actionExecuted="getResources"
                            @sort-note="$emit('sort-note', $event)"
                            @sort-resource="$emit('sort-resource', $event)"
                            ref="resourceTable"
                        >
                            <div class="flex items-center">
                                <div v-if="shouldShowCheckBoxes">
                                    <!-- Select All -->
                                    <dropdown
                                        dusk="select-all-dropdown"
                                        placement="bottom-end"
                                        class="-mx-2"
                                    >
                                        <dropdown-trigger class="px-2">
                                            <fake-checkbox :checked="selectAllChecked"/>
                                        </dropdown-trigger>

                                        <dropdown-menu slot="menu" direction="ltr" width="250">
                                            <div class="p-4">
                                                <ul class="list-reset">
                                                    <li class="flex items-center mb-4">
                                                        <checkbox-with-label
                                                            :checked="selectAllChecked"
                                                            @input="toggleSelectAll"
                                                        >
                                                            {{ __('Select All') }}
                                                        </checkbox-with-label>
                                                    </li>
                                                    <li class="flex items-center">
                                                        <checkbox-with-label
                                                            dusk="select-all-matching-button"
                                                            :checked="selectAllMatchingChecked"
                                                            @input="toggleSelectAllMatching"
                                                        >
                                                            <template>
                                                                <span class="mr-1">
                                                                    {{ __('Select All Matching') }} ({{
                                                                        allMatchingResourceCount
                                                                    }})
                                                                </span>
                                                            </template>
                                                        </checkbox-with-label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </dropdown-menu>
                                    </dropdown>
                                </div>
                            </div>
                        </custom-resource-table>
                    </div>

                    <div v-else class="flex justify-center items-center px-6 py-8">
                        <div class="text-center">
                            <svg
                                class="mb-3"
                                xmlns="http://www.w3.org/2000/svg"
                                width="65"
                                height="51"
                                viewBox="0 0 65 51"
                            >
                                <path
                                    fill="#A8B9C5"
                                    d="M56 40h2c.552285 0 1 .447715 1 1s-.447715 1-1 1h-2v2c0 .552285-.447715 1-1 1s-1-.447715-1-1v-2h-2c-.552285 0-1-.447715-1-1s.447715-1 1-1h2v-2c0-.552285.447715-1 1-1s1 .447715 1 1v2zm-5.364125-8H38v8h7.049375c.350333-3.528515 2.534789-6.517471 5.5865-8zm-5.5865 10H6c-3.313708 0-6-2.686292-6-6V6c0-3.313708 2.686292-6 6-6h44c3.313708 0 6 2.686292 6 6v25.049375C61.053323 31.5511 65 35.814652 65 41c0 5.522847-4.477153 10-10 10-5.185348 0-9.4489-3.946677-9.950625-9zM20 30h16v-8H20v8zm0 2v8h16v-8H20zm34-2v-8H38v8h16zM2 30h16v-8H2v8zm0 2v4c0 2.209139 1.790861 4 4 4h12v-8H2zm18-12h16v-8H20v8zm34 0v-8H38v8h16zM2 20h16v-8H2v8zm52-10V6c0-2.209139-1.790861-4-4-4H6C3.790861 2 2 3.790861 2 6v4h52zm1 39c4.418278 0 8-3.581722 8-8s-3.581722-8-8-8-8 3.581722-8 8 3.581722 8 8 8z"
                                />
                            </svg>

                            <h3 class="text-base text-80 font-normal mb-6">
                                {{
                                    __('No :resource matched the given criteria.', {
                                        resource: singularName.toLowerCase(),
                                    })
                                }}
                            </h3>

                            <create-resource-button
                                classes="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline"
                                :field-name="fieldName"
                                :show-modal="isTabulatedField"
                                :singular-name="singularName"
                                :resource-name="resourceName"
                                :table-group-Id="tableGroupId"
                                :via-resource="viaResource"
                                :via-resource-id="viaResourceId"
                                :via-relationship="viaRelationship"
                                :relationship-type="relationshipType"
                                :authorized-to-create="shouldShowCreateButton"
                                :authorized-to-relate="authorizedToRelate"
                            />
                        </div>
                    </div>

                    <!-- Pagination -->
                    <component
                        :is="paginationComponent"
                        v-if="shouldShowPagination"
                        :next="hasNextPage"
                        :previous="hasPreviousPage"
                        @load-more="loadMore"
                        @page="selectPage"
                        :pages="totalPages"
                        :page="currentPage"
                        :per-page="perPage"
                        :resource-count-label="resourceCountLabel"
                        :current-resource-count="resources.length"
                        :all-matching-resource-count="allMatchingResourceCount"
                    >
                        <span
                            v-if="resourceCountLabel"
                            class="text-80 px-4"
                            :class="{ 'ml-auto': paginationComponent === 'pagination-links' }"
                        >
                            {{ resourceCountLabel }}
                        </span>
                    </component>
                </loading-view>
            </card>
        </loading-view>
    </div>

</template>

<script>
import Index from '@nova/views/Index';
import {Deletable, Minimum} from 'laravel-nova';
import numbro from '@nova/util/numbro';

export default {
    extends: Index,
    mixins: [Deletable],

    props: {
        mode: String,
        initialSearch: String,
        tableGroupId: {type: [String, Number]},
        draggable: Boolean,
        canCreateViaModal: {type: Boolean, default: true}
    },

    metaInfo() {
        let updateTitle = this.shouldOverrideMeta
            && !['custom-edit', 'custom-create'].includes(this.$route.name);

        if (updateTitle) {
            return {
                title: this.resourceInformation.label,
            }
        }
    },

    data: _ => ({
        notes: null,
        tablePageParameter: 1,
        tableSearchParameter: null,
        tableOrderByParameter: '',
        tableOrderByDirectionParameter: null
    }),

    mounted() {
        Nova.$on(this.crud, this.getResources);
    },

    destroyed() {
        Nova.$off(this.crud, this.getResources);
    },

    computed: {
        fieldName() {
            return this.field ? this.field.name : '';
        },

        isShownViaModal() {
            return this.mode === 'modal';
        },

        shouldShowCreateButton() {
            let canCreate = this.authorizedToCreate && !this.resourceIsFull;

            return this.isShownViaModal
                ? canCreate && this.canCreateViaModal
                : canCreate;
        },

        // @override Index
        shouldShowDeleteMenu() {
            return (
                Boolean(this.selectedResources.length > 0)
                && this.canShowDeleteMenu
                && !this.isShownViaModal
            )
        },

        // @override Index
        shouldShowCheckBoxes() {
            return (
                Boolean(this.hasResources && !this.viaHasOne) &&
                Boolean(
                    this.actionsAreAvailable ||
                    this.authorizedToDeleteAnyResources ||
                    this.canShowDeleteMenu
                ) && !this.isShownViaModal
            )
        },

        isIndex() {
            return this.$route.name === 'custom-index';
        },

        isNotable() {
            return this.field && this.field.notable;
        },

        isDraggable() {
            return this.draggable && this.field.draggable;
        },

        isImportable() {
            return this.field && this.field.importable;
        },

        isTabulatedField() {
            return this.field && this.field.tabulated;
        },

        isSoftDeleted() {
            let viaResource = this.field && this.field.viaResource;
            return viaResource && viaResource.softDeleted;
        },

        // @override Paginatable
        currentPage() {
            let page = this.isIndex
                ? this.$route.query[this.pageParameter] || 1
                : this.tablePageParameter;

            return parseInt(page);
        },

        // @override Index
        currentSearch() {
            return this.isIndex
                ? this.$route.query[this.searchParameter] || ''
                : this.tableSearchParameter;
        },

        currentOrderBy() {
            return this.isIndex
                ? this.$route.query[this.orderByParameter] || ''
                : this.tableOrderByParameter;
        },

        // @override Index
        currentOrderByDirection() {
            return this.isIndex
                ? this.$route.query[this.orderByDirectionParameter] || null
                : this.tableOrderByDirectionParameter;
        },

        crud() {
            return [
                'resource-created',
                'resource-updated'
            ].map(e => `${this.fieldName}:${e}`);
        },

        viaResourceRelationship() {
            return this.relationshipType === 'hasMany' && this.viaResourceId === 0
                ? this.field.belongsToRelationship : undefined;
        },

        // @override Index
        resourceCountLabel() {
            const first = this.perPage * (this.currentPage - 1)

            return (
                this.resources.length &&
                `${this.formatNumber(first + 1)}-${this.formatNumber(
                    first + this.resources.length
                )} ${this.__('of')} ${this.formatNumber(this.allMatchingResourceCount)}`
            )
        },

        // @override Index
        resourceRequestQueryString() {
            return {
                search: this.currentSearch,
                filters: this.encodedFilters,
                orderBy: this.currentOrderBy,
                orderByDirection: this.currentOrderByDirection,
                perPage: this.currentPerPage,
                trashed: this.currentTrashed,
                page: this.currentPage,
                tableGroup: this.tableGroupId,
                notable: this.isNotable,
                draggable: this.isDraggable,
                viaResource: this.viaResource,
                viaResourceId: this.viaResourceId,
                viaSoftDeletedResource: this.isSoftDeleted,
                viaRelationship: this.viaRelationship,
                viaResourceRelationship: this.viaResourceRelationship,
                relationshipType: this.relationshipType,
                // to enable default values for action fields
                editing: true,
                editMode: 'create'
            }
        },
    },

    methods: {
        // @override Index
        getResources() {
            this.loading = true

            this.$nextTick(() => {
                this.clearResourceSelections()

                let prefix = '/fifteen-group/nova-compact-reactive-ui/';

                return Minimum(
                    Nova.request().get(prefix + this.resourceName, {
                        params: this.resourceRequestQueryString
                    }),
                    300
                ).then(({data}) => {
                    this.resourceResponse = data
                    this.resources = data.resources
                    this.notes = this.notesWithResources(data.tableNotes);
                    this.softDeletes = data.softDeletes
                    this.perPage = data.per_page
                    this.loading = false
                    this.allMatchingResourceCount = data.total

                    let shouldUpdateField = this.isTabulatedField
                        && this.field.value.length !== this.resources.length;

                    if (shouldUpdateField) this.field.value = this.resources;
                })
            })
        },

        /**
         * Get the actions available for the current resource.
         */
        getActions() {
            if (this.isSoftDeleted) return;

            this.actions = []
            this.pivotActions = null

            return Nova.request()
                .get(`/nova-api/${this.resourceName}/actions`, {
                    params: {
                        viaResource: this.viaResource,
                        viaResourceId: this.viaResourceId,
                        viaRelationship: this.viaRelationship,
                        relationshipType: this.relationshipType,
                    },
                })
                .then(response => {
                    this.actions = _.filter(response.data.actions, a => a.showOnIndex)
                    this.pivotActions = response.data.pivotActions
                })
        },

        notesWithResources(data) {
            if (!this.field || !data || !this.resources.length) return null;

            let noteMap = {};

            data.forEach(item => {
                // spoofing table-notes like a resource
                noteMap[item.id] = {
                    text: item.note,
                    id: {value: item.id},
                    resources: [],
                    resourceName: 'table-notes',
                    fieldCount: this.resources[0].fields.length,
                    authorizedToView: true,
                    authorizedToUpdate: true,
                    authorizedToDelete: true
                };
            })

            let noteId;

            this.resources.forEach(resource => {
                noteId = resource.tableNoteId;
                if (noteId) noteMap[noteId].resources.push(resource);
            });

            let notes = [];

            // return to original order
            data.forEach(item => notes.push(noteMap[item.id]));

            return notes;
        },

        // @override Deletable
        _deleteResources(resources, callback = null) {
            if (this.viaManyToMany) {
                return this.detachResources(resources)
            }

            let resourceName = resources[0].resourceName || this.resourceName;

            return Nova.request({
                url: '/nova-api/' + resourceName,
                method: 'delete',
                params: {
                    ...(resourceName === 'table-notes' ? [] : this.queryString),
                    ...{resources: _.map(resources, resource => resource.id.value)},
                },
            }).then(
                callback
                    ? callback
                    : () => {
                        this.deleteModalOpen = false
                        this.getResources()
                    }
            ).then(this.broadcastDelete);
        },

        broadcastDelete() {
            this.fieldName && Nova.$emit(`${this.fieldName}:resource-deleted`);
        },

        importTemplateAction() {
            let resource = {id: {value: this.viaResourceId}},
                event = {target: {value: 'import-quote-template'}};

            this.selectedResources.push(resource);
            this.$refs.actionSelector.handleSelectionChange(event);
        },

        checkAction() {
            let action = this.$refs.actionSelector.selectedActionKey;

            if (action === 'import-quote-template') {
                Nova.$emit('refresh-sales-order-panel');
            } else {
                this.getResources();
            }
        },

        // @override Index
        selectPage(page) {
            if (this.isIndex)
                this.updateQueryString({[this.pageParameter]: page});
            else this.tablePageParameter = page;
        },

        initializeSearchFromQueryString() {
            this.tableSearchParameter = this.initialSearch;
            this.search = this.currentSearch;
        },

        // @override Index
        performSearch(event) {
            this.debouncer(() => {
                // Only search if we're not tabbing into the field
                if (event.which !== 9) {
                    if (this.isIndex) {
                        this.updateQueryString({
                            [this.pageParameter]: 1,
                            [this.searchParameter]: this.search,
                        });
                    } else {
                        this.tablePageParameter = 1;
                        this.tableSearchParameter = this.search;
                        console.log('searching...');
                    }
                }
            })
        },

        orderByField(field) {
            let direction = this.currentOrderByDirection === 'asc' ? 'desc' : 'asc'

            if (this.currentOrderBy !== field.sortableUriKey) direction = 'asc'

            if (this.isIndex) {
                this.updateQueryString({
                    [this.orderByParameter]: field.sortableUriKey,
                    [this.orderByDirectionParameter]: direction,
                })
            } else {
                this.tableOrderByParameter = field.sortableUriKey;
                this.tableOrderByDirectionParameter = direction;
            }
        },

        resetOrderBy(field) {
            if (this.isIndex) {
                this.updateQueryString({
                    [this.orderByParameter]: field.sortableUriKey,
                    [this.orderByDirectionParameter]: null,
                })
            } else {
                this.tableOrderByParameter = field.sortableUriKey;
                this.tableOrderByDirectionParameter = null;
            }
        },

        // @override Nova
        formatNumber(number, format) {
            const num = numbro(number)

            if (format !== undefined) {
                return num.format(format)
            }

            return num.format()
        },

        // @override Index
        getAuthorizationToRelate() {
            if (!this.authorizedToCreate
                && this.relationshipType !== 'belongsToMany'
                && this.relationshipType !== 'morphToMany'
            ) {
                return
            }

            if (!this.viaResource || this.viaResourceId === 0) {
                return (this.authorizedToRelate = true)
            }

            return Nova.request()
                .get(
                    '/nova-api/' + this.resourceName + '/relate-authorization' +
                    '?viaResource=' + this.viaResource + '&viaResourceId=' +
                    this.viaResourceId + '&viaRelationship=' + this.viaRelationship +
                    '&relationshipType=' + this.relationshipType
                )
                .then(response => this.authorizedToRelate = response.data.authorized)
        }
    }
}
</script>
