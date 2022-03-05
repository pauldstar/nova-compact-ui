<template>
    <div v-show="isActive">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: {
            name: {type: String},
            selected: {default: false},
            sortable: {default: true},
            editable: {default: true},
            disabled: {default: false}
        },

        mounted() {
            this.isActive = this.selected;
        },

        data: _ => ({
            isActive: false,
            hasError: false
        })
    }
</script>
