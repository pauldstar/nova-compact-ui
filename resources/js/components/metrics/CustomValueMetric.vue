<template>
    <CustomBaseValueMetric
        @selected="handleRangeSelected"
        :title="card.name"
        :help-text="card.helpText"
        :help-width="card.helpWidth"
        :previous="previous"
        :value="value"
        :ranges="card.ranges"
        :format="format"
        :prefix="prefix"
        :suffix="suffix"
        :suffix-inflection="suffixInflection"
        :selected-range-key="selectedRangeKey"
        :loading="loading"
        :zero-result="zeroResult"
    />
</template>

<script>
    import ValueMetric from "@nova/components/Metrics/ValueMetric";
    import CustomBaseValueMetric from "./CustomBaseValueMetric";

    export default {
        extends: ValueMetric,
        components: {CustomBaseValueMetric}
    }
</script>
