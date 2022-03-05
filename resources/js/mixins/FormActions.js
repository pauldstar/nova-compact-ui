import {Errors} from 'laravel-nova'

export default {
    props: {
        resourceName: String,

        endpoint: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            formActions: [],
            submissionRedirect: null,
            working: false,
            selectedAction: null,
            selectedActionIndex: 0,
            selectedActionKey: '',
            selectedResources: [this.resourceId],
            errors: new Errors(),
            openConfirmActionModal: false,
        }
    },

    methods: {
        runActions(redirect, id) {
            this.openConfirmationModal();
            this.submissionRedirect = redirect;
            this.selectedResources = [id];
            this.nextAction();
        },

        nextAction() {
            let action = this.formActions[this.selectedActionIndex++];

            if (!action) {
                this.disableUnsavedPrompt();
                return this.$router.push({path: this.submissionRedirect});
            }

            this.selectedAction = action;
            this.selectedActionKey = action.uriKey;
        },

        getFormActions() {
            if (this.resource.softDeleted) return;

            let filter = this.$route.name === 'custom-create'
                ? 'showWhenCreating' : 'showWhenUpdating';

            return Nova.request()
                .get(`/nova-api/${this.resourceName}/actions`, {
                    params: {
                        viaResource: this.viaResource,
                        viaResourceId: this.viaResourceId,
                        viaRelationship: this.viaRelationship,
                    },
                })
                .then(response => {
                    this.formActions = _.filter(response.data.actions, a => {
                        if (a[filter]) {
                            a.cancelButtonText = 'Skip';
                            return true;
                        }
                    });
                })
        },

        /**
         * Determine whether the action should redirect or open a confirmation modal
         */
        determineActionStrategy() {
            if (this.selectedAction.withoutConfirmation) {
                this.executeAction()
            } else {
                this.openConfirmationModal()
            }
        },

        /**
         * Confirm with the user that they actually want to run the selected action.
         */
        openConfirmationModal() {
            this.openConfirmActionModal = true
        },

        /**
         * Close the action confirmation modal.
         */
        skipConfirmationModal() {
            this.errors = new Errors();
            this.nextAction();
        },

        /**
         * Execute the selected action.
         */
        executeAction() {
            this.working = true

            if (this.selectedResources.length == 0) {
                alert(this.__('Please select a resource to perform this action on.'))
                return
            }

            Nova.request({
                method: 'post',
                url: this.endpoint || `/nova-api/${this.resourceName}/action`,
                params: this.actionRequestQueryString,
                data: this.actionFormData(),
            }).then(response => {
                this.handleActionResponse(response.data)
                this.working = false
                this.nextAction();
            }).catch(error => {
                this.working = false

                if (error.response.status == 422) {
                    this.errors = new Errors(error.response.data.errors)
                    Nova.error(this.__('There was a problem executing the action.'))
                }
            })
        },

        /**
         * Gather the action FormData for the given action.
         */
        actionFormData() {
            return _.tap(new FormData(), formData => {
                formData.append('resources', this.selectedResources)

                _.each(this.selectedAction.fields, field => {
                    field.fill(formData)
                })
            })
        },

        /**
         * Handle the action response. Typically either a message, download or a redirect.
         */
        handleActionResponse(data) {
            if (data.message) {
                this.$emit('actionExecuted')
                Nova.$emit('action-executed')
                Nova.success(data.message)
            } else if (data.deleted) {
                this.$emit('actionExecuted')
                Nova.$emit('action-executed')
            } else if (data.danger) {
                this.$emit('actionExecuted')
                Nova.$emit('action-executed')
                Nova.error(data.danger)
            } else if (data.download) {
                let link = document.createElement('a')
                link.href = data.download
                link.download = data.name
                document.body.appendChild(link)
                link.click()
                document.body.removeChild(link)
            } else if (data.redirect) {
                window.location = data.redirect
            } else if (data.push) {
                this.$router.push(data.push)
            } else if (data.openInNewTab) {
                window.open(data.openInNewTab, '_blank')
            } else {
                this.$emit('actionExecuted')
                Nova.$emit('action-executed')
                Nova.success(this.__('The action ran successfully!'))
            }
        },
    },

    computed: {
        /**
         * Get the query string for an action request.
         */
        actionRequestQueryString() {
            return {
                action: this.selectedActionKey,
                search: this.currentSearch,
                filters: this.encodedFilters,
                trashed: this.currentTrashed,
                viaResource: this.queryString.viaResource,
                viaResourceId: this.queryString.viaResourceId,
                viaRelationship: this.queryString.viaRelationship,
            }
        },
    },
}
