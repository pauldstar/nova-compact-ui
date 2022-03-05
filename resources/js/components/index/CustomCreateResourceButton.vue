<template>
    <div v-if="shouldShowButtons">
        <!-- For ReactiveHasManyField -->
        <button
            v-if="showModal"
            :class="classes"
            @click.prevent="handleClick"
        >
            {{ __('Create :resource', { resource: singularName }) }}
        </button>

        <!-- Attach Related Models -->
        <router-link
            v-else-if="shouldShowAttachButton"
            dusk="attach-button"
            :class="classes"
            :to="{
                name: 'attach',
                params: {
                    resourceName: viaResource,
                    resourceId: viaResourceId,
                    relatedResourceName: resourceName,
                },
                query: {
                    viaRelationship: viaRelationship,
                    polymorphic: relationshipType === 'morphToMany' ? '1' : '0',
                },
            }"
        >
            <slot> {{ __('Attach :resource', { resource: singularName }) }}</slot>
        </router-link>

        <!-- Create Related Models -->
        <router-link
            v-else-if="shouldShowCreateButton"
            dusk="create-button"
            :class="classes"
            :to="{
                name: 'create',
                params: {
                    resourceName: resourceName,
                },
                query: {
                    viaResource: viaResource,
                    viaResourceId: viaResourceId,
                    viaRelationship: viaRelationship,
                },
            }"
        >
            {{ __('Create :resource', { resource: singularName }) }}
        </router-link>
    </div>
</template>

<script>
    import CreateResourceButton from '@nova/components/CreateResourceButton';

    export default {
        extends: CreateResourceButton,

        props: ['fieldName', 'showModal'],

        computed: {
            isForm() {
                return ['custom-create', 'custom-edit'].includes(this.$route.name);
            },
        },

        methods: {
            handleClick() {
                Nova.$emit(`${this.fieldName}:create-resource`, this.resourceName);
            }
        }
    }
</script>
