<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div class="flex items-center">
                <search-input
                    v-if="isSearchable && !isLocked && !isReadonly"
                    ref="searchInput"
                    :data-testid="`${field.resourceName}-search-input`"
                    @input="performTextSearch"
                    @clear="clearSelection"
                    @selected="selectResource"
                    :error="hasError"
                    :value="selectedResource"
                    :data="availableResources"
                    :clearable="field.nullable"
                    trackBy="value"
                    searchBy="display"
                    class="w-full"
                >
                    <div slot="default" v-if="selectedResource" class="flex items-center">
                        <div v-if="selectedResource.avatar" class="mr-3">
                            <img
                                :src="selectedResource.avatar"
                                class="w-8 h-8 rounded-full block"
                            />
                        </div>

                        {{ selectedResource.display }}
                    </div>

                    <div
                        slot="option"
                        slot-scope="{ option, selected }"
                        class="flex items-center"
                    >
                        <div v-if="option.avatar" class="mr-3">
                            <img :src="option.avatar" class="w-8 h-8 rounded-full block"/>
                        </div>

                        {{ option.display }}
                    </div>
                </search-input>

                <select-control
                    v-if="!isSearchable || isLocked || isReadonly"
                    class="form-control form-select w-full"
                    :class="{ 'border-danger': hasError }"
                    :data-testid="`${field.resourceName}-select`"
                    :dusk="field.attribute"
                    @change="selectResourceFromSelectControl"
                    :disabled="isLocked || isReadonly"
                    :options="availableResources"
                    :value="selectedResourceId"
                    :selected="selectedResourceId"
                    label="display"
                >
                    <option value="" selected>{{ placeholder }}</option>
                </select-control>

                <create-relation-button
                    v-if="canShowNewRelationModal"
                    @click="openRelationModal"
                    class="ml-1"
                />
            </div>

            <portal to="modals" transition="fade-transition">
                <create-relation-modal
                    v-if="relationModalOpen && canShowNewRelationModal"
                    @set-resource="handleSetResource"
                    @cancelled-create="closeRelationModal"
                    :resource-name="field.resourceName"
                    :via-relationship="dependsOnRelationship"
                    :via-resource="dependsOnResource"
                    :via-resource-id="dependsOnResourceId"
                    width="800"
                />
            </portal>

            <stack-modal
                :show="tableModalOpen"
                @close="closeTableModal"
            >
                <resource-index-modal
                    v-if="tableModalOpen"
                    :can-create-via-modal="canShowNewRelationModal"
                    @set-resource="handleSetResource"
                    @cancelled-search="closeTableModal"
                    :field="field"
                    :resource-name="field.resourceName"
                    :via-relationship="dependsOnRelationship"
                    :via-resource="dependsOnResource"
                    :via-resource-id="dependsOnResourceId"
                    :search="search"
                />
            </stack-modal>

            <!-- Trashed State -->
            <div v-if="shouldShowTrashed" class="mt-1">
                <checkbox-with-label
                    :dusk="`${field.resourceName}-with-trashed-checkbox`"
                    :checked="withTrashed"
                    @input="toggleWithTrashed"
                >
                    {{ __('With Trashed') }}
                </checkbox-with-label>
            </div>
        </template>
    </default-field>
</template>

<script>
import ReactiveField from '../../mixins/ReactiveField';
import BelongsToField from "@nova/components/Form/BelongsToField";
import ResourceIndexModal from "@ui/components/modal/ResourceIndexModal";
import StackModal from '@innologica/vue-stackable-modal';

