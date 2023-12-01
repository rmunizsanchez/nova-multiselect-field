import 'viewerjs/dist/viewer.css';
import Viewer from 'v-viewer';
Nova.booting((Vue, router, store) => {
  Nova.component('index-nitsnets-multiselect-field', require('./components/IndexField').default);
  Nova.component('detail-nitsnets-multiselect-field', require('./components/DetailField').default);
  Nova.component('form-nitsnets-multiselect-field', require('./components/FormField').default);
  Nova.component('nitsnets-multiselect-option', require('./components/MultiselectOption').default);
  Nova.component('nitsnets-listed-option', require('./components/ListedOption').default);

  Nova.component(
    'nova-nitsnets-multiselect-detail-one-field-value',
    require('./components/NovaMultiselectDetailOneFieldValue').default
  );
});
