<template>
    <div v-if="fields.length > 0">
        <card>
            <slot/>
            <div class="bootstrap-wrapper">
                <div class="row">
                    <template v-if="mode === 'form'">
                        <div
                            v-for="(column, cIndex) in columnsWithFields"
                            v-if="column && column.length !== 0"
                            :key="`col-${cIndex}`"
                            :class="isRow(column) ? 'col-12' : 'col-6'"
                        >
                            <component
                                v-for="(field, fIndex) in column"
                                :key="`field-c${cIndex}-f${fIndex}`"
                                :is="`form-${field.component}`"
                                :errors="validationErrors"
                                :resource-id="resourceId"
                                :resource-name="resourceName"
                                :field="field"
                                :via-resource="viaResource"
                                :via-resource-id="viaResourceId"
                                :via-relationship="viaRelationship"
                                :shown-via-new-relation-modal="mode === 'modal'"
                                @file-deleted="$emit('update-last-retrieved-at-timestamp')"
                            />
                        </div>
                    </template>

                    <template v-else>
                        <component
                            v-for="(field, index) in fields"
                            v-if="!field.listable && !field.tabulated"
                            class="col-12"
                            :key="index"
                            :is="`form-${field.component}`"
                            :errors="validationErrors"
                            :resource-id="resourceId"
                            :resource-name="resourceName"
                            :field="field"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            :shown-via-new-relation-modal="mode === 'modal'"
                            @file-deleted="$emit('update-last-retrieved-at-timestamp')"
                        />
                    </template>
                </div>
            </div>
        </card>

        <form-relationship-panels
            v-if="resourceId"
            :mode="mode"
            :panel="panel"
            :fields="fields"
            :resource-id="resourceId"
            :resource-name="resourceName"
            class="mt-8"
        />
    </div>
</template>

<script>
import Panel from '@nova/components/Form/Panel';
import FormRelationshipPanels from './FormRelationshipPanels';
import PanelColumns from "@ui/mixins/PanelColumns";

export default {
    extends: Panel,
    mixins: [PanelColumns],
    components: {FormRelationshipPanels},
    props: ['resource']
}
</script>
