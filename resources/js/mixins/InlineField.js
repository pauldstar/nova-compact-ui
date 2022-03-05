import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'field'],

    methods: {
        submit() {
            let formData = new FormData();

            formData.append(this.field.attribute, this.value);
            formData.append('viaInlineField', true);
            formData.append('_method', 'PUT');

            return Nova.request()
                .post(
                    `/nova-api/${this.resourceName}/${this.$parent.resource.id.value}`,
                    formData
                )
                .then(
                    () => {
                        this.$toasted.show(`${this.field.name} updated`, {
                            type: 'success',
                        });

                        this.refreshTable();
                    },
                    (response) => this.$toasted.show(response, {type: 'error'})
                );
        },

        refreshTable() {
            if (this.shouldRefresh) {
                if (this.$parent.fieldName)
                    Nova.$emit(`${this.$parent.fieldName}:resource-updated`);
                else this.$parent.$parent.$parent.$parent.$parent.$parent.getResources();
            }
        },

        capitalize(string) {
            return string.charAt(0).toUpperCase() + string.substr(1);
        },
    },

    computed: {
        resourceId() {
            return this.$parent.resource.id.value;
        },

        fieldId() {
            return `inline-text-field-${this.field.name}-${this.resourceId}`;
        },

        shouldRefresh() {
            return this.field.inlineRefreshOnSaving;
        },

        listener() {
            const event = this.field.inlineSaveTrigger.split('.');
            const name = event[0];
            const modifier = event[1] ? this.capitalize(event[1]) : null;

            return {
                [name]: (e) => {
                    if (this.valueWasNotChanged) return;

                    if (modifier && modifier === e.key) this.submit();

                    if (!modifier) this.submit();
                },
            };
        },

        valueWasNotChanged() {
            return this.value === this.field.value;
        },
    }
};
