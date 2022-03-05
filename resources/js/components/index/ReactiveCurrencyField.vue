<template>
    <div :class="`text-${field.textAlign}`">
        <div
            v-if="field.inlineOnIndex"
            class="flex flex-wrap items-stretch w-full relative"
        >
            <div class="flex -mr-px">
                <span class="flex items-center leading-normal rounded rounded-r-none border border-r-0 border-60 px-3 whitespace-no-wrap bg-30 text-80 text-sm font-bold">
                    {{ field.currency }}
                </span>
            </div>

            <input
                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 rounded-l-none form-control form-input form-input-bordered text-right"
                :dusk="field.attribute"
                :placeholder="field.name"
                v-on="listener"
                v-model="value"
                :disabled="field.readonly"
            />
        </div>
        <template v-else-if="hasValue">
            <div v-if="field.asHtml" v-html="valueWithCurrency"></div>
            <span v-else class="whitespace-no-wrap">{{ valueWithCurrency }}</span>
        </template>
        <p v-else>&mdash;</p>
    </div>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';
import CurrencyField from '@nova/components/Index/CurrencyField';
import InlineField from "../../mixins/InlineField";

export default {
    extends: CurrencyField,
    mixins: [FormField, HandlesValidationErrors, InlineField],

    computed: {
        valueWithCurrency() {
            return this.field.currency + this.field.value;
        }
    }
};
</script>
