<template>
    <loading-view :loading="loading">
        <form
            v-if="panels"
            @submit="submitViaUpdateResource"
            autocomplete="off"
            ref="form"
        >
            <custom-header
                :heading-title="$metaInfo && $metaInfo.title"
                :mode="mode"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
            />

            <form-panel
                v-for="panel in panelsWithFields"
                @update-last-retrieved-at-timestamp="updateLastRetrievedAtTimestamp"
                :panel="panel"
                :name="panel.name"
                :key="panel.name"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :fields="panel.fields"
                :mode="mode"
                class="mb-8"
                :validation-errors="validationErrors"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
            />

            <!-- Update Button -->
            <div class="flex items-center">
                <cancel-button @click="handleCancelUpdate"/>

                <progress-button
                    dusk="update-button"
                    type="submit"
                    :disabled="isWorking"
                    :processing="wasSubmittedViaUpdateResource"
                >
                    {{ updateButtonLabel }}
                </progress-button>
            </div>
        </form>

        <portal to="modals">
            <unsaved-changes-modal
                v-if="openUnsavedChangesModal"
                @confirm="confirmExit"
                @close="cancelExit"
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
    </loading-view>
</template>

<script>
    import Update from '@nova/views/Update';
    import ErrorNotification from '../mixins/ErrorNotification';
    import UnsavedChanges from '../mixins/UnsavedChanges';
    import UnsavedChangesModal from '../components/modal/UnsavedChangesModal';
    import FormActions from '../mixins/FormActions';
    import ReactiveFormView from '../mixins/ReactiveFormView';

    export default {
        extends: Update,
        mixins: [UnsavedChanges, FormActions, ReactiveFormView, ErrorNotification],
        components: {UnsavedChangesModal},

        props: {
            mode: {type: String, default: 'form'},
            fieldName: {type: String, default: ''},
            tableGroupId: {type: Number}
        },

        computed: {
            // @override Update
            updateResourceFormData() {
                return _.tap(new FormData(), formData => {
                    _(this.fields).each(field => {
                        field.tabulated || field.fill(formData)
                    })

                    if (this.tableGroupId) {
                        formData.get('tableGroup') || formData.append('tableGroup', this.tableGroupId)
                    }

                    formData.append('_method', 'PUT')
                    formData.append('_retrieved_at', this.lastRetrievedAt)
                })
            },
        },

        methods: {
            handleCancelUpdate() {
                if (this.mode === 'form') {
                    this.$router.back();
                } else if (this.mode === 'modal') {
                    this.$emit('cancelled-create');
                }
            },

            // @override Update
            async updateResource() {
                this.isWorking = true;

                if (this.$refs.form.reportValidity()) {
                    try {
                        let {
                            data: {redirect},
                        } = await this.updateRequest();

                        Nova.success(
                            this.__('The :resource was updated!', {
                                resource: this.resourceInformation.singularLabel.toLowerCase(),
                            })
                        );

                        await this.updateLastRetrievedAtTimestamp();

                        // redirects to parent resource if created via resource
                        if (this.viaResource) {
                            redirect = `/resources/${this.viaResource}/${this.viaResourceId}`;
                        }

                        if (this.mode === 'form') {
                            this.runActions(redirect, this.resourceId);
                        } else if (this.mode === 'modal') {
                            Nova.$emit(`${this.fieldName}:resource-updated`)
                        }
                    } catch (error) {
                        this.submittedViaUpdateResource = false;

                        if (error.response.status === 422) {
                            this.showErrors(error.response.data.errors);
                        }

                        if (error.response.status === 409) {
                            Nova.error(
                                this.__(
                                    'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                                )
                            )
                        }
                    }
                }

                this.submittedViaUpdateResource = false
                this.isWorking = false
            },
        }
    }
</script>
