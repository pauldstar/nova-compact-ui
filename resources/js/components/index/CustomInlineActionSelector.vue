<template>
  <span>
    <select
        ref="selectBox"
        v-if="actions.length"
        class="rounded-sm select-box-sm mr-2 h-6 text-xs appearance-none bg-40 pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
        style="max-width: 90px"
        @change="handleSelectionChange"
        dusk="inline-action-select"
    >
      <option disabled selected>Actions</option>
      <option
          v-for="action in actions"
          :key="action.uriKey"
          :value="action.uriKey"
      >
        {{ action.name }}
      </option>
    </select>

      <!-- Action Confirmation Modal -->
    <portal to="modals">
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

      <component
          :is="actionResponseData.modal"
          @close="closeActionResponseModal"
          v-if="showActionResponseModal"
          :data="actionResponseData"
      />
    </portal>
  </span>
</template>

<script>
import InlineActionSelector from '@nova/components/Index/InlineActionSelector';
import ErrorNotification from '@ui/mixins/ErrorNotification';
import {Errors} from "laravel-nova";

export default {
    extends: InlineActionSelector,
    mixins: [ErrorNotification],

    methods: {
        // @override InlineActionSelector
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
    },
}
</script>
