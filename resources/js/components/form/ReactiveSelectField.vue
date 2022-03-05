<script>
import SelectField from '@nova/components/Form/SelectField';
import ReactiveField from '../../mixins/ReactiveField';

export default {
    extends: SelectField,
    mixins: [ReactiveField],

    mounted() {
        this.watches('value');
    },

    methods: {
        setValue(val) {
            if (this.isSearchable) {
                this.selectedOption = _.find(
                    this.filteredOptions, ['value', val]
                );
            } else this.value = val;
        }
    }
}
</script>
