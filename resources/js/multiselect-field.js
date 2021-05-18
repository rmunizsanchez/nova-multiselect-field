import 'viewerjs/dist/viewer.css';
import Viewer from 'v-viewer';
Nova.booting((Vue, router, store) => {
  Vue.use(Viewer);
  Vue.component('index-nitsnets-multiselect-field', require('./components/IndexField').default);
  Vue.component('detail-nitsnets-multiselect-field', require('./components/DetailField').default);
  Vue.component('form-nitsnets-multiselect-field', require('./components/FormField').default);
  Vue.component('nitsnets-multiselect-option', require('./components/MultiselectOption').default);
  Vue.component('nitsnets-listed-option', require('./components/ListedOption').default);
  // Allow user to overwrite nova-multiselect-detail-field-value
  if (!Vue.options.components['nova-nitsnets-multiselect-detail-field-value']) {
    Vue.component(
      'nova-nitsnets-multiselect-detail-field-value',
      require('./components/NovaMultiselectDetailFieldValue').default
    );
  }

  Vue.component(
    'nova-nitsnets-multiselect-detail-one-field-value',
    require('./components/NovaMultiselectDetailOneFieldValue').default
  );
});
