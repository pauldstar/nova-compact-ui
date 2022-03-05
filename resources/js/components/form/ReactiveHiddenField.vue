<script>
import ReactiveField from '../../mixins/ReactiveField';
import HiddenField from '@nova/components/Form/HiddenField';

export default {
    extends: HiddenField,
    mixins: [ReactiveField],

    mounted() {
        this.watches('value');
    }
};
</script>
