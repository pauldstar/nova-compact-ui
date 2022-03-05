<template>
    <table
        class="table w-full text-sm"
        :id="tableId"
        data-testid="resource-table"
    >
        <thead>
        <tr>
            <!-- Drag Indicator -->
            <th v-if="draggable" class="w-8">
            </th>

            <!-- Select Checkbox -->
            <th
                :class="{
                    'w-16': shouldShowCheckboxes,
                    'w-8': !shouldShowCheckboxes,
                }"
            >
                <slot/>
            </th>

            <!-- Field Names -->
            <th v-for="field in fields" :class="`text-${field.textAlign}`">
                <sortable-icon
                    @sort="requestOrderByChange(field)"
                    @reset="resetOrderBy(field)"
                    :order-by="orderBy"
                    :order-by-direction="orderByDirection"
                    :resource-name="resourceName"
                    :uri-key="field.sortableUriKey"
                    v-if="field.sortable"
                >
                    {{ field.indexName }}
                </sortable-icon>

                <span v-else>{{ field.indexName }}</span>
            </th>

            <!-- Actions, View, Edit, Delete -->
            <th v-if="! isShownViaModal"></th>
        </tr>
        </thead>
        <tbody :class="notelessClass">
        <tr
            v-for="(resource, index) in resources"
            v-if="notable ? !resource.tableNoteId : true"
            class="bg-white"
            :class="rowClass"
            @actionExecuted="$emit('actionExecuted')"
            :mode="mode"
            :testId="`${resourceName}-items-${index}`"
            :key="resource.id.value"
            :delete-resource="deleteResource"
            :restore-resource="restoreResource"
            is="resource-table-row"
            :draggable="draggable"
            :field-name="fieldName"
            :data-resourceid="resource.id.value"
            :resource="resource"
            :resource-name="resourceName"
            :relationship-type="relationshipType"
            :via-relationship="viaRelationship"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-many-to-many="viaManyToMany"
            :checked="selectedResources.indexOf(resource) > -1"
            :actions-are-available="actionsAreAvailable"
            :should-show-checkboxes="shouldShowCheckboxes"
            :show-modal="showModal"
            :update-selection-status="updateSelectionStatus"
        />
        </tbody>

        <template v-if="notable">
            <tbody
                v-for="note in notesWithResources"
                :data-noteid="note.id.value"
                :class="noteClass"
            >
            <tr
                :delete-resource="deleteResource"
                :restore-resource="restoreResource"
                is="resource-table-row"
                :draggable="draggable"
                :field-name="fieldName"
                :resource="note"
                :resource-name="'table-notes'"
                :via-relationship="viaRelationship"
                :actions-are-available="false"
                :should-show-checkboxes="false"
                :show-modal="showModal"
                class="table-note bg-primary"
                :class="noteHandleClass"
            />

            <tr
                v-for="(resource, index) in note.resources"
                class="bg-white table-row"
                :class="rowClass"
                @actionExecuted="$emit('actionExecuted')"
                :testId="`${resourceName}-items-${index}`"
                :key="resource.id.value"
                :delete-resource="deleteResource"
                :restore-resource="restoreResource"
                is="resource-table-row"
                :draggable="draggable"
                :field-name="fieldName"
                :data-resourceid="resource.id.value"
                :resource="resource"
                :resource-name="resourceName"
                :relationship-type="relationshipType"
                :via-relationship="viaRelationship"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-many-to-many="viaManyToMany"
                :checked="selectedResources.indexOf(resource) > -1"
                :actions-are-available="actionsAreAvailable"
                :should-show-checkboxes="shouldShowCheckboxes"
                :show-modal="showModal"
                :update-selection-status="updateSelectionStatus"
            />
            </tbody>
        </template>
    </table>
</template>

<script>
import ResourceTable from "@nova/components/ResourceTable";
import Sortable from "sortablejs";

export default {
    extends: ResourceTable,

    props: [
        'mode',
        'fieldName',
        'draggable',
        'notable',
        'tableGroupId',
        'showModal',
        'orderBy',
        'orderByDirection',
        'notesWithResources'
    ],

    mounted() {
        this.initSortable();
    },

    computed: {
        draggableField() {
            return this.fieldName && this.draggable;
        },

        isShownViaModal() {
            return this.mode === 'modal';
        },

        uid() {
            return `${this.fieldName}-${this.tableGroupId}`;
        },

        tableId() {
            return this.draggableField ? `${this.uid}-table` : '';
        },

        noteClass() {
            return this.draggableField ? `noted-${this.uid}-container` : '';
        },

        notelessClass() {
            return this.draggableField ? `noteless-${this.uid}-container` : '';
        },

        noteHandleClass() {
            return this.draggableField ? `${this.uid}-container-handle` : '';
        },

        rowClass() {
            return this.draggableField ? `${this.uid}-table-row` : '';
        }
    },

    methods: {
        initSortable() {
            if (!this.draggableField || !this.resources.length) return;

            let notes = document.querySelectorAll(
                `.${this.noteClass}, .${this.notelessClass}`
            );

            for (let i = 0; i < notes.length; i++) {
                new Sortable(notes[i], {
                    group: 'notes',
                    handle: '.row-drag-indicator',
                    draggable: `.${this.rowClass}`,
                    onEnd: this.handleResourceSort
                });
            }

            let table = document.getElementById(this.tableId);

            new Sortable(table, {
                draggable: `.${this.noteClass}`,
                handle: `.${this.noteHandleClass} > .row-drag-indicator`,
                onEnd: this.handleNoteSort
            });
        },

        handleNoteSort(e) {
            let start = e.oldDraggableIndex,
                end = e.newDraggableIndex;

            if (start !== end) this.$emit('sort-note', {
                start, end,
                noteId: e.item.dataset.noteid
            });
        },

        handleResourceSort(e) {
            let start = e.oldDraggableIndex,
                end = e.newDraggableIndex,
                startNoteId = e.from.dataset.noteid,
                endNoteId = e.to.dataset.noteid;

            if (start === end && startNoteId === endNoteId) return;

            this.$emit('sort-resource', {
                start, end,
                resourceName: this.resourceName,
                resourceId: e.item.dataset.resourceid,
                noteId: endNoteId
            });
        }
    }
}
</script>

<style>
.table tr.table-note:hover td {
    background-color: #4099de !important;
}

.sortable-chosen .row-drag-indicator {
    cursor: ns-resize;
}
</style>
