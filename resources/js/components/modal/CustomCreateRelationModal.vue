<template>
    <modal
        dusk="new-relation-modal"
        tabindex="-1"
        role="dialog"
        @modal-close="handleClose"
        :classWhitelist="[
            'flatpickr-current-month',
            'flatpickr-next-month',
            'flatpickr-prev-month',
            'flatpickr-weekday',
            'flatpickr-weekdays',
            'flatpickr-calendar',
        ]"
    >
        <div
            class="bg-40 rounded-lg shadow-lg overflow-hidden p-8"
            style="max-width: 800px"
        >
            <component
                :is="resourceId ? 'edit-view' : 'create-view'"
                mode="modal"
                @refresh="handleRefresh"
                @cancelled-create="handleCancelledCreate"
                :field-name="fieldName"
                :table-group-id="tableGroupId"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
            />
        </div>
    </modal>
</template>

<script>
    import CreateRelationModal from '@nova/components/Modals/CreateRelationModal';

    export default {
        extends: CreateRelationModal,

        props: {
            fieldName: {type: String, default: ''},
            tableGroupId: {type: Number}
        },
    }
</script>
