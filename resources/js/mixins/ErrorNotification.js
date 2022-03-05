import {Errors} from 'laravel-nova';

export default {
    data: _ => ({
        validationErrors: new Errors()
    }),

    methods: {
        hideErrors() {
            this.validationErrors = new Errors();
        },

        showErrors(errors) {
            this.validationErrors = new Errors(errors);

            for (let field in errors) {
                Nova.error(errors[field]);
            }
        }
    }
};
