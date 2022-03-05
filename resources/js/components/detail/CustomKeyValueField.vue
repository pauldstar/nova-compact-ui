<template>
  <panel-item :field="field">
    <template slot="value">
      <KeyValueTable
        v-if="theData.length > 0"
        :edit-mode="false"
        class="overflow-hidden"
      >
        <CustomKeyValueHeader
          :key-label="field.keyLabel"
          :value-label="field.valueLabel"
        />

        <div class="bg-white overflow-hidden key-value-items">
          <CustomKeyValueItem
            v-for="item in theData"
            :item="item"
            :disabled="true"
            :key="item.key"
          />
        </div>
      </KeyValueTable>
    </template>
  </panel-item>
</template>

<script>
import CustomKeyValueHeader from "@ui/components/form/ReactiveKeyValueField/CustomKeyValueHeader";
import CustomKeyValueItem from "@ui/components/form/ReactiveKeyValueField/CustomKeyValueItem";
import KeyValueField from "@nova/components/Detail/KeyValueField";

export default {
    extends: KeyValueField,
    components: {CustomKeyValueItem, CustomKeyValueHeader}
}
</script>
