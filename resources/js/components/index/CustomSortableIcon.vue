<script>
import SortableIcon from '@nova/components/Index/SortableIcon';

export default {
    extends: SortableIcon,

    props: {
        orderBy: String,
        orderByDirection: String,
    },

    computed: {
        // override SortableIcon
        sortColumn() {
            return this.$route.query[this.sortKey] ||  this.orderBy;
        },

        // override SortableIcon
        direction() {
            return this.$route.query[this.directionKey] || this.orderByDirection;
        }
    }
}
</script>
