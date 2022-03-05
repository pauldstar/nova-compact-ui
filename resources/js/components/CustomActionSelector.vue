<template>
    <div>
        <div
            v-if="actions.length > 0 || availablePivotActions.length > 0"
            class="flex items-center"
        >
            <select
                ref="selectBox"
                data-testid="action-select"
                dusk="action-select"
                v-model="selectBoxValue"
                class="form-control form-select"
                @change="handleSelectionChange"
            >
                <option value="" disabled selected>Select Action</option>

                <optgroup
                    v-if="actions.length > 0"
                    :label="resourceInformation.singularLabel"
                >
                    <option
                        v-for="action in actions"
                        :value="action.uriKey"
                        :key="action.urikey"
                        :selected="action.uriKey == selectedActionKey"
                    >
                        {{ action.name }}
                    </option>
                </optgroup>

                <optgroup
                    class="pivot-option-group"
                    :label="pivotName"
                    v-if="availablePivotActions.length > 0"
                >
                    <option
                        v-for="action in availablePivotActions"
                        :value="action.uriKey"
                        :key="action.urikey"
                        :selected="action.uriKey == selectedActionKey"
                    >
                        {{ action.name }}
                    </option>
                </optgroup>
            </select>
        </div>

        <!-- Action Confirmation Modal -->
        <portal to="modals" transition="fade-transition">
            <component
                v-if="confirmActionModalOpened"
                class="text-left"
                :is="selectedAction.component"
                :working="working"
                :selected-resources="selectedResources"
                :resource-name="resourceName"
                :action="selectedAction"
                :errors="errors"
                @confirm="executeAction"
                @close="closeConfirmationModal"
            />
        </portal>
    </div>
</template>

<script>
import ActionSelector from '@nova/components/ActionSelector';
import ErrorNotification from '@ui/mixins/ErrorNotification';
import {Errors} from "laravel-nova";

export default {
    extends: ActionSelector,
    mixins: [ErrorNotification],

    data: _ => ({
        selectBoxValue: ''
    }),

    computed: {
        availablePivotActions() {
            return this.pivotActions ? _(this.pivotActions.actions).filter(action => {
                if (this.selectedResources != 'all') {
                    return true
                }

                return action.availableForEntireResource
            }).value() : {}
        }
    },

    methods: {
        handleSelectionChange(e) {
            this.selectBoxValue = '';
            this.selectedActionKey = e.target.value;
            this.determineActionStrategy();
        },

        // @override ActionSelector
        executeAction() {
            this.working = true

            Nova.request({
                method: 'post',
                url: this.endpoint || `/nova-api/${this.resourceName}/action`,
                params: this.actionRequestQueryString,
                data: this.actionFormData(),
            }).then(response => {
                this.confirmActionModalOpened = false
                this.handleActionResponse(response.data)
                this.working = false
            }).catch(error => {
                this.working = false

                if (error.response.status === 422) {
                    this.showErrors(error.response.data.errors)
                    this.errors = new Errors(error.response.data.errors)
                }
            })
        },
    }
}
</script>
