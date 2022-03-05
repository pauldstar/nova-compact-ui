Nova.booting((Vue) => {
    Vue.component('index-reactive-boolean-field', Vue.component('index-boolean-field'));
    Vue.component('detail-reactive-boolean-field', Vue.component('detail-boolean-field'));
    Vue.component('form-reactive-boolean-field', require('./components/form/ReactiveBooleanField'));

    Vue.component('index-reactive-text-field', require('./components/index/ReactiveTextField'));
    Vue.component('detail-reactive-text-field', Vue.component('detail-text-field'));
    Vue.component('form-reactive-text-field', require('./components/form/ReactiveTextField'));

    Vue.component('index-reactive-date-field', Vue.component('index-date'));
    Vue.component('detail-reactive-date-field', Vue.component('detail-date'));
    Vue.component('form-reactive-date-field', require('./components/form/ReactiveDateField'));

    Vue.component('index-reactive-number-field', require('./components/index/ReactiveTextField'));
    Vue.component('detail-reactive-number-field', Vue.component('detail-text-field'));
    Vue.component('form-reactive-number-field', require('./components/form/ReactiveTextField'));

    Vue.component('index-reactive-belongs-to-field', require('./components/index/ReactiveBelongsToField'));
    Vue.component('detail-reactive-belongs-to-field', Vue.component('detail-belongs-to-field'));
    Vue.component('form-reactive-belongs-to-field', require('./components/form/ReactiveBelongsToField'));

    Vue.component('index-reactive-select-field', Vue.component('index-select-field'));
    Vue.component('detail-reactive-select-field', Vue.component('detail-select-field'));
    Vue.component('form-reactive-select-field', require('./components/form/ReactiveSelectField'));

    Vue.component('reactive-has-many-field', require('./components/form/ReactiveHasManyField'));
    Vue.component('detail-reactive-has-many-field', require('./components/form/ReactiveHasManyField'));

    Vue.component('reactive-belongs-to-many-field', Vue.component('detail-belongs-to-many-field'));

    Vue.component('index-reactive-currency-field', require('./components/index/ReactiveCurrencyField'));
    Vue.component('detail-reactive-currency-field', Vue.component('detail-currency-field'));
    Vue.component('form-reactive-currency-field', require('./components/form/ReactiveCurrencyField'));

    Vue.component('detail-reactive-key-value-field', require('./components/detail/CustomKeyValueField'));
    Vue.component('form-reactive-key-value-field', require('./components/form/ReactiveKeyValueField/ReactiveKeyValueField'));

    Vue.component('detail-reactive-trix-field', Vue.component('detail-trix-field'));
    Vue.component('form-reactive-trix-field', require('./components/form/ReactiveTrixField.vue'));

    Vue.component('form-reactive-hidden-field', require('./components/form/ReactiveHiddenField'));

    Vue.component('detail-heading-field', require('./components/detail/CustomHeadingField'));
});
