<template>
  <div>
    <default-field :field="field" :show-errors="false" :field-name="fieldName">
      <select
        v-if="hasMorphToTypes"
        :disabled="isLocked || isReadonly"
        :data-testid="`${field.attribute}-type`"
        :dusk="`${field.attribute}-type`"
        slot="field"
        :value="resourceType"
        @change="refreshResourcesForTypeChange"
        class="block w-full form-control form-input form-input-bordered form-select"
      >
        <option value="" selected :disabled="!field.nullable">
          {{ __('Choose Type') }}
        </option>

        <option
          v-for="option in field.morphToTypes"
          :key="option.value"
          :value="option.value"
          :selected="resourceType == option.value"
        >
          {{ option.singularLabel }}
        </option>
      </select>

      <label v-else slot="field" class="flex items-center select-none mt-3">
        {{ __('There are no available options for this resource.') }}
      </label>
    </default-field>

    <default-field
      :field="field"
      :errors="errors"
      :show-help-text="false"
      :field-name="fieldTypeName"
      v-if="hasMorphToTypes"
    >
      <template slot="field">
        <div class="flex items-center">
          <search-input
            class="w-full"
            v-if="isSearchable && !isLocked && !isReadonly"
            :data-testid="`${field.attribute}-search-input`"
            :disabled="!resourceType || isLocked || isReadonly"
            @input="performSearch"
            @clear="clearSelection"
            @selected="selectResource"
            :value="selectedResource"
            :data="availableResources"
            :clearable="field.nullable"
            trackBy="value"
          >
            <div
              slot="default"
              v-if="selectedResource"
              class="flex items-center"
            >
              <div v-if="selectedResource.avatar" class="mr-3">
                <img
                  :src="selectedResource.avatar"
                  class="w-8 h-8 rounded-full block"
                />
              </div>

              {{ selectedResource.display }}
            </div>

            <div
              slot="option"
              slot-scope="{ option, selected }"
              class="flex items-center"
            >
              <div v-if="option.avatar" class="mr-3">
                <img :src="option.avatar" class="w-8 h-8 rounded-full block" />
              </div>

              <div>
                <div
                  class="text-sm font-semibold leading-5 text-90"
                  :class="{ 'text-white': selected }"
                >
                  {{ option.display }}
                </div>

                <div
                  v-if="field.withSubtitles"
                  class="mt-1 text-xs font-semibold leading-5 text-80"
                  :class="{ 'text-white': selected }"
                >
                  <span v-if="option.subtitle">{{ option.subtitle }}</span>
                  <span v-else>{{ __('No additional information...') }}</span>
                </div>
              </div>
            </div>
          </search-input>

          <select-control
            v-if="!isSearchable || isLocked"
            class="form-control form-select w-full"
            :class="{ 'border-danger': hasError }"
            :dusk="`${field.attribute}-select`"
            @change="selectResourceFromSelectControl"
            :disabled="!resourceType || isLocked || isReadonly"
            :options="availableResources"
            :selected="selectedResourceId"
            label="display"
          >
            <option
              value=""
              :disabled="!field.nullable"
              :selected="selectedResourceId == ''"
            >
              {{ __('Choose') }} {{ fieldTypeName }}
            </option>
          </select-control>

          <create-relation-button
            v-if="canShowNewRelationModal"
            @click="openRelationModal"
            class="ml-1"
          />
        </div>

        <portal to="modals" transition="fade-transition">
          <create-relation-modal
            v-if="relationModalOpen && !shownViaNewRelationModal"
            @set-resource="handleSetResource"
            @cancelled-create="closeRelationModal"
            :resource-name="resourceType"
            :via-relationship="viaRelationship"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            width="800"
          />
        </portal>

        <!-- Trashed State -->
        <div v-if="shouldShowTrashed">
          <checkbox-with-label
            :dusk="field.attribute + '-with-trashed-checkbox'"
            :checked="withTrashed"
            @input="toggleWithTrashed"
          >
            {{ __('With Trashed') }}
          </checkbox-with-label>
        </div>
      </template>
    </default-field>
  </div>
</template>

<script>
import MorphToField from '@nova/components/Form/MorphToField';

export default {
  extends: MorphToField
}
</script>
