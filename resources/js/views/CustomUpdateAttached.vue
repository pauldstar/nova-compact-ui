<template>
    <loading-view :loading="loading">
        <custom-header
            :headingTitle="`Update ${relatedResourceLabel}`"
            mode="form"
            :via-relationship="viaRelationship"
            :resource-id="resourceId"
            :resource-name="resourceName"
            :selected-resource="selectedResource"
        />

        <form
            v-if="field"
            @submit.prevent="updateAttachedResource"
            @change="onUpdateFormStatus"
            autocomplete="off"
        >
            <card class="overflow-hidden mb-8 bootstrap-wrapper">
                <div class="row">
                    <!-- Related Resource -->
                    <default-field :field="field" :errors="validationErrors" class="col-6">
                        <template slot="field">
                            <select-control
                                class="form-control form-select w-full"
                                dusk="attachable-select"
                                :class="{
                                    'border-danger': validationErrors.has(field.attribute),
                                }"
                                :data-testid="`${field.resourceName}-select`"
                                @change="selectResourceFromSelectControl"
                                disabled
                                :options="availableResources"
                                :label="'display'"
                                :selected="selectedResourceId"
                            >
                                <option value="" disabled selected>
                                    {{ selectedResource.display }}
                                </option>
                            </select-control>
                        </template>
                    </default-field>

                    <!-- Pivot Fields -->
                    <div v-for="field in fields" class="col-6">
                        <component
                            :is="'form-' + field.component"
                            :resource-name="resourceName"
                            :resource-id="resourceId"
                            :field="field"
                            :errors="validationErrors"
                            :related-resource-name="relatedResourceName"
                            :related-resource-id="relatedResourceId"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                        />
                    </div>
                </div>
            </card>
            <!-- Attach Button -->
            <div class="flex items-center">
                <cancel-button @click="$router.back()"/>

                <progress-button
                    class="mr-3"
                    dusk="update-and-continue-editing-button"
                    @click.native="updateAndContinueEditing"
                    :disabled="isWorking"
                    :processing="submittedViaUpdateAndContinueEditing"
                >
                    {{ __('Update & Continue Editing') }}
                </progress-button>

                <progress-button
                    dusk="update-button"
                    type="submit"
                    :disabled="isWorking"
                    :processing="submittedViaUpdateAttachedResource"
                >
                    {{ __('Update :resource', {resource: relatedResourceLabel}) }}
                </progress-button>
            </div>
        </form>

        <portal to="modals">
            <unsaved-changes-modal
                v-if="openUnsavedChangesModal"
                @confirm="confirmExit"
                @close="cancelExit"
            />
        </portal>
    </loading-view>
</template>

<script>
import UpdateAttached from "@nova/views/UpdateAttached";
import ReactiveFormView from '../mixins/ReactiveFormView';
import UnsavedChanges from '../mixins/UnsavedChanges';
import ErrorNotification from '../mixins/ErrorNotification';
import UnsavedChangesModal from '../components/modal/UnsavedChangesModal';

export default {
    extends: UpdateAttached,
    mixins: [UnsavedChanges, ReactiveFormView, ErrorNotification],
    components: {UnsavedChangesModal},

    methods: {
        // Override UpdateAttached
        async updateAttachedResource() {
            this.submittedViaUpdateAttachedResource = true

            try {
                await this.updateRequest()

                this.submittedViaUpdateAttachedResource = false
                this.canLeave = true

                Nova.success(this.__('The resource was updated!'))

                this.$router.push({
                    name: 'detail',
                    params: {
                        resourceName: this.resourceName,
                        resourceId: this.resourceId,
                    },
                })
            } catch (error) {
                window.scrollTo(0, 0)

                this.submittedViaUpdateAttachedResource = false
                if (
                    this.resourceInformation &&
                    this.resourceInformation.preventFormAbandonment
                ) {
                    this.canLeave = false
                }

                if (error.response.status == 422) {
                    this.showErrors(error.response.data.errors);
                }

                if (error.response.status == 409) {
                    Nova.error(
                        this.__(
                            'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                        )
                    )
                }
            }
        },
    }
}
</script>
