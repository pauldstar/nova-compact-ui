<template>
    <div>
        <card>
            <slot/>
            <div class="bootstrap-wrapper px-3">
                <div class="row">
                    <div
                        v-for="(column, cIndex) in columnsWithFields"
                        v-if="column && column.length !== 0"
                        :key="`col-${cIndex}`"
                        :class="isRow(column) ? 'col-12' : 'col-6'"
                    >
                        <component
                            v-for="(field, fIndex) in column"
                            :key="`field-c${cIndex}-f${fIndex}`"
                            :class="{ 'remove-bottom-border': fIndex === panel.fields.length - 1 }"
                            :is="resolveComponentName(field)"
                            :resource-name="resourceName"
                            :resource-id="resourceId"
                            :resource="resource"
                            :field="field"
                            @actionExecuted="actionExecuted"
                        />
                    </div>
                </div>
            </div>

            <div
                v-if="shouldShowShowAllFieldsButton"
                class="bg-20 -mt-px -mx-6 -mb-6 border-t border-40 p-3 text-center rounded-b text-center"
            >
                <button
                    class="block w-full dim text-80 font-bold"
                    @click="showAllFields"
                >
                    {{ __('Show All Fields') }}
                </button>
            </div>
        </card>

        <form-relationship-panels
            :panel="panel"
            :fields="fields"
            mode="form"
            :resource-id="resourceId"
            :resource-name="resourceName"
        />
    </div>
</template>

<script>
    import Panel from "@nova/components/Detail/Panel";
    import FormRelationshipPanels from '../form/FormRelationshipPanels';
    import PanelColumns from "@ui/mixins/PanelColumns";

    export default {
        extends: Panel,
        mixins: [PanelColumns],
        components: {FormRelationshipPanels},
    }
</script>

