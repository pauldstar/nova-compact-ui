import {Minimum} from 'laravel-nova'

export default {
    data: () => ({
        resource: null,
    }),

    methods: {
        getResource() {
            this.resource = null;

            if (this.viaResourceId === 0) {
                return;
            }

            let resourceId = this.viaResourceId || this.resourceId,
                resourceName = this.viaResource ? this.viaResource : this.resourceName;

            return Minimum(
                Nova.request().get('/nova-api/' + resourceName + '/' + resourceId)
            ).then(({data: {resource}}) => {
                this.resource = resource;
                this.loading = false
            }).catch(error => {
                if (error.response.status >= 500) {
                    Nova.$emit('error', error.response.data.message)
                    return
                }

                if (error.response.status === 404 && this.initialLoading) {
                    this.$router.push({name: '404'})
                    return
                }

                if (error.response.status === 403) {
                    this.$router.push({name: '403'})
                    return
                }

                this.$toasted.show(this.__('This resource no longer exists'), {type: 'error'})

                this.$router.push({
                    name: 'index',
                    params: {resourceName: this.resourceName},
                })
            })
        },
    }
}
