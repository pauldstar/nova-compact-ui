<template>
    <loading-view :loading="loading">
        <custom-header
            :headingTitle="headingTitle"
            mode="form"
            :via-relationship="viaRelationship"
            :resource="resource"
            :resource-id="resourceId"
            :resource-name="resourceName"
        />

        <form
            v-if="field"
            @submit.prevent="attachResource"
            @change="onUpdateFormStatus"
            autocomplete="off"
        >
            <card class="overflow-hidden mb-8 bootstrap-wrapper">
                <!-- Related Resource -->
                <div class="row">
                    <default-field :field="field" :errors="validationErrors" class="col-6">
                        <template slot="field">
                            <search-input
                                v-if="field.searchable"
                                :data-testid="`${field.resourceName}-search-input`"
                                @input="performSearch"
                                @clear="clearSelection"
                                @selected="selectResource"
                                :value="selectedResource"
                                :data="availableResources"
                                trackBy="value"
                                searchBy="display"
                            >
                                <div
                                    slot="default"
                                    v-if="selectedResource"
                                    class="flex items-center"
                                >
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
                                        <img
                                            :src="option.avatar"
                                            class="w-8 h-8 rounded-full block"
                                        />
                                    </div>

                                    {{ option.display }}
                                </div>
                            </search-input>

                            <select-control
                                v-else
                                dusk="attachable-select"
                                class="form-control form-select w-full"
                                :class="{
                                    'border-danger': validationErrors.has(field.attribute),
                                }"
                                :data-testid="`${field.resourceName}-select`"
                                @change="selectResourceFromSelectControl"
                                :options="availableResources"
                                :label="'display'"
                                :selected="selectedResourceId"
                            >
                                <option value="" disabled selected>{{
                                        __('Choose :resource', {
                                            resource: relatedResourceLabel,
                                        })
                                    }}
                                </option>
                            </select-control>

                            <!-- Trashed State -->
                            <div v-if="softDeletes">
                                <checkbox-with-label
                                    :dusk="field.resourceName + '-with-trashed-checkbox'"
                                    :checked="withTrashed"
                                    @change="toggleWithTrashed"
                                >
                                    {{ __('With Trashed') }}
                                </checkbox-with-label>
                            </div>
                        </template>
                    </default-field>

                    <!-- Pivot Fields -->
                    <div v-for="field in fields" class="col-6">
                        <component
                            :is="'form-' + field.component"
                            :resource-name="resourceName"
                            :field="field"
                            :errors="validationErrors"
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
                    dusk="attach-and-attach-another-button"
                    @click.native="attachAndAttachAnother"
                    :disabled="isWorking"
                    :processing="submittedViaAttachAndAttachAnother"
                >
                    {{ __('Attach & Attach Another') }}
                </progress-button>

                <progress-button
                    dusk="attach-button"
                    type="submit"
                    :disabled="isWorking"
                    :processing="submittedViaAttachResource"
                >
                    {{ __('Attach :resource', {resource: relatedResourceLabel}) }}
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
import Attach from "@nova/views/Attach";
import ReactiveFormView from '../mixins/ReactiveFormView';
import UnsavedChanges from '../mixins/UnsavedChanges';
import UnsavedChangesModal from '../components/modal/UnsavedChangesModal';
import ErrorNotification from "../mixins/ErrorNotification";

export default {
    extends: Attach,
    mixins: [UnsavedChanges, ReactiveFormView, ErrorNotification],
    components: {UnsavedChangesModal},

    methods: {

        // Override Attached
        async attachResource() {
            this.submittedViaAttachResource = true

            try {
                await this.attachRequest()

                this.submittedViaAttachResource = false
                this.canLeave = true

                this.$router.push({
                    name: 'detail',
                    params: {
                        resourceName: this.resourceName,
                        resourceId: this.resourceId,
                    },
                })
            } catch (error) {
                window.scrollTo(0, 0)

                this.submittedViaAttachResource = false
                if (
                    this.resourceInformation &&
                    this.resourceInformation.preventFormAbandonment
                ) {
                    this.canLeave = false
                }

                if (error.response.status == 422) {
                    this.showErrors(error.response.data.errors);
                }
            }
        },
    }
}
</script>
