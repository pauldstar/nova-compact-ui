<template>
    <loading-view :loading="initialLoading">
        <custom-header
            :heading-title="$metaInfo && $metaInfo.title"
            :via-relationship="viaRelationship"
            :resource="resource"
            :resource-id="resourceId"
            :resource-name="resourceName"
            mode="form"
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

        <!-- Resource Tabs -->

        <tabs>
            <tab :name="'Details'" :selected="true">
                <component
                    v-for="(panel, index) in normalPanels"
                    :key="panel.id"
                    :is="panel.component"
                    class="mb-6"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                    :panel="panel"
                >
                    <action-bar
                        v-if="!index"
                        :resource="resource"
                        :resource-name="resourceName"
                        :resource-id="resourceId"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        :via-relationship="viaRelationship"
                        @confirm-delete="getResource"
                        @confirm-restore="getResource"
                        @action-executed="getResource"
                    />
                </component>
            </tab>

            <tab
                v-for="(panel, index) in availablePanels"
                v-if="panel.component === 'relationship-panel'"
                :key="panel.id"
                :name="index ? panel.name : 'Details'"
            >
                <section
                    :dusk="resourceName + '-detail-component'"
                    :key="panel.id"
                    :id="slug(panel.name)"
                    :class="{'tab-panel': availablePanels.length > 1}"
                >
                    <component
                        :is="panel.component"
                        :resource-name="resourceName"
                        :resource-id="resourceId"
                        :resource="resource"
                        :panel="panel"
                    />
                </section>
            </tab>
        </tabs>
    </loading-view>
</template>

<script>
    import Detail from "@nova/views/Detail";
    import Tabs from "../components/tabs/Tabs";
    import Tab from "../components/tabs/Tab";
    import ActionBar from '../components/detail/ActionBar';
    import SalesOrderPanel from '../components/form/SalesOrderPanel';

    export default {
        extends: Detail,
        components: {Tab, Tabs, ActionBar, SalesOrderPanel},

        computed: {
            normalPanels() {
                return _.filter(this.availablePanels, panel => panel.component === 'panel');
            },

            isSalesOrder() {
                switch (this.resourceName) {
                    case 'opportunities':
                    case 'sales-orders':
                    case 'quote-templates': return true;
                    default: return false;
                }
            },
        },

        methods: {
            slug(string) {
                return string.toString()
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, "-")
                    .replace(/[^\w\-]+/g, "")
                    .replace(/\-\-+/g, "-")
                    .replace(/^-+/, "")
                    .replace(/-+$/, "");
            }
        }
    }
</script>
