<template>
    <div class="flex flex-wrap items-stretch w-full relative">
        <input
            v-if="field.inlineOnIndex"
            :type="field.type || 'text'"
            :class="`text-${field.textAlign} ${errorClasses} flex-shrink flex-grow flex-auto leading-normal w-px flex-1 form-control form-input form-input-bordered`"
            :placeholder="field.name"
            v-model="value"
            v-on="listener"
            :disabled="field.readonly"
        />
        <template v-else-if="hasValue">
            <div v-if="field.asHtml" v-html="field.value"></div>
            <span v-else class="whitespace-no-wrap">{{ field.value }}</span>
        </template>
        <p v-else>&mdash;</p>
    </div>
</template>

<script>
import TextField from '@nova/components/Index/TextField';
import InlineField from "../../mixins/InlineField";

export default {
    extends: TextField,
    mixins: [InlineField],
};
</script>
