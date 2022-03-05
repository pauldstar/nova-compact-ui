export default {
    mounted() {
        if (this.disabled) return;

        setTimeout(_ => this.fieldFill = this.field.fill, 700);

        this.$watch('fieldValue', _.debounce(function (val) {
            if (this.initialValue === undefined) return void (this.initialValue = val);

            Nova.$emit('field-status', {
                field: this.field.attribute,
                changed: this.isDirty(val),
                triggerFormChange: this.triggerFormChange
            });
        }, 700))
    },

    data: _ => ({
        fieldFill: null,
        initialValue: undefined,
        disabled: false,
        triggerFormChange: false
    }),

    computed: {
        fieldValue() {
            if (this.fieldFill) {
                let fd = new FormData;
                this.fieldFill(fd);
                return fd.get(this.field.attribute);
            }

            return null;
        }
    },

    methods: {
        disableFieldStatus() {
            this.disabled = true;
        },

        isDirty(val) {
            return this.initialValue === undefined ? false : val !== this.initialValue;
        }
    }
}
