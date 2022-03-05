<template>
    <tr
        :dusk="resource['id'].value + '-row'"
        @click="selectRow"
        :class="{'cursor-pointer': isShownViaModal}"
    >
        <td v-if="draggable" class="row-drag-indicator cursor-pointer">
            <span
                class="material-icons"
                :class="isTableNotes ? 'text-white' : 'text-70'"
            >drag_indicator</span>
        </td>

        <!-- Resource Selection Checkbox -->
        <td class="w-16">
            <checkbox
                :data-testid="`${testId}-checkbox`"
                :dusk="`${resource['id'].value}-checkbox`"
                v-if="shouldShowCheckboxes"
                :checked="checked"
                @input="toggleSelection"
            />
        </td>

        <!-- Fields -->
        <td v-if="isTableNotes" :colspan="resource.fieldCount">
            <div class="text-white font-bold capitalize">
                <span class="whitespace-no-wrap">{{ resource.text }}</span>
            </div>
        </td>

        <template v-else>
            <td v-for="field in resource.fields">
                <component
                    :is="'index-' + field.component"
                    :class="`text-${field.textAlign}`"
                    :table-search="isShownViaModal"
                    :resource-name="resourceName"
                    :via-resource="viaResource"
                    :via-resource-id="viaResourceId"
                    :field="field"
                />
            </td>
        </template>

        <td class="td-fit text-right pr-6 align-middle" v-if="! isShownViaModal">
            <div class="inline-flex items-center">
                <!-- Actions Menu -->
                <inline-action-selector
                    v-if="availableActions.length > 0"
                    class="mr-3"
                    :resource="resource"
                    :resource-name="resourceName"
                    :actions="availableActions"
                    @actionExecuted="$emit('actionExecuted')"
                />

                <span v-if="resource.authorizedToUpdate" class="inline-flex">
                    <!-- Edit Pivot Button -->
                    <router-link
                        v-if="relationshipType == 'belongsToMany' || relationshipType == 'morphToMany'"
                        class="inline-flex cursor-pointer text-70 hover:text-primary mr-3"
                        :dusk="`${resource['id'].value}-edit-attached-button`"
                        v-tooltip.click="__('Edit Attached')"
                        :to="{
                        name: 'edit-attached',
                        params: {
                        resourceName: viaResource,
                        resourceId: viaResourceId,
                        relatedResourceName: resourceName,
                        relatedResourceId: resource['id'].value,
                        },
                        query: {
                        viaRelationship: viaRelationship,
                        },
                        }"
                    >
                        <icon type="edit"/>
                    </router-link>

                    <!-- Edit Resource Link -->
                    <template v-else>
                        <a
                            v-if="showModal"
                            href="#"
                            @click.prevent="handleClick"
                            class="inline-flex cursor-pointer mr-3"
                            :class="isTableNotes ? 'text-white' : 'text-70 hover:text-primary'"
                            :dusk="`${resource['id'].value}-edit-button`"
                            v-tooltip.click="__('Edit')"
                        >
                            <icon type="edit"/>
                        </a>
                        <router-link
                            v-else
                            class="inline-flex cursor-pointer text-70 hover:text-primary mr-3"
                            :dusk="`${resource['id'].value}-edit-button`"
                            :to="{
                          name: 'edit',
                          params: {
                            resourceName: resourceName,
                            resourceId: resource['id'].value,
                          },
                          query: {
                            viaResource: viaResource,
                            viaResourceId: viaResourceId,
                            viaRelationship: viaRelationship,
                          },
                        }"
                            v-tooltip.click="__('Edit')"
                        >
                            <icon type="edit"/>
                        </router-link>

                    </template>
                </span>

                <!-- View Resource Link -->
                <span v-else-if="resource.authorizedToView" class="inline-flex">
                    <router-link
                        :data-testid="`${testId}-view-button`"
                        :dusk="`${resource['id'].value}-view-button`"
                        class="cursor-pointer text-70 hover:text-primary mr-3 inline-flex items-center"
                        v-tooltip.click="__('View')"
                        :to="{
                            name: 'detail',
                            params: {
                                resourceName: resourceName,
                                resourceId: resource['id'].value,
                            },
                        }"
                    >
                        <icon type="view" width="22" height="18" view-box="0 0 22 16"/>
                    </router-link>
                </span>

                <!-- Delete Resource Link -->
                <button
                    :data-testid="`${testId}-delete-button`"
                    :dusk="`${resource['id'].value}-delete-button`"
                    class="inline-flex appearance-none cursor-pointer mr-3"
                    :class="isTableNotes ? 'text-white' : 'text-70 hover:text-primary'"
                    v-tooltip.click="__(viaManyToMany ? 'Detach' : 'Delete')"
                    v-if="
            resource.authorizedToDelete &&
              (!resource.softDeleted || viaManyToMany)
          "
                    @click.prevent="openDeleteModal"
                >
                    <icon/>
                </button>

                <!-- Restore Resource Link -->
                <button
                    :dusk="`${resource['id'].value}-restore-button`"
                    class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
                    v-if="
            resource.authorizedToRestore &&
              resource.softDeleted &&
              !viaManyToMany
          "
                    v-tooltip.click="__('Restore')"
                    @click.prevent="openRestoreModal"
                >
                    <icon type="restore" with="20" height="21"/>
                </button>

                <portal
                    to="modals"
                    transition="fade-transition"
                    v-if="deleteModalOpen || restoreModalOpen"
                >
                    <delete-resource-modal
                        v-if="deleteModalOpen"
                        @confirm="confirmDelete"
                        @close="closeDeleteModal"
                        :mode="viaManyToMany ? 'detach' : 'delete'"
                    >
                        <div slot-scope="{ uppercaseMode, mode }" class="p-8">
                            <heading :level="2" class="mb-6">{{
                                __(uppercaseMode + ' Resource')
                                }}
                            </heading>
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to ' + mode + ' this resource?') }}
                            </p>
                        </div>
                    </delete-resource-modal>

                    <restore-resource-modal
                        v-if="restoreModalOpen"
                        @confirm="confirmRestore"
                        @close="closeRestoreModal"
                    >
                        <div class="p-8">
                            <heading :level="2" class="mb-6">{{
                                __('Restore Resource')
                                }}
                            </heading>
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to restore this resource?') }}
                            </p>
                        </div>
                    </restore-resource-modal>
                </portal>
            </div>
        </td>
    </tr>
</template>

<script>
    import ResourceTableRow from '@nova/components/Index/ResourceTableRow';

    export default {
        extends: ResourceTableRow,
        props: ['mode', 'fieldName', 'showModal', 'draggable'],

        computed: {
            isTableNotes() {
                return this.resourceName === 'table-notes';
            },

            isShownViaModal() {
                return this.mode === 'modal';
            }
        },

        methods: {
            handleClick() {
                Nova.$emit(
                    `${this.fieldName}:edit-resource`,
                    this.resourceName,
                    this.resource['id'].value
                );
            },

            selectRow() {
                if (this.isShownViaModal) {
                    Nova.$emit(`${this.fieldName}:select-resource`, {
                        display: this.resource.title,
                        value: this.resource.id.value
                    });
                }
            }
        }
    }
</script>
