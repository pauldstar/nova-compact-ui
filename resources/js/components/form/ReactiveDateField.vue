<script>
import DateField from '@nova/components/Form/DateField';
import ReactiveField from '../../mixins/ReactiveField';

export default {
    extends: DateField,
    mixins: [ReactiveField],

    mounted() {
        this.watches('value');
    },

    methods: {
        setValue(value) {
            this.value = value;

            if (value)
                this.$refs.dateTimePicker.flatpickr.setDate(value, false, this.pickerFormat);
            else this.$refs.dateTimePicker.flatpickr.clear();
        }
    }
}
</script>
