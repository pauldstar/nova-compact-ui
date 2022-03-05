<template>
    <!-- For ReactiveHasMany fields -->
    <div v-if="canShowPanels">
        <component
            v-for="panel in tabulatedPanels"
            :is="panel.fields[0].component"
            :key="panel.name"
            :mode="mode"
            :panel="panel"
            :field="panel.fields[0]"
            :resource-id="resourceId"
            :resource-name="resourceName"
            :delete-unsaved-changes-on-load="deleteUnsavedChangesOnLoad"
        />
    </div>
</template>

<script>
import {BehavesAsPanel} from 'laravel-nova'

export default {
    mixins: [BehavesAsPanel],

    props: {
        mode: {type: String},
        panel: {type: Object},
        fields: {type: Array},
        resourceId: {type: [Number, String]},
        resourceName: {type: String},
        deleteUnsavedChangesOnLoad: {type: Boolean, default: true},
    },

    computed: {
        canShowPanels() {
            return ['form', 'detail'].includes(this.mode);
        },

        tabulatedPanels() {
            let panels = {},
                fields = _.toArray(this.fields);

            fields.forEach((field) => {
                if (field.tabulated) {
                    panels[field.name] = this.createPanelForRelationship(field);
                }
            })

            return _.toArray(panels);
        }
    },

    methods: {
        // @copied Detail
        createPanelForRelationship(field) {
            return {
                component: 'relationship-panel',
                prefixComponent: true,
                name: field.name,
                fields: [field],
            }
        },

        // @copied Detail
        createPanelForField(field) {
            return _.tap(this.panel, panel => panel.fields = [field])
        }
    }
}
</script>
