export default {
    props: {
        resourceId: {type: [Number, String]}
    },

    data: () => ({
        fieldsWithValues: {},
        skipBroadcast: false
    }),

    created() {
        if (this.field.listensTo) {
            Nova.$on(
                'reactive-field-response:' + this.resourceName,
                this.getValue
            );
        }
    },

    destroyed() {
        if (this.field.listensTo) {
            Nova.$off(
                'reactive-field-response:' + this.resourceName,
                this.getValue
            );
        }
    },

    computed: {
        shouldEmitOnLoad() {
            return ! this.resourceId
                && this.field.resourceName === this.viaResource;
        },

        awaitingEmitOnLoad() {
            return ! this.resourceId
                && this.field.dependsOnResource === this.viaResource;
        },
    },

    methods: {
        // name of value to broadcast after a change
        watches(value) {
            if (this.field.broadcastFrom) {
                this.$watch(value, _.debounce(function () {
                    if (this.skipBroadcast) {
                        return void (this.skipBroadcast = false);
                    }

                    this.broadcast(this.$data[value]);
                }, 500));

                this.shouldEmitOnLoad && this.broadcast(this.$data[value], true);
            }
        },

        // broadcast value to listening components
        broadcast(value, emittedOnLoad = false) {
            if (this.skipBroadcast) {
                return void (this.skipBroadcast = false);
            }

            if (this.field.broadcastFrom) {
                Nova.$emit('reactive-field-broadcast:' + this.resourceName, {
                    'broadcastFrom': this.field.broadcastFrom,
                    'emittedOnLoad': emittedOnLoad,
                    'fieldName': this.field.attribute,
                    'resourceId': this.resourceId,
                    'value': value
                });
            }
        },

        getValue(e) {
            if (e[this.field.attribute] === undefined) {
                return;
            }

            this.skipBroadcast = true;
            this.setValue(e[this.field.attribute], e.emittedOnLoad);
        },

        // override this to customise how broadcast results are handled for a specific field
        setValue(val) {
            this.value = val;
        }
    }
}
