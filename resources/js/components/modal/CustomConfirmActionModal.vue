<template>
    <modal
        data-testid="confirm-action-modal"
        tabindex="-1"
        role="dialog"
        @modal-close="handleClose"
        :classWhitelist="[
            'flatpickr-current-month',
            'flatpickr-next-month',
            'flatpickr-prev-month',
            'flatpickr-weekday',
            'flatpickr-weekdays',
            'flatpickr-calendar',
        ]"
    >
        <form
            autocomplete="off"
            @keydown="handleKeydown"
            @submit.prevent.stop="handleConfirm"
            class="bg-white rounded-lg shadow-lg overflow-hidden"
            :class="{
                'w-action-fields': action.fields.length > 0,
                'w-action': action.fields.length == 0,
            }"
        >
            <div>
                <heading :level="2" class="border-b border-40 py-8 px-8">
                    {{ action.name }}
                </heading>

                <p v-if="action.fields.length === 0" class="text-80 px-8 my-8">
                    {{ action.confirmText }}
                </p>

                <div v-else class="bootstrap-wrapper">
                    <!-- Validation Errors -->
                    <validation-errors :errors="errors"/>

                    <!-- Action Fields -->
                    <div
                        class="action row px-4"
                        v-for="field in action.fields"
                        :key="field.attribute"
                    >
                        <component
                            :is="'form-' + field.component"
                            :errors="errors"
                            :resource-name="resourceName"
                            :field="field"
                            class="col-12"
                        />
                    </div>
                </div>
            </div>

            <div class="bg-30 px-6 py-3 flex">
                <div class="flex items-center ml-auto">
                    <button
                        dusk="cancel-action-button"
                        type="button"
                        @click.prevent="handleClose"
                        class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6"
                    >
                        {{ action.cancelButtonText }}
                    </button>

                    <button
                        ref="runButton"
                        dusk="confirm-action-button"
                        :disabled="working"
                        type="submit"
                        class="btn btn-default"
                        :class="{
              'btn-primary': !action.destructive,
              'btn-danger': action.destructive,
            }"
                    >
                        <loader v-if="working" width="30"></loader>
                        <span v-else>{{ action.confirmButtonText }}</span>
                    </button>
                </div>
            </div>
        </form>
    </modal>
</template>

<script>
    import ConfirmActionModal from '@nova/components/Modals/ConfirmActionModal';

    export default {
        extends: ConfirmActionModal
    }
</script>
