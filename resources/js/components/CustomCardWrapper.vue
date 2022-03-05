<template>
  <div
    class="px-3 mb-6"
    :class="widthClass"
    :key="`${card.component}.${card.uriKey}`"
  >
    <component
      :is="card.component"
      :card="card"
      :resource="resource"
      :resourceName="resourceName"
      :resourceId="resourceId"
      :lens="lens"
    />
  </div>
</template>

<script>
    import CardWrapper from "@nova/components/CardWrapper";

    export default {
        extends: CardWrapper,

        computed: {
            widthClass() {
                return this.size === 'large' ? 'w-full' : 'w-1/4'
            },
        },
    }
</script>
