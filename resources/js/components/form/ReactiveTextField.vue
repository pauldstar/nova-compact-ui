<script>
    import ReactiveField from '../../mixins/ReactiveField';
    import TextField from '@nova/components/Form/TextField';

    export default {
        extends: TextField,
        mixins: [ReactiveField],

        mounted() {
            this.watches('value');
        }
    };
</script>
