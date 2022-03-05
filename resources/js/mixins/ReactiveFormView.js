export default {
    data: () => ({
        fieldsWithValues: {},
        ignoreOnLoadEmit: true
    }),

    created() {
        Nova.$on(
            'reactive-field-broadcast:' + this.resourceName,
            this.broadcastValues
        );
    },

    destroyed() {
        Nova.$off(
            'reactive-field-broadcast:' + this.resourceName,
            this.broadcastValues
        );
    },

    methods: {
        broadcastValues: function (e) {
            this.fieldsWithValues[e.fieldName] = e.value;
            this.fieldsWithValues.resourceId = e.resourceId;

            let urlPrefix = '/pauldstar/nova-compact-reactive-ui/calculate';

            Nova.request().post(
                `${urlPrefix}/${this.resourceName}/${e.broadcastFrom}`,
                this.fieldsWithValues
            ).then(({data}) => {
                let value;

                for (const field in data) {
                    value = data[field];

                    if (value instanceof  Object) {
                        value = value['value'];
                    }

                    this.fieldsWithValues[field] = value;
                }

                data.emittedOnLoad = e.emittedOnLoad;
                Nova.$emit('reactive-field-response:' + this.resourceName, data);
            });
        }
    }
}
