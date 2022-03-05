<template>
    <modal @modal-close="handleClose">
        <div
            class="bg-40 rounded-lg shadow-lg overflow-hidden p-8"
            style="width: 1600px"
        >
            <resource-index
                ref="index"
                mode="modal"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
                :initial-search="search"
                :field="field"
                relationship-type="hasMany"
                :load-cards="false"
                :can-create-via-modal="canCreateViaModal"
            />
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        search: String,
        resourceName: String,
        field: Object,
        viaResource: String,
        viaResourceId: {type: [String, Number]},
        viaRelationship: String,
        canCreateViaModal: {type: Boolean, default: true}
    },

    methods: {
        handleClose() {
            this.$emit('cancelled-search')
        },

        handleSelect() {
            this.$emit('set-resource');
        }
    },
}
</script>
