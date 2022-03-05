<template>
    <div class="flex border-b border-40">
        <div class="col-4 py-2">
            <slot>
                <h4 class="font-normal text-80">{{ label }}</h4>
            </slot>
        </div>
        <div class="col-8 py-2 break-words">
            <slot name="value">
                <p v-if="fieldValue && !shouldDisplayAsHtml" class="text-90">
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
    import PanelItem from '@nova/components/PanelItem';

    export default {
        extends: PanelItem
    }
</script>
