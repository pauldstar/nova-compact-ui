<template>
  <div class="bg-primary flex border-b border-t border-40 px-2">
    <div class="w-full py-4 px-2">
      <slot name="value">
        <p v-if="fieldValue && !shouldDisplayAsHtml" class="text-white font-bold capitalize">
          {{ fieldValue }}
        </p>
        <div
          v-else-if="fieldValue && shouldDisplayAsHtml"
          v-html="field.value"
        ></div>
        <p v-else>&mdash;</p>
      </slot>
    </div>
  </div>
</template>

<script>
import HeadingField from '@nova/components/Detail/HeadingField';

export default {
  extends: HeadingField
}
</script>
