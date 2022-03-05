<template>
    <div>
        <create-form
            @resource-created="handleResourceCreated"
            @cancelled-create="handleCancelledCreate"
            :mode="mode"
            :table-group-id="tableGroupId"
            :resource-name="resourceName"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
            :update-form-status="updateFormStatus"
            :disable-unsaved-prompt="disableUnsavedPrompt"
        />

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
                :resource-name="resourceName"
                :selected-resources="selectedResources"
                :action="selectedAction"
                :errors="errors"
                @confirm="executeAction"
                @close="skipConfirmationModal"
            />
        </portal>
    </div>
</template>

<script>
import Create from '@nova/views/Create';
import FormActions from '../mixins/FormActions';
import UnsavedChanges from '../mixins/UnsavedChanges';
import ReactiveFormView from '../mixins/ReactiveFormView';
import UnsavedChangesModal from '../components/modal/UnsavedChangesModal';

export default {
    extends: Create,
    mixins: [FormActions, UnsavedChanges, ReactiveFormView],
    components: {UnsavedChangesModal},

    props: {
        fieldName: {type: String, default: ''},
        tableGroupId: {type: Number}
    },

    mounted() {
        Nova.$on(['paginate', 'search'], value => {
            this.skipUnsavedPrompt(_ => this.updateQueryString(value));
        });
    },

    methods: {
        handleResourceCreated({ redirect, id }) {
            if (this.mode === 'form') {
                return this.runActions(redirect, id);
            }

            if (this.fieldName) {
                return Nova.$emit(`${this.fieldName}:resource-created`);
            }

            return this.$emit('refresh', { redirect, id })
        },

        handleCancelledCreate() {
            if (this.mode !== 'modal') {
                return this.$router.back()
            }

            return this.$emit('cancelled-create')
        }
    }
}
</script>
