<template>
    <div>
        <div v-if="canShowTabs" class="tabs">
            <ul :id="tabsId" class="px-0">
                <li
                    v-for="(tab, index) in tabs"
                    v-if="tab.name"
                    :key="'tab-' + index"
                    :data-firstindex="index"
                    class="tab relative p-3"
                    :class="tabClasses(tab)"
                    :aria-label="tab.hasError ? 'Name already exists' : undefined"
                    data-balloon-pos="up"
                    @click="tab.disabled || selectTab(index)"
                >
                    <span
                        :contenteditable="editable && tab.editable && tab.isActive"
                        v-text="tab.name"
                        @blur="updateTab($event, index, tab.name)"
                    />
                    <span
                        v-if="sortable && tab.sortable"
                        class="material-icons absolute tab__drag-indicator text-70"
                    >drag_indicator</span>
                </li>

                <li
                    v-if="editable"
                    class="tab p-3"
                    aria-label="Add New Tab"
                    data-balloon-pos="right"
                    :is-editable="false"
                    @click="$emit('add-tab')"
                    v-text="'+'"
                />
            </ul>
        </div>

        <div class="tabs-panels" :class="{' pt-4': canShowTabs}">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import Sortable from "sortablejs";

export default {
    props: {
        id: {type: String, default: ''},
        arrowed: {type: Boolean, default: false},
        editable: {type: Boolean, default: false},
        sortable: {type: Boolean, default: false}
    },

    data: _ => ({
        tabs: [],
        selectedTabIndex: 0
    }),

    created() {
        this.tabs = this.$children;

        this.tabs.forEach((tab, index) => {
            if (tab.isActive) this.selectedTabIndex = index;
        });
    },

    mounted() {
        if (this.sortable) {
            new Sortable(document.getElementById(this.tabsId), {
                draggable: '.tab--li',
                handle: '.tab__drag-indicator',
                onEnd: this.handleSort
            });
        }
    },

    computed: {
        canShowTabs() {
            return this.tabs.length > 1 || this.editable;
        },

        tabsId() {
            return `tabs__${this.id}`;
        }
    },

    methods: {
        handleSort(e) {
            let start = e.oldDraggableIndex,
                end = e.newDraggableIndex;

            if (start === end) return;

            let first = e.item.dataset.firstindex;

            this.$emit('sort-tab', {first, start, end});
        },

        tabClasses(tab) {
            return {
                'is-active': tab.isActive,
                'tab--disabled': tab.disabled,
                'tab--arrowed': this.arrowed,
                'border-2 border-danger tooltip-red': tab.hasError,
                'pr-8': this.sortable && tab.sortable,
                'pl-6': this.arrowed,
                'tab--li': tab.sortable
            };
        },

        selectTab(tabIndex = 0) {
            if (this.selectedTabIndex === tabIndex) return;

            this.tabs.forEach((tab, index) => {
                tab.isActive = index === tabIndex;
                if (tab.isActive) this.selectedTabIndex = index;
            });

            this.$emit('select-tab', tabIndex);
        },

        updateTab($event, index, name) {
            let newName = $event.target.innerText;
            if (newName === name) return void(this.hideError(index));
            if (newName === '') return void(this.deleteTab(index));

            if (this.isUniqueName(index, newName)) this.$emit('update-tab', index, newName);
            else this.showError(index);
        },

        deleteTab(index) {
            this.selectTab();
            this.$emit('delete-tab', index);
        },

        isUniqueName(index, name) {
            let isUnique = true;

            this.tabs.forEach((tab, tabIndex) => {
                if (index !== tabIndex) isUnique = tab.name !== name;
            });

            return isUnique;
        },

        showError(index) {
            this.tabs[index].hasError = true;
        },

        hideError(index) {
            this.tabs[index].hasError = false;
        }
    }
}
</script>

<style>
.tab--li.sortable-chosen {
    cursor: ew-resize;
}
.tab__drag-indicator {
    right: 5px;
    top: 9px;
}
</style>
