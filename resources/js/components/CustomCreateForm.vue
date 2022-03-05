<template>
    <loading-view :loading="loading">
        <form
            v-if="panels"
            @submit="submitViaCreateResource"
            @change="onUpdateFormStatus"
            autocomplete="off"
            ref="form"
        >
            <custom-header
                :headingTitle="headingTitle"
                :mode="mode"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
            />

            <template v-if="isSalesOrder">
                <sales-order-panel
                    v-if="panelsWithFields.length > 0"
                    :panels="panelsWithFields"
                    :fields="Object.values(fields)"
                    :validation-errors="validationErrors"
                    :resource-name="resourceName"
                    :via-resource="viaResource"
                    :via-resource-id="viaResourceId"
                    @form-change="onUpdateFormStatus"
                    :via-relationship="viaRelationship"
                    :disable-unsaved-prompt="disableUnsavedPrompt"
                />
            </template>

            <template v-else>
                <form-panel
                    class="mb-8"
                    v-for="panel in panelsWithFields"
                    :shown-via-new-relation-modal="shownViaNewRelationModal"
                    :panel="panel"
                    :mode="mode"
                    :name="panel.name"
                    :key="panel.name"
                    :resource-name="resourceName"
                    :fields="panel.fields"
                    :validation-errors="validationErrors"
                    :via-resource="viaResource"
                    :via-resource-id="viaResourceId"
                    :via-relationship="viaRelationship"
                />
            </template>

            <div class="flex items-center" v-if="! isSalesOrder">
                <cancel-button @click="$emit('cancelled-create')"/>

                <progress-button
                    dusk="create-button"
                    type="submit"
                    :disabled="isWorking"
                    :processing="wasSubmittedViaCreateResource"
                >
                    {{ progressButtonText }}
                </progress-button>
            </div>
        </form>
    </loading-view>
</template>

<script>
import CreateForm from '@nova/components/CreateForm';
import ErrorNotification from '../mixins/ErrorNotification';
import FormRelationshipPanels from '../components/form/FormRelationshipPanels';
import {InteractsWithQueryString} from 'laravel-nova';
import SalesOrderPanel from '../components/form/SalesOrderPanel';

export default {
    extends: CreateForm,

    mixins: [InteractsWithQueryString, ErrorNotification],

    components: {FormRelationshipPanels, SalesOrderPanel},

    props: {
        tableGroupId: {type: Number},
        disableUnsavedPrompt: {type: Function},
    },

    metaInfo() {
        return {
            title: this.headingTitle,
        }
    },

    data: _ => ({
        progressButtonText: ''
    }),

    mounted() {
        this.progressButtonText = 'Create ' + this.singularName;
    },

    destroyed() {
        Nova.$off(['paginate', 'search']);
    },

    computed: {
        headingTitle() {
            return this.panels[0] ? this.panels[0].name : '';
        },

        hasTabulatedField() {
            let fields = _.filter(this.fields, field => field.tabulated);
            return fields.length > 0;
        },

        // @override CreateForm
        panelsWithFields() {
            return _.map(this.panels, panel => {
                return {
                    ...panel,
                    fields: _.filter(this.fields, field => field.panel === panel.name),
                }
            })
        },

        isSalesOrder() {
            switch (this.resourceName) {
                case 'opportunities':
                case 'sales-orders':
                case 'invoices':
                case 'quote-templates':
                    return true;
                default:
                    return false;
            }
        },
    },

    methods: {
        // @override CreateForm
        createResourceFormData() {
            return _.tap(new FormData(), formData => {
                _.each(this.fields, field => {
                    if (!field.tabulated && field.fill) field.fill(formData);
                })

                if (this.tableGroupId) {
                    formData.get('tableGroup')
                    || formData.append('tableGroup', this.tableGroupId)
                }

                formData.append('viaResource', this.viaResource)
                formData.append('viaResourceId', this.viaResourceId)
                formData.append('viaRelationship', this.viaRelationship)
            })
        },

        // @override CreateForm
        async createResource() {
            this.isWorking = true;

            if (this.$refs.form.reportValidity()) {
                try {
                    let {
                        data: {redirect, id},
                    } = await this.createRequest();

                    Nova.success(
                        this.__('The :resource was created!', {
                            resource: this.resourceInformation.singularLabel.toLowerCase(),
                        })
                    );

                    // redirects to parent resource if created via resource
                    if (this.viaResource)
                        redirect = `/resources/${this.viaResource}/${this.viaResourceId}`;
                    else if (this.hasTabulatedField)
                        redirect = `/resources/${this.resourceName}/${id}`;
                    else redirect = `/resources/${this.resourceName}`;

                    this.$emit('resource-created', {id, redirect})
                } catch (error) {
                    this.submittedViaCreateResource = true;
                    this.isWorking = false;

                    if (error.response.status === 422) {
                        this.showErrors(error.response.data.errors);
                    } else {
                        Nova.error(
                            this.__('There was a problem submitting the form.') +
                            ' "' +
                            error.response.statusText +
                            '"'
                        )
                    }
                }
            }

            this.submittedViaCreateResource = true;
            this.isWorking = false;
        }
    }
}
</script>
