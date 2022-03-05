export default {
    beforeMount() {
        window.addEventListener('beforeunload', this.unsavedPrompt)
    },

    beforeDestroy() {
        window.removeEventListener('beforeunload', this.unsavedPrompt);
    },

    beforeRouteLeave(to, from, next) {
        if (this.shouldSkipUnsavedPrompt) {
            next();
        } else {
            this.openUnsavedChangesModal = true;
            this.nextRoute = next;
        }
    },

    mounted() {
        Nova.$on('field-status', e => {
            if (e.changed) {
                this.dirtyFields.indexOf(e.field) === -1 && this.dirtyFields.push(e.field);
                if (e.triggerFormChange) this.dirtyForm = true;
            } else this.dirtyFields = this.dirtyFields.filter(field => field !== e.field);
        });
    },

    data: _ => ({
        skipPrompt: false,
        openUnsavedChangesModal: false,
        nextRoute: null,
        dirtyFields: [],
        dirtyForm: false
    }),

    computed: {
        shouldSkipUnsavedPrompt() {
            return this.skipPrompt
                || this.submissionRedirect
                || !this.hasChanges()
                || this.nextRoute;
        },
    },

    methods: {
        hasChanges() {
            return this.dirtyForm && this.dirtyFields.length !== 0;
        },

        // @override PreventFormAbandonment
        updateFormStatus() {
            this.dirtyForm = true;
        },

        skipUnsavedPrompt(callback) {
            this.skipPrompt = true;
            callback();
            this.skipPrompt = false;
        },

        disableUnsavedPrompt() {
            this.skipPrompt = true;
        },

        enableUnsavedPrompt() {
            this.skipPrompt = false;
        },

        unsavedPrompt(e) {
            if (this.hasChanges()) {
                e.preventDefault();
                e.returnValue = '';
            }
        },

        confirmExit() {
            this.openUnsavedChangesModal = false;
            this.nextRoute();
        },

        cancelExit() {
            this.openUnsavedChangesModal = false;
            this.nextRoute = null;
        }
    }
}
