<template>
    <loading-view :loading="initialLoading">
        <custom-header
            :headingTitle="headingTitle"
            mode="form"
            :resource="resource"
            :resource-title="resource && resource.title"
            :resource-id="resourceId"
            :resource-name="resourceName"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
        />

        <div v-if="shouldShowCards">
            <cards
                v-if="smallCards.length > 0"
                :cards="smallCards"
                :resource="resource"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :only-on-detail="true"
            />

            <cards
                v-if="largeCards.length > 0"
                :cards="largeCards"
                size="large"
                :resource="resource"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :only-on-detail="true"
            />
        </div>

        <tabs>
            <tab
                v-for="(panel, index) in tabPanels"
                :key="panel.id"
                :name="index ? panel.name : 'Details'"
                :selected="!index"
            >
                <section
                    :dusk="resourceName + '-detail-component'"
                    :key="panel.id"
                    :id="slug(panel.name)"
                    :class="{'tab-panel': tabPanels.length > 1}"
                >
                    <component
                        v-if="index"
                        :is="panel.component"
                        class="mb-6"
                        :resource-name="resourceName"
                        :resource-id="resourceId"
                        :resource="resource"
                        :panel="panel"
                    />

                    <sales-order-panel
                        v-else-if="isSalesOrder"
                        :panels="panelsWithFields"
                        :fields="fields"
                        :validation-errors="validationErrors"
                        :resource="resource"
                        :resource-id="resourceId"
                        :resource-name="resourceName"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        @form-change="onUpdateFormStatus"
                        :disable-unsaved-prompt="disableUnsavedPrompt"
                        :via-relationship="viaRelationship"
                        :key="panelRefreshKey"
                    >
                        <action-bar
                            :resource="resource"
                            :resource-name="resourceName"
                            :resource-id="resourceId"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            @confirm-delete="getResource"
                            @confirm-restore="getResource"
                            @action-executed="getResource"
                            :disable-unsaved-prompt="disableUnsavedPrompt"
                        />
                    </sales-order-panel>

                    <form
                        v-else
                        @submit="submitViaUpdateResource"
                        @change="onUpdateFormStatus"
                        autocomplete="off"
                        ref="form"
                    >
                        <form-panel
                            class="mb-6"
                            v-for="(subPanel, subIndex) in panelsWithFields"
                            :shown-via-new-relation-modal="false"
                            :panel="subPanel"
                            mode="form"
                            :name="subPanel.name"
                            :key="subPanel.name"
                            :resource="resource"
                            :resource-id="resourceId"
                            :resource-name="resourceName"
                            :fields="subPanel.fields"
                            :validation-errors="validationErrors"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                        >
                            <action-bar
                                v-if="!subIndex"
                                :resource="resource"
                                :resource-name="resourceName"
                                :resource-id="resourceId"
                                :via-resource="viaResource"
                                :via-resource-id="viaResourceId"
                                :via-relationship="viaRelationship"
                                @confirm-delete="getResource"
                                @confirm-restore="getResource"
                                @action-executed="getResource"
                                :disable-unsaved-prompt="disableUnsavedPrompt"
                            />
                        </form-panel>
                    </form>
                </section>
            </tab>
        </tabs>

        <!-- Update Button -->
        <div class="flex items-center" v-if="! isSalesOrder">
            <cancel-button @click="$router.back()"/>

            <progress-button
                dusk="update-button"
                :disabled="isWorking"
                :processing="wasSubmittedViaUpdateResource"
                @clicked="submitViaUpdateResource"
            >
                {{ progressButtonText }}
            </progress-button>
        </div>

        <portal to="modals">
            <unsaved-changes-modal
                v-if="openUnsavedChangesModal"
                @confirm="confirmExit"
                @close="cancelExit"
            />
        </portal>
    </loading-view>
</template>