export default {
    extends: BelongsToField,
    mixins: [ReactiveField],
    components: {ResourceIndexModal, StackModal},

    data: _ => ({
        dependsOnValue: null,
        tableModalOpen: false
    }),

    created() {
        this.ignoreOnLoadEmit = false;
    },

    mounted() {
        Nova.$on(
            `${this.field.name}:create-resource`,
            this.triggerCreateResource
        );

        Nova.$on(
            `${this.field.name}:select-resource`,
            this.selectResourceFromTable
        );

        this.watches('selectedResourceId');
    },

    computed: {
        // @override BelongToField
        isSearchable() {
            return this.field.searchable || this.field.searchableTable;
        },

        // @override BelongToField
        shouldSelectInitialResource() {
            return Boolean(
                this.editingExistingResource || this.creatingViaRelatedResource || this.dependsOnValue
            )
        },

        // @override BelongToField
        queryParams() {
            return {
                params: {
                    current: this.selectedResourceId,
                    first: this.initializingWithExistingResource,
                    search: this.search,
                    withTrashed: this.withTrashed,
                    viaResource: this.viaResource,
                    viaResourceId: this.viaResourceId,
                    viaRelationship: this.viaRelationship,
                    dependsOnValue: this.dependsOnValue || this.field.dependsOnResourceId
                },
            }
        },

        dependsOnResourceId() {
            return this.dependsOnValue
                || this.field.dependsOnResourceId
                || (this.shownViaNewRelationModal ? '' : this.viaResourceId);
        },

        dependsOnResource() {
            return this.dependsOnResourceId
                ? this.field.dependsOnResource || this.viaResource
                : '';
        },

        dependsOnRelationship() {
            return this.dependsOnResourceId
                ? this.field.dependsOnRelationship || this.viaRelationship : '';
        }
    },

    methods: {
        // override BelongsToField
        initializeComponent() {
            this.withTrashed = false

            this.selectedResourceId = this.field.value

            // If a user is editing an existing resource with this relation
            // we'll have a belongsToId on the field, and we should prefill
            // that resource in this field
            if (this.editingExistingResource) {
                this.initializingWithExistingResource = true
                this.selectedResourceId = this.field.belongsToId
            }

            this.field.fill = this.fill;

            // don't load available resources yet if this field depends
            // on a viaResource field for an on-load emit
            if (this.awaitingEmitOnLoad) {
                return;
            }

            // If the user is creating this resource via a related resource's index
            // page we'll have a viaResource and viaResourceId in the params and
            // should prefill the resource in this field with that information
            if (this.creatingViaRelatedResource) {
                this.initializingWithExistingResource = true
                this.selectedResourceId = this.viaResourceId
            }

            if (this.shouldSelectInitialResource && !this.isSearchable) {
                // If we should select the initial resource but the field is not
                // searchable we should load all of the available resources into the
                // field first and select the initial option.
                this.initializingWithExistingResource = false
                this.getAvailableResources().then(() => this.selectInitialResource())
            } else if (this.shouldSelectInitialResource && this.isSearchable) {
                // If we should select the initial resource and the field is
                // searchable, we won't load all the resources but we will select
                // the initial option.
                this.getAvailableResources().then(() => this.selectInitialResource())
            } else if (!this.shouldSelectInitialResource && !this.isSearchable) {
                // If we don't need to select an initial resource because the user
                // came to create a resource directly and there's no parent resource,
                // and the field is searchable we'll just load all of the resources.
                this.getAvailableResources()
            }

            // this.determineIfSoftDeletes()
        },

        openTableModal() {
            this.tableModalOpen = true;
        },

        closeTableModal() {
            this.tableModalOpen = false;
        },

        performTextSearch(search) {
            if (this.field.searchableTable) {
                const trimmedSearch = search.trim();

                if (trimmedSearch !== '') {
                    this.search = trimmedSearch;
                    this.$nextTick(() => this.$refs.searchInput.close())
                    return this.openTableModal();
                }
            }

            this.performSearch(search);
        },

        triggerCreateResource() {
            this.closeTableModal();
            this.openRelationModal();
        },

        // @override BelongsToField
        selectResource(resource) {
            this.selectedResource = resource;
            this.selectedResourceId = resource.value;
        },

        selectResourceFromTable(resource) {
            this.closeTableModal();
            this.selectResource(resource);
        },

        // @override BelongsToField
        clearSelection() {
            this.selectedResource = '';
            this.selectedResourceId = '';
            this.availableResources = [];

            if (this.field) {
                Nova.$emit(this.field.attribute + '-change', null)
            }
        },

        // @override Reactive
        async setValue(val) {
            let {dependsOnId, value} = val;

            if (dependsOnId === this.dependsOnValue) {
                return;
            }

            this.dependsOnValue = dependsOnId;

            if (this.shouldSelectInitialResource) {
                if (!this.isSearchable) {
                    this.initializingWithExistingResource = false
                }

                this.getAvailableResources().then(_ => {
                    this.selectedResourceId = value;
                    this.selectInitialResource();
                });
            }
        }
    }
}
</script>
