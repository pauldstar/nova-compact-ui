<template>
    <div
        class="flex"
        :class="{'tooltip-red': hasError}"
        :aria-label="firstError || field.helpText"
        data-balloon-pos="up"
    >
        <div
            class="px-8 col-3"
            :class="[
                field.stacked ? 'pt-6' : 'py-1',
                { 'ui-form-label' : field.columnRow }
            ]"
        >
            <slot>
                <form-label :label-for="field.attribute">
                    {{ fieldLabel }}

                    <span v-if="field.required" class="text-danger">
                        {{ __('*') }}
                    </span>
                </form-label>
            </slot>
        </div>
        <div
            class="py-1 px-8"
            :class="[
                field.columnRow ? 'col-11' : 'col-9',
                { 'ui-form-input' : field.columnRow }
            ]"
        >
            <slot name="field"/>
        </div>
    </div>
</template>

<script>
    import DefaultField from "@nova/components/Form/DefaultField";
    import FormFieldStatus from "../../mixins/FormFieldStatus";

    export default {
        extends: DefaultField,
        mixins: [FormFieldStatus],

        created() {
            this.$parent.shownViaNewRelationModal && this.disableFieldStatus();
        },

        computed: {
            firstError() {
                return this.showErrors && this.hasError
                    ? this.errors.errors[this.field.attribute][0]
                    : '';
            },

            hasError() {
                return this.errors.errors[this.field.attribute];
            }
        }
    }
</script>