<script>
    import {
        InteractsWithResourceInformation,
        InteractsWithQueryString,
        Minimum,
        HasCards,
    } from 'laravel-nova';

    import UnsavedChanges from '../mixins/UnsavedChanges';
    import ErrorNotification from '../mixins/ErrorNotification';
    import UnsavedChangesModal from '../components/modal/UnsavedChangesModal';
    import Update from '@nova/views/Update';
    import Tabs from '../components/tabs/Tabs';
    import Tab from '../components/tabs/Tab';
    import ProgressButton from '../components/ProgressButton';
    import ActionBar from '../components/detail/ActionBar';
    import SalesOrderPanel from '../components/form/SalesOrderPanel';
    import ReactiveFormView from '../mixins/ReactiveFormView';
    import FormActions from "../mixins/FormActions";

    export default {
        extends: Update,

        mixins: [
            UnsavedChanges,
            FormActions,
            ReactiveFormView,
            HasCards,
            InteractsWithResourceInformation,
            InteractsWithQueryString,
            ErrorNotification
        ],

        components: {
            ProgressButton, Tab, Tabs,
            UnsavedChangesModal, ActionBar, SalesOrderPanel
        },

        metaInfo() {
            return {
                title: `${this.headingTitle}`,
            }
        },

        data: _ => ({
            initialLoading: true,
            loading: true,
            resource: null,
            progressButtonText: '',
            refreshKey: 0
        }),

        // @copy Detail
        watch: {
            resourceId: function (newResourceId, oldResourceId) {
                if (newResourceId !== oldResourceId) {
                    this.initializeComponent()
                }
            },
        },

        async mounted() {
            this.progressButtonText = 'Update ' + this.singularName;

            Nova.$on('refresh-sales-order-panel', _ => {
                this.refreshKey++;
                this.getResource();
            });

            Nova.$on(['paginate', 'search'], value => {
                this.skipUnsavedPrompt(_ => this.updateQueryString(value));
            });
            // @copy Detail
            await this.initializeComponent();
            this.getFormActions();
        },

        // @copy Detail
        created() {
            document.addEventListener('keydown', this.handleKeydown);
        },

        // @copy Detail
        destroyed() {
            document.removeEventListener('keydown', this.handleKeydown);
            Nova.$off(['paginate', 'search', 'refresh-sales-order-panel']);
        },

        computed: {
            headingTitle() {
                return this.progressButtonText + ': ' + this.title;
            },

            panelRefreshKey() {
                return 'refresh-key-' + this.refreshKey;
            },

            isSalesOrder() {
                switch (this.resourceName) {
                    case 'opportunities':
                    case 'sales-orders':
                    case 'quote-templates': return true;
                    default: return false;
                }
            },

            // @overriding Update
            panelsWithFields() {
                let panels = _.map(this.panels, panel => {
                    return {
                        name: panel.name,
                        fields: _.filter(this.fields, field => field.panel === panel.name),
                    }
                });

                return _.filter(panels, panel => panel.fields.length);
            },

            // @override Update
            updateResourceFormData() {
                return _.tap(new FormData(), formData => {
                    _(this.fields).each(field => {
                        field.tabulated || field.listable || field.fill(formData)
                    })

                    formData.append('_method', 'PUT')
                    formData.append('_retrieved_at', this.lastRetrievedAt)
                })
            },

            // @copy Detail
            tabPanels() {
                if (!this.resource) return;

                let panels = {},
                    detailsPanel = this.panels[0].name,
                    fields = _.toArray(JSON.parse(JSON.stringify(this.resource.fields)));

                fields.forEach(field => {
                    if (field.listable)
                        panels[field.name] = this.createPanelForRelationship(field);
                    else if (panels[detailsPanel])
                        panels[detailsPanel].fields.push(field);
                    else panels[detailsPanel] = this.createPanelForField(field);
                });

                return _.toArray(panels);
            },

            // @copy Detail
            cardsEndpoint() {
                return `/nova-api/${this.resourceName}/cards`
            },

            // @copy Detail
            extraCardParams() {
                return {
                    resourceId: this.resourceId,
                }
            },
        },

        methods: {
            // @override Update
            async submitViaUpdateResource() {
                this.submittedViaUpdateResource = true;
                await this.updateResource();
            },

            // @copy Detail
            async getResource() {
                this.resource = null

                let urlPrefix = '/fifteen-group/nova-compact-reactive-ui',
                    urlSegments = `${this.resourceName}/${this.resourceId}/update-detail`;

                return Minimum(
                    Nova.request().get(`${urlPrefix}/${urlSegments}`, {
                        params: {
                            editing: true,
                            editMode: 'update'
                        }
                    }
                )).then(({data: {title, panels, resource}}) => {
                    this.title = title;
                    this.panels = panels;
                    this.resource = resource;
                    this.fields = resource.fields;
                    this.loading = false;
                    Nova.$emit('resource-loaded');
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

                    Nova.error(this.__('This resource no longer exists'))

                    this.$router.push({
                        name: 'index',
                        params: {resourceName: this.resourceName},
                    })
                });
            },

            // @override Update
            async updateResource() {
                this.isWorking = true;

                try {
                    await this.updateRequest();

                    Nova.success(
                        this.__(':resource was updated!', {resource: this.resource.title})
                    );

                    await this.updateLastRetrievedAtTimestamp();

                    let redirect;
                    // redirects to parent resource if created via resource
                    if (this.viaResource)
                        redirect = `/resources/${this.viaResource}/${this.viaResourceId}`;
                    else redirect = `/resources/${this.resourceName}`;

                    this.runActions(redirect, this.resourceId);
                } catch (error) {
                    this.submittedViaUpdateResource = false;

                    if (error.response.status === 422) {
                        this.showErrors(error.response.data.errors);
                    }

                    if (error.response.status === 409) {
                        Nova.error(
                            this.__(
                                'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                            )
                        )
                    }
                }

                this.submittedViaUpdateResource = false
                this.isWorking = false
            },

            // @copy Detail
            createPanelForRelationship(field) {
                return {
                    component: 'relationship-panel',
                    prefixComponent: true,
                    name: field.name,
                    fields: [field],
                }
            },

            // @copy Detail
            createPanelForField(field) {
                return _.tap(
                    _.find(this.panels, panel => panel.name === field.panel),
                    panel => panel.fields = [field]
                )
            },

            // @copy Detail
            async initializeComponent() {
                await this.getResource()
                this.initialLoading = false
            },

            // @copy Detail
            handleKeydown(e) {
                if (
                    this.resource.authorizedToUpdate &&
                    !e.ctrlKey &&
                    !e.altKey &&
                    !e.metaKey &&
                    !e.shiftKey &&
                    e.keyCode == 69 &&
                    e.target.tagName != 'INPUT' &&
                    e.target.tagName != 'TEXTAREA'
                ) {
                    this.$router.push({
                        name: 'edit',
                        params: {id: this.resource.id},
                    })
                }
            },

            slug(string) {
                return string.toString()
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, "-")
                    .replace(/[^\w\-]+/g, "")
                    .replace(/\-\-+/g, "-")
                    .replace(/^-+/, "")
                    .replace(/-+$/, "");
            },

            // @override Update
            getFields() {
            }
        }
    }
</script>
