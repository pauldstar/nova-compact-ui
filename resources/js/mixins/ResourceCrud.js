import ErrorNotification from '@ui/mixins/ErrorNotification';

export default {
    mixins: [ErrorNotification],

    methods: {
        resourceFormData(fields) {
            return _.tap(new FormData(), formData => {
                if (Array.isArray(fields))
                    fields.forEach(field => {
                        if (!field.tabulated && field.fill) field.fill(formData);
                    });
                else {
                    for (const attribute in fields) {
                        formData.append(attribute, String(fields[attribute]));
                    }
                }

                formData.append('viaResourceCrud', true);
            })
        },

        async addResource(resourceName, fields) {
            try {
                const {data: {id}} = await this.addRequest(resourceName, fields)
                this.hideErrors();
                Nova.success('Resource created');
                return id;
            } catch (error) {
                if (error.response.status === 422) {
                    this.showErrors(error.response.data.errors);
                } else Nova.error('Resource creation failure');

                return null;
            }
        },

        addRequest(resourceName, fields) {
            return Nova.request().post(
                `/nova-api/${resourceName}`,
                this.resourceFormData(fields),
                {params: {editing: true, editMode: 'create'}}
            )
        },

        async amendResource(resourceName, resourceId, fields) {
            try {
                await this.amendRequest(resourceName, resourceId, fields)
                this.hideErrors();
                Nova.success('Resource Updated');
                return resourceId;
            } catch (error) {
                if (error.response.status === 422) {
                    this.showErrors(error.response.data.errors);
                } else Nova.error('Resource update failure');
                return null;
            }
        },

        amendRequest(resourceName, resourceId, fields) {
            let uri = `/nova-api/${resourceName}/${resourceId}`,
                formData = this.resourceFormData(fields);

            formData.append('_method', 'PUT')

            return Nova.request().post(uri, formData, {
                params: {
                    editing: true,
                    editMode: 'update',
                },
            });
        },

        async removeResource(resourceName, resourceId) {
            try {
                await this.removeRequest(resourceName, resourceId)
                Nova.success('Resource deleted');
                return resourceId;
            } catch (error) {
                Nova.error('Resource delete failure')
                return null;
            }
        },

        removeRequest(resourceName, resourceId) {
            return Nova.request({
                url: '/nova-api/' + resourceName,
                method: 'delete',
                params: {
                    ...{resources: [resourceId]},
                },
            })
        },

        async attachResource(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields) {
            try {
                await this.attachRequest(
                    resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields
                );

                this.hideErrors();
                Nova.success('Resource attached');
            } catch (error) {
                if (error.response.status === 422) {
                    this.showErrors(error.response.data.errors);
                } else Nova.error('Resource attachment failure');

                return null;
            }
        },

        attachRequest(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields) {
            let uri = `/nova-api/${resourceName}/${resourceId}/attach/${relatedResource}`,
                formData = this.resourceFormData(fields);

            formData.append(relatedResource, relatedResourceId)
            formData.append('viaRelationship', viaRelationship)

            return Nova.request().post(uri, formData, {
                params: {
                    editing: true,
                    editMode: 'attach'
                },
            });
        },

        async amendAttachedResource(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields) {
            try {
                await this.amendAttachedRequest(
                    resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields
                );

                this.hideErrors();
                Nova.success('Resource attached');
            } catch (error) {
                if (error.response.status === 422) {
                    this.showErrors(error.response.data.errors);
                } else Nova.error('Resource update failure');

                return null;
            }
        },

        async amendAttachedRequest(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship, fields) {
            let uri = `/nova-api/${resourceName}/${resourceId}/update-attached/${relatedResource}/${relatedResourceId}`,
                formData = this.resourceFormData(fields);

            formData.append(relatedResource, relatedResourceId)
            formData.append('viaRelationship', viaRelationship)

            return Nova.request().post(uri, formData, {
                params: {
                    editing: true,
                    editMode: 'update-attached'
                },
            });
        },

        async detachResource(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship) {
            try {
                await this.detachRequest(
                    resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship
                );

                Nova.success('Resource detached');
            } catch (error) {
                Nova.error('Resource detach failure');
                return null;
            }
        },

        detachRequest(resourceName, resourceId, relatedResource, relatedResourceId, viaRelationship) {
            return Nova.request({
                url: `/nova-api/${relatedResource}/detach`,
                method: 'delete',
                params: {
                    viaResource: resourceName,
                    viaResourceId: resourceId,
                    viaRelationship: viaRelationship,
                    resources: [relatedResourceId],
                    editing: true,
                    editMode: 'attach'
                }
            });
        },
    },
}
