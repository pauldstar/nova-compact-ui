<template>
    <nav>
        <ul class="breadcrumbs">
            <li
                v-for="(to, text) in pageCrumbs"
                class="breadcrumbs-item"
                :class="{'inactive-item': ! to}"
            >
                <router-link v-if="to" :to="to">
                    {{ format(text) }}
                </router-link>
                <a v-else>{{ format(text) }}</a>
            </li>
        </ul>
    </nav>
</template>

<script>
    /*
     * The following routes should be tested for breadcrumb correctness:
     *
     * /resources/{resource} (e.g. /resources/companies)
     * /resources/{resource}/{resourceId} (e.g. /resources/companies/1)
     * /resources/{resource}/new
     * /resources/{resource}/new?viaResource={viaResourceName}&viaResourceId={viaResourceId}
     * /resources/{resource}/{resourceId}/edit
     * /resources/{resource}/{resourceId}/edit?viaResource={viaResourceName}&viaResourceId={viaResourceId}
     * /resources/action-events/{actionId}
     * /resources/{resource}/{resourceId}/attach/{relatedResource}
     * /resources/{resource}/{resourceId}/update-attached/{relatedResource}/{relatedResourceId}
     */
    export default {
        props: [
            'headingTitle',
            'resource',
            'resourceId',
            'resourceName',
            'resourceTitle',
            'singularName',
            'selectedResource',
            'viaResource',
            'viaResourceId',
        ],

        data: _ => ({
            crumbs: null
        }),

        computed: {
            pageCrumbs() {
                this.crumbs = {home: '/'};

                switch (this.$route.name) {
                    case 'custom-index': this.index(); break;
                    case 'custom-detail': this.detail(); break;
                    case 'custom-create': this.create(); break;
                    case 'custom-edit': this.edit(); break;
                    case 'custom-attach': this.attach(); break;
                    case 'custom-edit-attached': this.editAttached();
                }

                return this.crumbs;
            }
        },

        methods: {
            index() {
                this.crumbs[this.resourceName] = null;
            },

            detail() {
                this.crumbs[this.resourceName] = `/resources/${this.resourceName}`;
                this.crumbs[this.resource.title || 'details'] = null;
            },

            create() {
                if (this.viaResource) {
                    this.crumbs[this.viaResource] = `/resources/${this.viaResource}`;
                    this.crumbs[`-${this.viaResourceId}`] = `/resources/${this.viaResource}/${this.viaResourceId}`;
                }
                this.crumbs[this.resourceName] = `/resources/${this.resourceName}`;
                this.crumbs.new = null;
            },

            edit() {
                if (this.viaResource) {
                    this.crumbs[this.viaResource] = `/resources/${this.viaResource}`;
                    this.crumbs[`-${this.viaResourceId}`] = `/resources/${this.viaResource}/${this.viaResourceId}`;
                }
                this.crumbs[this.resourceName] = `/resources/${this.resourceName}`;
                this.crumbs[this.resourceTitle] = null;
            },

            attach() {
                this.crumbs[this.resourceName] = `/resources/${this.resourceName}`;
                this.crumbs[`-${this.resourceId}`] = `/resources/${this.resourceName}/${this.resourceId}/edit`;
                this.crumbs[this.headingTitle] = null;
            },

            editAttached() {
                this.crumbs[this.resourceName] = `/resources/${this.resourceName}`;
                this.crumbs[`-${this.resourceId}`] = `/resources/${this.resourceName}/${this.resourceId}/edit`;
                this.crumbs[this.headingTitle] = null;
                this.crumbs[this.selectedResource.display] = null;
            },

            format(string) {
                return string.replace(/-/g, ' ').replace(/(?:^|\s)\S/g, function (a) {
                    return a.toUpperCase();
                });
            }
        }
    }
</script>
