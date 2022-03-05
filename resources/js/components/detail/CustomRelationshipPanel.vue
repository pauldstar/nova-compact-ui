<script>
import RelationshipPanel from '@nova/components/Detail/RelationshipPanel';

export default {
    extends: RelationshipPanel,

    mounted() {
        this.panel.fields.forEach(field => field.viaResource = this.resource);
    }
}
</script>
