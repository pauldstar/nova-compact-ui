<template>
    <default-field
        :field="field"
        :errors="errors"
        :full-width-content="true"
        :key="index"
        :show-help-text="showHelpText"
    >
        <template slot="field">
            <div class="rounded-lg" :class="{ disabled: isReadonly }">
                <trix
                    ref="trix"
                    name="trixman"
                    :value="value"
                    @change="handleChange"
                    @file-add="handleFileAdd"
                    @file-remove="handleFileRemove"
                    :with-files="field.withFiles"
                    v-bind="extraAttributes"
                    :disabled="isReadonly"
                    class="rounded-lg"
                    :class="{
                        'border-danger': hasError,
                        'trix-content--large': field.largeTextArea
                    }"
                />
            </div>
        </template>
    </default-field>
</template>

<script>
import ReactiveField from '../../mixins/ReactiveField';
import Trix from '../Trix';
import {FormField, HandlesValidationErrors} from 'laravel-nova'

export default {
    mixins: [HandlesValidationErrors, FormField, ReactiveField],
    components: {Trix},

    data: () => ({draftId: uuidv4(), index: 0}),

    beforeDestroy() {
        this.cleanUp()
    },

    mounted() {
        this.watches('value');

        Nova.$on(this.field.attribute + '-value', value => {
            this.value = value
            this.index++
        })
    },

    computed: {
        defaultAttributes() {
            return {
                placeholder: this.field.placeholder || this.field.name,
            }
        },

        extraAttributes() {
            const attrs = this.field.extraAttributes

            return {
                ...this.defaultAttributes,
                ...attrs,
            }
        },
    },

    methods: {
        setValue(val) {
            this.$refs.trix.updateValue(val);
        },

        /**
         * Update the field's internal value when it's value changes
         */
        handleChange(value) {
            this.value = value
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
            formData.append(this.field.attribute + 'DraftId', this.draftId)
        },

        /**
         * Initiate an attachement upload
         */
        handleFileAdd({attachment}) {
            if (attachment.file) {
                this.uploadAttachment(attachment)
            }
        },

        /**
         * Upload an attachment
         */
        uploadAttachment(attachment) {
            const data = new FormData()
            data.append('Content-Type', attachment.file.type)
            data.append('attachment', attachment.file)
            data.append('draftId', this.draftId)

            Nova.request()
                .post(
                    `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}`,
                    data,
                    {
                        onUploadProgress: function (progressEvent) {
                            attachment.setUploadProgress(
                                Math.round((progressEvent.loaded * 100) / progressEvent.total)
                            )
                        },
                    }
                )
                .then(({data: {url}}) => {
                    return attachment.setAttributes({
                        url: url,
                        href: url,
                    })
                })
        },

        /**
         * Remove an attachment from the server
         */
        handleFileRemove({attachment: {attachment}}) {
            Nova.request()
                .delete(
                    `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}`,
                    {
                        params: {
                            attachmentUrl: attachment.attributes.values.url,
                        },
                    }
                )
                .then(response => {
                })
                .catch(error => {
                })
        },

        /**
         * Purge pending attachments for the draft
         */
        cleanUp() {
            if (this.field.withFiles) {
                Nova.request()
                    .delete(
                        `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}/${this.draftId}`
                    )
                    .then(response => {
                    })
                    .catch(error => {
                    })
            }
        }
    }
}

function uuidv4()
{
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
        (
            c ^
            (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))
        ).toString(16)
    )
}
</script>
