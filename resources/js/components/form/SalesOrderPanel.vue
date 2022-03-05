<template>
    <div>
        <card>

            <slot/>

            <form-panel
                v-if="isEdit"
                class="mb-6"
                :panel="paymentSummaryPanel"
                :shown-via-new-relation-modal="false"
                mode="form"
                :name="paymentSummaryPanel.name"
                :key="paymentSummaryPanel.name"
                :resource-id="itemsResourceId"
                :resource-name="resourceName"
                :fields="paymentSummaryPanel.fields"
                :validation-errors="validationErrors"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
            />
        </card>

        <tabs
            :arrowed="true"
            @select-tab="selectStep($event)"
            ref="tabs"
        >
            <tab name="Quote Setup" :selected="step === setupStep">
                <form
                    @change="$emit('form-change')"
                    autocomplete="off"
                    ref="form"
                >
                    <form-panel
                        class="mb-6"
                        v-for="(panel, index) in quoteSetupPanels"
                        :key="index"
                        :panel="panel"
                        :shown-via-new-relation-modal="false"
                        mode="form"
                        :name="panel.name"
                        :resource-id="itemsResourceId"
                        :resource-name="resourceName"
                        :fields="panel.fields"
                        :validation-errors="validationErrors"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        :via-relationship="viaRelationship"
                    />
                </form>
            </tab>

            <tab
                name="Prepare Content"
                :selected="step === contentStep"
                :disabled="! enabledSteps.contentStep"
            >
                <form-relationship-panels
                    v-if="isEdit"
                    mode="form"
                    :delete-unsaved-changes-on-load="false"
                    :panel="prepareContentPanel"
                    :fields="prepareContentPanel.fields"
                    :resource-id="itemsResourceId"
                    :resource-name="resourceName"
                />
            </tab>

            <tab
                name="Media"
                :selected="step === mediaStep"
                :disabled="! enabledSteps.mediaStep"
            >
                <card
                    v-if="isEdit"
                    class="bootstrap-wrapper p-6 mb-6"
                >
                    <div class="row">
                        <div class="col-6">
                            <tabs>
                                <tab
                                    v-for="(title, type) in mediaTypes"
                                    :key="type"
                                    :name="title"
                                    :selected="type === 'documents'"
                                    class="h-full"
                                >
                                    <card class="card--bordered min-h-textarea p-4 pt-0" style="min-height: 5.2rem">
                                        <div class="row" :id=" type + '-container' ">
                                            <div
                                                v-for="file in files(type)"
                                                v-if="! file.selected"
                                                :data-fileid="file.id"
                                                class="col-3 mt-4"
                                                :class=" type + '-item' "
                                            >
                                                <card class="card--dotted p-3 text-center cursor-grab">
                                                    <span class="material-icons text-80 text-3xl text-danger">
                                                        {{ type === 'documents' ? 'insert_drive_file' : 'play_circle' }}
                                                    </span>
                                                    <p class="text-80 text-sm capitalize mt-1 font-semibold">
                                                        {{ file.name }}</p>
                                                </card>
                                            </div>
                                        </div>
                                    </card>
                                </tab>
                            </tabs>
                        </div>
                        <div v-for="(title, type) in mediaTypes" class="col-3">
                            <card class="card--bordered p-6 h-full">
                                <p class="text-80 capitalize mb-1 font-semibold inline-block">Attached {{ type }}</p>

                                <card
                                    class="card--dotted mt-6 p-4 pt-0 min-h-input"
                                    :id=" 'attached-' + type + '-container' "
                                >
                                    <div
                                        v-for="file in selectedFiles(type)"
                                        :key="file.name"
                                        :data-fileid="file.id"
                                        class="mt-4"
                                        :class="[
                                            type + '-item',
                                            isQuotePdf(file) ? 'ignore-elements' : ''
                                        ]"
                                    >
                                        <card class="card--dotted p-3 text-center cursor-grab">
                                            <span class="material-icons text-80 text-3xl text-danger">
                                                {{ type === 'documents' ? 'insert_drive_file' : 'play_circle' }}
                                            </span>
                                            <p class="text-80 text-sm capitalize mt-1 font-semibold">{{
                                                    file.name
                                                }}</p>
                                        </card>
                                    </div>
                                </card>
                            </card>
                        </div>
                    </div>
                </card>

                <form
                    @change="$emit('form-change')"
                    autocomplete="off"
                    ref="form"
                >
                    <form-panel
                        class="mb-6"
                        :panel="mediaPanel"
                        :shown-via-new-relation-modal="false"
                        mode="form"
                        :name="mediaPanel.name"
                        :key="mediaPanel.name"
                        :resource-id="itemsResourceId"
                        :resource-name="resourceName"
                        :fields="mediaPanel.fields"
                        :validation-errors="validationErrors"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        :via-relationship="viaRelationship"
                    />
                </form>
            </tab>

            <tab name="Email" :selected="step === emailStep" :disabled="! enabledSteps.emailStep">
                <form
                    @change="$emit('form-change')"
                    autocomplete="off"
                    ref="form"
                >
                    <form-panel
                        class="mb-6"
                        :panel="emailPanel"
                        :shown-via-new-relation-modal="false"
                        mode="form"
                        :name="emailPanel.name"
                        :key="emailPanel.name"
                        :resource-id="itemsResourceId"
                        :resource-name="resourceName"
                        :fields="emailPanel.fields"
                        :validation-errors="validationErrors"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        :via-relationship="viaRelationship"
                    />
                </form>
            </tab>
        </tabs>

        <progress-button
            v-if="step === emailStep"
            dusk="email-button"
            class="float-right ml-2"
            :disabled="isWorking"
            :processing="isPublishing"
            @clicked="emailOrder"
        >Send
        </progress-button>

        <progress-button
            dusk="update-button"
            class="float-right"
            :disabled="isWorking"
            :processing="wasSubmittedViaUpdateResource"
            @clicked="selectStep"
        >{{ progressButtonText }}
        </progress-button>
    </div>
