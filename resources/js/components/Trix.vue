<template>
    <trix-editor
        ref="theEditor"
        @keydown.stop
        @trix-change="handleChange"
        @trix-initialize="initialize"
        @trix-attachment-add="handleAddFile"
        @trix-attachment-remove="handleRemoveFile"
        @trix-file-accept="handleFileAccept"
        :value="value"
        :placeholder="placeholder"
        class="trix-content"
    />
</template>

<script>
export default {
    name: 'trix-vue',

    props: {
        name: {type: String},
        value: {type: String},
        placeholder: {type: String},
        withFiles: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
    },

    methods: {
        initialize() {
            this.updateValue();

            if (this.disabled) {
                this.$refs.theEditor.setAttribute('contenteditable', false)
            }
        },

        updateValue(value) {
            let editor = this.$refs.theEditor.editor;
            editor.setSelectedRange([0, editor.getDocument().getLength()]);
            editor.insertHTML(value || this.value);
        },

        handleChange() {
            this.$emit('change', this.$refs.theEditor.value);
        },

        handleFileAccept(e) {
            if (!this.withFiles) {
                e.preventDefault()
            }
        },

        handleAddFile(event) {
            this.$emit('file-add', event)
        },

        handleRemoveFile(event) {
            this.$emit('file-remove', event)
        },
    },
}
</script>
