<template>
    <component
        :is="detailComponent"
        :resourceName="resourceName"
        :resourceId="resourceId"
    />
</template>

<script>
    import {mapProps, Minimum} from "laravel-nova";

    export default {
        props: mapProps([
            'resourceName',
            'resourceId',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
        ]),

        data: _=> ({
            canUpdate: false,
        }),

        async created() {
            await this.getResourceAuth();
        },

        computed: {
            detailComponent() {
                if (this.canUpdate) return 'update-detail';
                return 'view-detail';
            }
        },

        methods: {
            // @copy UpdateDetail
            async getResourceAuth() {
                let urlPrefix = '/pauldstar/nova-compact-reactive-ui',
                    urlSegments = `${this.resourceName}/${this.resourceId}/resource-auth`;

                return Minimum(
                    Nova.request().get(`${urlPrefix}/${urlSegments}`)
                ).then(response => {
                    let data = response.data;

                    if (data.authorizedToUpdate || data.authorizedToView) {
                        this.canUpdate = data.authorizedToUpdate;
                        this.canView = data.authorizedToView;
                    } else {
                        this.$router.push({name: '403'})
                    }
                });
            }
        }
    }
</script>
