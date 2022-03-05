<template>
    <div class="flex items-center p-3 border-b border-50">
        <div class="w-full flex items-center">
            <div class="flex mr-auto">
                <button
                    v-if="resource.authorizedToRestore && resource.softDeleted"
                    data-testid="open-restore-modal"
                    dusk="open-restore-modal-button"
                    @click.prevent="openRestoreModal"
                    class="btn btn-default btn-icon bg-success-light mr-3"
                    title="Restore"
                >
                    <icon type="restore" class="text-80"/>
                </button>

                <button
                    v-if="resource.authorizedToDelete && !resource.softDeleted"
                    data-testid="open-delete-modal"
                    dusk="open-delete-modal-button"
                    @click.prevent="openDeleteModal"
                    class="btn btn-default btn-icon bg-warning-light mr-3"
                    title="Delete"
                >
                    <icon type="delete" class="text-80"/>
                </button>

                <button
                    v-if="resource.authorizedToForceDelete"
                    data-testid="open-force-delete-modal"
                    dusk="open-force-delete-modal-button"
                    @click.prevent="openForceDeleteModal"
                    class="btn btn-default btn-icon bg-danger-light mr-3"
                    title="Force Delete"
                >
                    <icon type="force-delete" class="text-80"/>
                </button>

                <portal to="modals">
                    <delete-resource-modal
                        v-if="deleteModalOpen"
                        @confirm="confirmDelete"
                        @close="closeDeleteModal"
                        mode="delete"
                    />

                    <restore-resource-modal
                        v-if="restoreModalOpen"
                        @confirm="confirmRestore"
                        @close="closeRestoreModal"
                    />

                    <delete-resource-modal
                        v-if="forceDeleteModalOpen"
                        @confirm="confirmForceDelete"
                        @close="closeForceDeleteModal"
                        mode="force delete"
                    />
    
                    <confirm-action-modal
                        v-if="openConfirmActionModal"
                        class="text-left"
                        :working="working"
                        :selected-resources="selectedResources"
                        :resource-name="resourceName"
                        :action="selectedAction"
                        :errors="errors"
                        @confirm="executeAction"
                        @close="skipConfirmationModal"
                    />
                </portal>
            </div>

            <!-- Actions -->
            <action-selector
                v-if="resource"
                ref="actionSelector"
                :resource-name="resourceName"
                :actions="actions"
                :pivot-actions="{ actions: [] }"
                :selected-resources="selectedResources"
                :query-string="{
                    currentSearch,
                    encodedFilters,
                    currentTrashed,
                    viaResource,
                    viaResourceId,
                    viaRelationship,
                }"
                @actionExecuted="actionExecuted"
            />
        </div>
    </div>
</template>

<script>
import {Deletable, Errors} from 'laravel-nova';
import FormActions from "../../mixins/FormActions";

export default {
    props: {
        resource: {type: Object},
        resourceName: {type: String},
        resourceId: {type: [Number, String]},
        viaResource: {type: String},
        viaResourceId: {type: [Number, String]},
        viaRelationship: {type: String},
        disableUnsavedPrompt: {type: Function},
    },

    mixins: [Deletable, FormActions],

    data: _ => ({
        actions: [],
        actionValidationErrors: new Errors(),
        deleteModalOpen: false,
        restoreModalOpen: false,
        forceDeleteModalOpen: false,
    }),

    async mounted() {
        await this.getActions();

        Nova.$on('run-action-bar', this.triggerAction);
    },

    computed: {
        // @copy Detail
        currentSearch() {
            return ''
        },

        // @copy Detail
        encodedFilters() {
            return []
        },

        // @copy Detail
        currentTrashed() {
            return ''
        },

        // @copy Detail
        isActionDetail() {
            return this.resourceName === 'action-events'
        },
    },

    methods: {
        triggerAction(actionName) {
            let event = {target: {value: actionName}};
            this.$refs.actionSelector.handleSelectionChange(event);
        },

        // @copy Detail
        async getActions() {
            if (this.resource.softDeleted) return;

            this.actions = [];

            return Nova.request()
                .get('/nova-api/' + this.resourceName + '/actions', {
                    params: {
                        editing: true,
                        editMode: 'create',
                        resourceId: this.resourceId,
                    },
                }).then(response => {
                    this.actions = _.filter(response.data.actions, a => a.showOnDetail)
                })
        },

        // @copy Detail
        async confirmDelete() {
            this.deleteResources([this.resource], () => {
                Nova.success(
                    this.__(':resource is deleted!', {resource: this.resource.title})
                )

                if (!this.resource.softDeletes) {
                    this.disableUnsavedPrompt();

                    this.$router.push({
                        name: 'index',
                        params: {resourceName: this.resourceName},
                    })

                    return
                }

                this.closeDeleteModal()
                this.$emit('confirm-delete');
            })
        },

        async confirmRestore() {
            this.restoreResources([this.resource], () => {
                Nova.success(
                    this.__(':resource is restored!', {resource: this.resource.title})
                )

                this.closeRestoreModal()
                this.$emit('confirm-restore');
            })
        },

        async confirmForceDelete() {
            this.forceDeleteResources([this.resource], () => {
                Nova.success(
                    this.__(':resource is deleted!', {resource: this.resource.title})
                )

                this.disableUnsavedPrompt();

                this.$router.push({
                    name: 'index',
                    params: {resourceName: this.resourceName},
                })
            })
        },

        // @copy Detail
        async actionExecuted() {
            await this.getActions()
            this.$emit('action-executed');
        },

        // @copy Detail
        openRestoreModal() {
            this.restoreModalOpen = true
        },

        // @copy Detail
        closeRestoreModal() {
            this.restoreModalOpen = false
        },

        // @copy Detail
        openForceDeleteModal() {
            this.forceDeleteModalOpen = true
        },

        // @copy Detail
        closeForceDeleteModal() {
            this.forceDeleteModalOpen = false
        },

        // @copy Detail
        openDeleteModal() {
            this.deleteModalOpen = true
        },

        // @copy Detail
        closeDeleteModal() {
            this.deleteModalOpen = false
        },
    }
}
</script>
