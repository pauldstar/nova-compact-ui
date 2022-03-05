<template>
    <div
        v-if="isForm || isIndex"
        :class="mode === 'modal' ? 'pb-4' : 'py-4'"
        class="flex items-center"
    >
        <heading :level="1" class="capitalize">{{ slimHeadingTitle }}</heading>

        <breadcrumbs
            v-if="mode !== 'modal'"
            class="ml-4"
            :heading-title="headingTitle"
            :resource="resource"
            :resource-id="resourceId"
            :resource-name="resourceName"
            :resource-title="resourceTitle"
            :singular-name="resourceName"
            :via-resource="viaResource === '' ? null : viaResource"
            :via-resource-id="viaResourceId"
            :selected-resource="selectedResource"
        />
    </div>
</template>

<script>
    import breadcrumbs from './Breadcrumbs';

    export default {
        components: {breadcrumbs},

        props: [
            'headingTitle',
            'mode',
            'resource',
            'resourceId',
            'resourceName',
            'resourceTitle',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
            'selectedResource'
        ],

        computed: {
            slimHeadingTitle() {
                return this.headingTitle && this.headingTitle.substring(0, 50);
            },

            isForm() {
                return ['form', 'modal'].includes(this.mode);
            },

            isIndex() {
                return this.$route.name === 'custom-index';
            }
        }
    }
</script>
