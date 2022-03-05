<template>
    <loading-card :loading="loading" class="px-6 py-4">
        <div class="flex mb-4">
            <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>

            <div v-if="helpText" class="absolute pin-r pin-b p-2 z-50">
                <tooltip trigger="click">
                    <icon
                        type="help"
                        viewBox="0 0 17 17"
                        height="16"
                        width="16"
                        class="cursor-pointer text-60 -mb-1"
                    />

                    <tooltip-content
                        slot="content"
                        v-html="helpText"
                        :max-width="helpWidth"
                    />
                </tooltip>
            </div>

            <select
                v-if="ranges.length > 0"
                @change="handleChange"
                class="select-box-sm ml-auto min-w-24 h-6 text-xs appearance-none bg-40 pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
            >
                <option
                    v-for="option in ranges"
                    :key="option.value"
                    :value="option.value"
                    :selected="selectedRangeKey == option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div class="bootstrap-wrapper">
            <div class="row">

                <p class="flex items-center text-2xl mb-2 col-6">
                    {{ formattedValue }}
                    <span v-if="suffix" class="ml-2 font-bold text-80">{{
                    formattedSuffix
                }}</span>
                </p>

                <p class="flex items-center flex-row-reverse text-80 font-bold col-6">
                    <span v-if="increaseOrDecrease != 0">
                        <span v-if="growthPercentage !== 0">
                        {{ growthPercentage }}%
                        {{ __(increaseOrDecreaseLabel) }}
                        </span>

                        <span v-else> {{ __('No Increase') }} </span>
                    </span>

                    <span v-else>
                        <span v-if="previous == '0' && value != '0'">
                        {{ __('No Prior Data') }}
                        </span>

                        <span v-if="value == '0' && previous != '0' && !zeroResult">
                        {{ __('No Current Data') }}
                        </span>

                        <span v-if="value == '0' && previous == '0' && !zeroResult">
                        {{ __('No Data') }}
                        </span>
                    </span>

                    <svg
                        v-if="increaseOrDecreaseLabel == 'Decrease'"
                        class="text-danger fill-current mr-2"
                        width="20"
                        height="12"
                    >
                        <path
                            d="M2 3a1 1 0 0 0-2 0v8a1 1 0 0 0 1 1h8a1 1 0 0 0 0-2H3.414L9 4.414l3.293 3.293a1 1 0 0 0 1.414 0l6-6A1 1 0 0 0 18.293.293L13 5.586 9.707 2.293a1 1 0 0 0-1.414 0L2 8.586V3z"
                        />
                    </svg>
                    <svg
                        v-if="increaseOrDecreaseLabel == 'Increase'"
                        class="rotate-180 text-success fill-current mr-2"
                        width="20"
                        height="12"
                    >
                        <path
                            d="M2 3a1 1 0 0 0-2 0v8a1 1 0 0 0 1 1h8a1 1 0 0 0 0-2H3.414L9 4.414l3.293 3.293a1 1 0 0 0 1.414 0l6-6A1 1 0 0 0 18.293.293L13 5.586 9.707 2.293a1 1 0 0 0-1.414 0L2 8.586V3z"
                        />
                    </svg>
                </p>
            </div>
        </div>

    </loading-card>
</template>

<script>
    import BaseValueMetric from "@nova/components/Metrics/Base/ValueMetric";

    export default {
        extends: BaseValueMetric
    }
</script>