</template>

<script>
import FormRelationshipPanels from '@ui/components/form/FormRelationshipPanels';
import Tab from '@ui/components/tabs/Tab';
import ResourceCrud from '@ui/mixins/ResourceCrud';
import Tabs from '@ui/components/tabs/Tabs';
import ProgressButton from '@ui/components/ProgressButton';
import Sortable from 'sortablejs';
import {Minimum} from 'laravel-nova';

export default {
    mixins: [ResourceCrud],

    components: {Tabs, Tab, FormRelationshipPanels, ProgressButton},

    props: {
        panels: {type: Array, required: true},
        fields: {type: Array, required: true},
        resourceName: {type: String, required: true},
        resourceId: {type: [Number, String]},
        viaResource: {type: String},
        viaResourceId: {type: [Number, String]},
        viaRelationship: {type: String},
        disableUnsavedPrompt: {type: Function},
    },

    data: _ => ({
        newResourceId: null,
        progressButtonText: 'Save & Next',
        isWorking: false,
        isPublishing: false,
        mediaTypes: {documents: 'PDF Documents', videos: 'Videos'},
        step: 0,
        submittedViaUpdateResource: false,
        setupStep: 0,
        contentStep: 1,
        mediaStep: 2,
        emailStep: 3,
        documents: [],
        videos: [],
    }),

    async mounted() {
        this.disableUnsavedPrompt();

        if (this.isEdit) {
            this.initSortableMedia();
            await this.getFiles();
        }
    },

    computed: {
        isEdit() {
            return this.$route.name === 'custom-edit';
        },

        enabledSteps() {
            return {
                contentStep: this.itemsResourceId,
                mediaStep: this.itemsResourceId && this.hasItems,
                emailStep: this.itemsResourceId && this.hasItems
            }
        },

        paymentSummaryPanel() {
            return _.find(this.panels, ['name', 'payment-summary']);
        },

        quoteSetupPanels() {
            return _.filter(this.panels, p =>
                ['customer-setup', 'delivery'].includes(p.name)
            );
        },

        prepareContentPanel() {
            return _.find(this.panels, ['name', 'prepare-content']);
        },

        mediaPanel() {
            return _.find(this.panels, ['name', 'media']);
        },

        emailPanel() {
            return _.find(this.panels, ['name', 'email']);
        },

        hasItems() {
            return this.prepareContentPanel.fields[0].value.length;
        },

        wasSubmittedViaUpdateResource() {
            return this.isWorking && this.submittedViaUpdateResource
        },

        itemsResourceId() {
            return this.newResourceId || this.resourceId;
        },

        sortedSelectedDocuments() {
            return _.sortBy(
                _.filter(this.documents, doc => doc.selected), 'position'
            );
        },

        sortedSelectedVideos() {
            return _.sortBy(
                _.filter(this.videos, vid => vid.selected), 'position'
            );
        }
    },

    methods: {
        initSortableMedia() {
            for (let type in this.mediaTypes) {
                new Sortable(document.getElementById(type + '-container'), {
                    draggable: '.' + type + '-item',
                    group: type,
                    sort: false,
                    ghostClass: 'col-3',
                    onEnd: e => e.to.id === type + '-container'
                        ? e.item.classList.add('col-3')
                        : e.item.classList.remove('col-3'),
                    onAdd: e => e.item.classList.add('col-3')
                });

                new Sortable(document.getElementById('attached-' + type + '-container'), {
                    draggable: '.' + type + '-item',
                    group: type,
                    filter: '.ignore-elements',
                    onAdd: this.attachFile,
                    onUpdate: this.amendFile,
                    onRemove: this.detachFile
                });
            }
        },

        async attachFile(e) {
            let resourceId = e.item.dataset.fileid;

            await this.attachResource(
                'files', resourceId, 'sales-orders', this.itemsResourceId, 'salesOrders',
                {position: e.newIndex + 1}
            );
        },

        async amendFile(e) {
            let resourceId = e.item.dataset.fileid;

            await this.amendAttachedResource(
                'files', resourceId, 'sales-orders', this.itemsResourceId, 'salesOrders',
                {position: e.newIndex + 1}
            );
        },

        async detachFile(e) {
            let resourceId = e.item.dataset.fileid;

            await this.detachResource(
                'files', resourceId, 'sales-orders', this.itemsResourceId, 'salesOrders',
            );
        },

        async getFiles() {
            return Minimum(Nova.request().get('/files/sales-orders'), 300)
                .then(({data}) => {
                    this.documents = [];
                    this.videos = [];

                    data.forEach(file => {
                        file = this.selectedFileWithOrder(file);

                        if (file.type === 'PDF') {
                            this.documents.push(file);
                        } else if (file.type === 'Video') {
                            this.videos.push(file);
                        }
                    });
                })
        },

        files(type) {
            switch (type) {
                case 'documents':
                    return this.documents;
                case 'videos':
                    return this.videos;
                default:
                    return null;
            }
        },

        selectedFiles(type) {
            switch (type) {
                case 'documents':
                    return this.sortedSelectedDocuments;
                case 'videos':
                    return this.sortedSelectedVideos;
                default:
                    return null;
            }
        },

        selectedFileWithOrder(file) {
            file.sales_orders.forEach(order => {
                // keep == comparison
                if (this.itemsResourceId == order.id) {
                    file.selected = true;
                    file.position = order.pivot.position;
                }
            });

            if (!file.selected && this.isQuotePdf(file)) {
                file.selected = true;
            }

            return file;
        },

        isQuotePdf(file) {
            return file.name === 'current-quote' && file.type === 'PDF';
        },

        // @copy UpdateDetail
        async submitViaUpdateResource() {
            this.submittedViaUpdateResource = true;
            this.isWorking = true;

            let resourceId;

            if (this.itemsResourceId) {
                resourceId = await this.amendResource(
                    this.resourceName, this.itemsResourceId, this.fields
                );
            } else resourceId = await this.addResource(this.resourceName, this.fields);

            this.submittedViaUpdateResource = false
            this.isWorking = false

            return resourceId;
        },

        async selectStep(step) {
            let buttonClick = step === undefined;
            this.step = buttonClick ? this.step : step;

            switch (this.step) {
                case this.setupStep:
                case this.mediaStep: { // setup
                    if (buttonClick) {
                        let resourceId = await this.submitViaUpdateResource();

                        if (resourceId) {
                            this.newResourceId = resourceId;

                            this.$route.name === 'custom-create' && this.$router.push({
                                name: 'custom-edit',
                                params: {
                                    resourceName: this.resourceName,
                                    resourceId: resourceId
                                },
                            })
                        } else return;
                    }
                    break;
                }

                case this.emailStep: { // email
                    if (buttonClick) {
                        let resourceId = await this.submitViaUpdateResource();

                        if (!resourceId) return;

                        this.disableUnsavedPrompt();

                        this.$router.push({
                            name: 'edit',
                            params: {
                                resourceName: this.resourceName,
                                resourceId: this.itemsResourceId
                            }
                        })
                    }
                }
            }

            if (buttonClick && this.step < this.emailStep) {
                this.step++;
                this.$refs.tabs.selectTab(this.step);
            }

            this.setProgressText();
        },

        setProgressText() {
            switch (this.step) {
                case this.setupStep:
                case this.mediaStep:
                    return void (this.progressButtonText = 'Save & Next');

                case this.contentStep:
                    return void (this.progressButtonText = 'Next');

                case this.emailStep:
                    return void (this.progressButtonText = 'Save');
            }
        },

        async emailOrder() {
            let resourceId = await this.submitViaUpdateResource();
            if (resourceId) Nova.$emit('run-action-bar', 'mail-order');
        }
    }
}
</script>
