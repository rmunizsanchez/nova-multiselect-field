<template>
  <DefaultField :field="currentField" :showHelpText="showHelpText" :errors="errors">
    <template #field>
      <div class="outl1ne-multiselect-field flex flex-col">
        <!-- Multi select field -->
        <div>
        <multiselect
          v-if="!reorderMode"
          @input="handleChange"
          @open="handleOpen"
          @search-change="tryToFetchOptions"
          @select="handleSelect"
          track-by="value"
          label="label"
          :group-label="isOptionGroups ? 'label' : void 0"
          :group-values="isOptionGroups ? 'values' : void 0"
          :group-select="currentField.groupSelect || false"
          ref="multiselect"
          :value="selected"
          :options="currentField.apiUrl ? asyncOptions : computedOptions"
          :internal-search="!currentField.apiUrl"
          :class="[errorClasses, { 'has-optiongroup': isOptionGroups }]"
          :disabled="currentField.readonly"
          :placeholder="currentField.placeholder || currentField.name"
          :close-on-select="currentField.max === 1 || !isMultiselect"
          :multiple="isMultiselect"
          :max="max || currentField.max || null"
          :optionsLimit="currentField.optionsLimit || 1000"
          :limit="currentField.limit"
          :limitText="count => __('novaMultiselect.limitText', { count: String(count || '') })"
          selectLabel=""
          :loading="isLoading"
          selectGroupLabel=""
          selectedLabel=""
          tagPlaceholder=""
          deselectLabel=""
          deselectGroupLabel=""
          :clearOnSelect="currentField.clearOnSelect || false"
          :taggable="currentField.taggable || false"
          @tag="addTag"
        >
          <template #maxElements>
            {{ __('novaMultiselect.maxElements', { max: String(currentField.max || '') }) }}
          </template>

          <template #noResult>
            {{ __('novaMultiselect.noResult') }}
          </template>

          <template #noOptions>
            {{ currentField.apiUrl ? __('novaMultiSelect.startTypingForOptions') : __('novaMultiselect.noOptions') }}
          </template>

          <template #clear>
            <div
              class="multiselect__clear"
              v-if="currentField.nullable && (isMultiselect ? value.length : value)"
              @mousedown.prevent.stop="value = isMultiselect ? [] : null"
            />
          </template>

          <template #singleLabel>
            <span>aasasdasd{{ value ? value.label : '' }}</span>
          </template>


          <template #option="{ option }">
            <nitsnets-listed-option :value="option"></nitsnets-listed-option>
          </template>

          <template #tag="{ option, remove }">
            <form-multiselect-field-tag :option="option" :remove="remove" />
          </template>
        </multiselect>
          <!-- csv import -->
          <form v-if="currentField.activeImport" enctype="multipart/form-data" class="import-container">
            <input  type="file" :id="fileid" name="name" class="form-file-input select-none" @change="handleFile" ref="file">
            <label :for="fileid" class="form-file-btn btn btn-default btn-primary select-none">
              <span>Import</span>
            </label>
          </form>
        </div>

        <!-- Reorder mode field -->
        <div v-if="reorderMode" class="form-input-bordered py-1 px-2 rounded-lg">
          <ul class="flex flex-col pl-0" style="list-style: none; margin-top: 5px">
            <VueDraggable v-model="value" tag="transition-group">
              <template #item="{ element }">
                <li class="reorder__tag text-sm mb-1 px-2 py-1 text-white">
                  {{ element.label }}
                </li>
              </template>
            </VueDraggable>
          </ul>
        </div>

        <div
          v-if="currentField.reorderable"
          class="ml-auto mt-2 text-sm font-bold text-primary cursor-pointer dim"
          @click="reorderMode = !reorderMode"
        >
          {{ __(reorderMode ? 'novaMultiselect.doneReordering' : 'novaMultiselect.reorder') }}
        </div>

        <template v-if="currentField.htmlAfterInput">
          <div class="p3" v-html="this.currentField.htmlAfterInput"></div>
        </template>


        <!-- item's list -->
        <div v-if="currentField.listed" class="py-2">
          <div v-for="(s, i) in listable" class="block form-input-bordered mb-2 p-3 pb-2 relative">
            <div v-if="s.label.title">
              <div class="inline-block w-16 pr-2" v-if="s.label.img">
                <img v-viewer class="w-auto" :src="s.label.img" :alt="s.label.code">
              </div>
              <div class="inline-block w-10/12 align-top">
                <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ s.label.title }}</span><br>
                <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ s.label.url }}</span><br>
                <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ s.label.code }}</span>
              </div>
              <div class="absolute top-3 right-3">
                <i class="cursor-pointer" v-on:click="removeList(i)">
                  <svg class="w-4" xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </i>
              </div>
            </div>
            <div v-else>
              <div class="inline-block w-10/12 align-top">
                <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ s.label }}</span><br>
              </div>
              <div class="absolute top-3 right-3">
                <i class="cursor-pointer" v-on:click="removeList(i)">
                  <svg class="w-4" xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reorder mode field -->
      <div v-if="reorderMode && listed" class="form-input-bordered py-1">
        <vue-draggable tag="ul" v-model="listable" class="flex flex-col pl-0" style="list-style: none; margin-top: 5px">
          <transition-group>
            <li v-for="(s, i) in listable" :key="i + 0" class="reorder__tag text-sm mb-1 px-2 py-1 pb-2 text-white">

              <div v-if="s.label.title">
                <div class="inline-block w-16 pr-2" v-if="s.label.img">
                  <img v-viewer class="w-auto"  :src="s.label.img" :alt="s.label.code">
                </div>
                <div class="inline-block w-10/12 align-top">
                  <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ s.label.title }}</span><br>
                  <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ s.label.url }}</span><br>
                  <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ s.label.code }}</span>
                </div>
              </div>
              <span v-else>
                  {{ s.label }}
                  </span>
            </li>
          </transition-group>
        </vue-draggable>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova';
import HandlesFieldValue from '../mixins/HandlesFieldValue';
import Multiselect from 'vue-multiselect/src/Multiselect';
import VueDraggable from 'vuedraggable';
import debounce from 'lodash/debounce';

export default {
  components: { Multiselect, VueDraggable },

  mixins: [HandlesValidationErrors, HandlesFieldValue, DependentFormField],

  props: ['resourceName', 'resourceId', 'field', 'mode'],

  data: () => ({
    reorderMode: false,
    options: [],
    max: void 0,
    asyncOptions: [],
    distinctValues: [],
    isLoading: false,
    isInitialized: false,
    listable : [],
    listed: false,
    importLog: [],
    file: '',
    fileinput: '',
    fileid: ''
  }),

  mounted() {
    window.addEventListener('scroll', this.repositionDropdown);
    this.onSyncedField();

    if (this.field.optionsDependOn) {
      this.options = [];
      this.setInitialValue();

      Nova.$on(`multiselect-${this.safeDependsOnAttribute}-input`, values => {
        values = Array.isArray(values) ? values : [values]; // Handle singleSelect

        // Clear options
        this.options = [];

        const newOptions = [];
        values.forEach(option => {
          if (!option) return;

          Object.keys(this.field.optionsDependOnOptions[option.value] || {}).forEach(value => {
            // Only add unique
            if (newOptions.find(o => o.value === value)) return;

            let label = this.field.optionsDependOnOptions[option.value][value];
            newOptions.push({ label, value });
          });
        });

        this.options = newOptions;

        // Remove values that no longer apply
        const hasValue = value => this.options.find(v => v.value === value);
        if (this.isMultiselect) {
          if (Array.isArray(this.value)) {
            this.value = this.value.filter(v => !!v && !!hasValue(v.value));
          }
        } else {
          this.value = this.value && !!hasValue(this.value.value) ? this.value : void 0;
        }

        // Calculate max values
        const dependsOnMax = this.field.optionsDependOnMax;
        if (dependsOnMax) {
          const maxValues = values.map(option => {
            return option && (this.field.optionsDependOnMax[option.value] || null);
          });
          this.max = Math.max(...maxValues) || null;
        }

        // Emit new value so fields down the line also get refreshed
        Nova.$emit(`multiselect-${this.field.attribute}-input`, this.value);
      });
    }

    if (this.field.distinct) {
      // Handle distinct callback.
      Nova.$on(`multiselect-${this.field.distinct}-distinct`, callback => {
        return callback(this.value);
      });
    }
    if (this.currentField.listed) {
      this.listed = true
    }
    // Emit initial value
    this.$nextTick(() => {
      Nova.$emit(`multiselect-${this.field.attribute}-input`, this.value);
    });
  },

  destroyed() {
    window.removeEventListener('scroll', this.repositionDropdown);
    if (this.field.distinct) Nova.$off(`multiselect-${this.field.distinct}-distinct`);
  },

  computed: {
    selected() {
      return this.value || [];
    },

    flexibleKey() {
      const flexRegex = /^([a-zA-Z0-9]+)(?=__)/;
      const match = this.field.attribute.match(flexRegex);
      if (match && match[0] && match[0].length === 16) return match[0];
    },

    safeDependsOnAttribute() {
      if (this.field.optionsDependOnOutsideFlexible) {
        return this.field.optionsDependOn;
      }

      const flexibleKey = this.flexibleKey;
      if (!flexibleKey) return this.field.optionsDependOn;
      return `${flexibleKey}__${this.field.optionsDependOn}`;
    },
  },

  methods: {
    // start csv import
    fetchServerData: async function(search) {

      console.log('fetchServerData', search);

      const { data } = await Nova.request().post(`${this.field.apiImportUrl}`, { codes: search });

      // Response is not an array or an object
      if (typeof data !== 'object') throw new Error('Server response was invalid.');

      // Is array
      if (Array.isArray(data)) {
        this.importLog.push("Error en la búsqueda");
        return;
      }

      // Nova resource response
      if (data) {
        console.log(data);

        var vm = this;
        _.forEach(data,function (label, value) {
          vm.handleSelect({ value, label }, null);
        })
      }

      return;
    },

    importContentToList: function(dataList) {
      let importCalls = 0;
      let productList = [];
      dataList.forEach( function(val, idx) {
        if (idx == 0) {
          this.importLog.push("Detectada cabecera " + val);
        } else {
          if (val.length) {
            // this.fetchServerData(val.trim());
            productList.push(val.trim());
            importCalls++;
          }
        }
      }, this);
      this.fetchServerData(productList);
      this.importLog.push("Buscando " + importCalls + " productos");
    },

    handleFile: function (event) {
      // debugger;
      var file = this.$refs.file.files[0];

      let promise = new Promise((resolve, reject) => {
        var reader = new FileReader();
        var vm = this;
        reader.onload = e => {
          resolve((vm.fileinput = reader.result));
        };
        reader.readAsText(file);
      });

      promise.then(
        result => {
          // handle a successful result
          this.importContentToList(this.fileinput.split('\n'));
        },
        error => {
          // handle an error
          console.log(error);
        }
      );
    },
    // end csv import

    setInitialValue() {
      if (this.isMultiselect) {
        const valuesArray = this.getInitialFieldValuesArray();
          if (this.currentField.listed) {
            this.listable = valuesArray && valuesArray.length ? valuesArray.map(this.getValueFromOptions).filter(Boolean) : [];
          } else {
            this.value = valuesArray && valuesArray.length ? valuesArray.map(this.getValueFromOptions).filter(Boolean) : [];
          }
      } else {
        if (this.currentField.listed) {
          this.listable = this.getValueFromOptions(this.currentField.value);
        } else {
          this.value = this.getValueFromOptions(this.currentField.value);
        }
      }
      // Emit new value so fields down the line also get refreshed
//      this.currentField.value = !this.value ? '' : this.isMultiselect ? this.value.map(v => v.value) : this.value.value;
    },

    fillIfVisible(formData, attribute) {
      if (!this.currentlyIsVisible) return;
      if (this.listed) {
        this.value = this.listable;
      }
      if (this.isMultiselect) {
        if (this.value && this.value.length) {
          this.value.forEach((v, i) => {
            formData.append(`${attribute}[${i}]`, v.value);
          });
        } else {
          formData.append(attribute, '');
        }
      } else {
        formData.append(attribute, (this.value && this.value.value) || '');
      }
    },

    handleChange(value) {
      // For some reason, after upgrading to Vue 3, this callback
      // Sometimes receives an InputEvent as an argument - my only
      // fix is to filter out the InputEvent and only accept arrays
      if (this.isMultiselect) {
        if (!Array.isArray(value)) return;
      } else {
        if (value && !value.value) return;
      }

      if (this.listed) {
        this.value = [];
      } else {
        this.value = value;
      }
      this.$nextTick(() => this.repositionDropdown());
      Nova.$emit(`multiselect-${this.field.attribute}-input`, this.value);
      this.emitFieldValueChange(
        this.field.attribute,
        !this.value ? '' : this.isMultiselect ? this.value.map(v => v.value) : this.value.value
      );
    },

    handleOpen() {
      this.repositionDropdown(true);
      if (!this.isInitialized) this.isInitialized = true;
      if (this.field.distinct) this.distinctOptions();
    },

    /**
     * Creates new array of values that have been used by another multiselect.
     * If an options is used by another multiselect, we disable it.
     */
    distinctOptions() {
      this.distinctValues = [];

      // Fetch other select values in current distinct group
      Nova.$emit(`multiselect-${this.field.distinct}-distinct`, values => {
        if (!values) return;

        // Validate that current value is not disabled.
        if (values !== this.selected) {
          // Add already used values to distinctValues
          if (Array.isArray(values)) this.distinctValues.push(...values.map(value => value.value));
          else this.distinctValues.push(values.value);
        }
      });

      this.options = this.options.map(option => {
        if (this.isOptionGroups) {
          // Support for option groups
          return {
            ...option,
            values: option.values.map(option => this.newDistinctOption(option)),
          };
        }

        return this.newDistinctOption(option);
      });
    },

    newDistinctOption(option) {
      // Only return $disabled option if values match
      if (this.distinctValues.includes(option.value)) return { ...option, $isDisabled: true };

      // Force remove $isDisabled
      delete option.$isDisabled;
      return option;
    },

    repositionDropdown(onOpen = false) {
      const ms = this.$refs.multiselect;
      if (!ms) return;

      const el = ms.$el;

      const handlePositioning = () => {
        const { top, height, bottom } = el.getBoundingClientRect();
        if (onOpen) ms.$refs.list.scrollTop = 0;

        // Find parent with 'fixed' class
        let parent = el.parentElement;
        let fixedModal = void 0;
        if (document.querySelectorAll('.fixed.modal').length > 0) {
          while (parent && !fixedModal) {
            if (parent.classList.contains('fixed')) fixedModal = parent;
            parent = parent.parentElement;
          }
        }

        const fromBottom = (window.innerHeight || document.documentElement.clientHeight) - bottom;

        ms.$refs.list.style.position = 'fixed';
        ms.$refs.list.style.width = `${el.clientWidth}px`;

        if (fromBottom < 300) {
          ms.$refs.list.style.top = 'auto';
          ms.$refs.list.style.bottom = `${fromBottom + height}px`;
          ms.$refs.list.style['border-radius'] = '5px 5px 0 0';
        } else {
          const adjustedTop = fixedModal
            ? top - (parseInt(window.getComputedStyle(fixedModal)['padding-top']) || 0)
            : top;
          ms.$refs.list.style.bottom = 'auto';
          ms.$refs.list.style.top = `${adjustedTop + height}px`;
          ms.$refs.list.style['border-radius'] = '0 0 5px 5px';
        }
      };

      if (onOpen) this.$nextTick(handlePositioning);
      else handlePositioning();
    },

    addTag(newTag) {
      const tag = {
        label: newTag,
        value: newTag,
      };

      this.options.push(tag);

      if (this.isMultiselect) {
        this.value.push(tag);
      } else {
        this.value = tag;
      }
    },

    fetchOptions: debounce(async function (search) {
      const resourceId = this.resourceId || '';
      const { data } = await Nova.request().get(`${this.currentField.apiUrl}`, { params: { search, resourceId } });

      // Response is not an array or an object
      if (typeof data !== 'object') throw new Error('Server response was invalid.');
      // Is array
      if (Array.isArray(data)) {
        this.asyncOptions = data;
        this.isLoading = false;
        console.log(this.asyncOptions);
        return;
      }

      // Nova resource response
      if (data.resources) {
        const newOptions = [];

        for (const resource of data.resources) {
          const label = resource.display || resource.title || '-';
          const value = resource.value || resource.id.value || null;
          newOptions.push({ value, label });
        }

        this.asyncOptions = newOptions;
        this.isLoading = false;
        return;
      }

      this.asyncOptions = Object.entries(data).map(entry => ({ label: entry[1], value: entry[0] }));
      this.isLoading = false;
    }, 500),

    tryToFetchOptions(query) {
      if (!this.currentField.apiUrl) return;

      if (query.length >= 1) {
        this.asyncOptions = [];
        this.isLoading = true;
        try {
          this.fetchOptions(query);
        } catch (error) {
          console.error('Error performing search:', error);
        }
      } else {
        this.asyncOptions = [];
      }
    },

    onSyncedField() {
      this.options = this.currentField.options || [];
      this.setInitialValue();
    },
    handleSelect(selected,id) {
      if (this.isMultiselect) {
        console.log(this.field.max);
        console.log(this.listable.length);
        if (this.listable.length >= this.field.max) {
          this.listable.pop();
        }
        if (this.listable.findIndex(function (o) {
          return o.value === selected.value
        }) === -1) {
          this.listable.push(selected)
        }
      } else {
        this.listable = [];
        this.listable.push(selected)
      }
    },
    removeList(id) {
      this.listable.splice(id, 1);
    }
  },
};
</script>

<style lang="scss">
$white: #fff;
$slate50: #f8fafc;
$slate100: #f1f5f9;
$slate200: #e2e8f0;
$slate300: #cbd5e1;
$slate400: #94a3b8;
$slate500: #64748b;
$slate600: #475569;
$slate700: #334155;
$slate800: #1e293b;
$slate900: #0f172a;
$slate1000: #070a13;

$red400: #f87171;
$red500: #ef4444;

.outl1ne-multiselect-field {
  .multiselect {
    min-height: 36px;
    border: none;
    border-radius: 0;
    background: none;
    display: block;
    &.has-optiongroup {
      .multiselect__option:not(.multiselect__option--group) {
        padding-left: 24px;
      }
    }
  }

  .multiselect__tags {
    --tw-border-opacity: 1;
    border-width: 1px;

    border-color: $slate300;
    background-color: $white;
    color: $slate600;

    padding: 6px 56px 0 6px;
    min-height: 36px;

    border-radius: 4px;
    overflow: hidden;

    .dark & {
      border-color: $slate700;
      background-color: $slate900;
      color: $slate400;
    }
  }

  .multiselect__input {
    border: none;
    border-color: rgba(var(--colors-gray-100), var(--tw-border-opacity));
    background-color: $white;
    color: rgba(var(--colors-gray-400));

    font-size: 14px;
    line-height: 14px;

    padding-left: 8px;

    .dark & {
      background-color: $slate900;
      color: $slate400;
    }
  }

  .multiselect__tag {
    background-color: rgba(var(--colors-primary-500));
    color: $white;
    font-weight: 600;

    /* .dark & {
      color: rgba(var(--colors-gray-900), var(--tw-text-opacity));
    } */

    padding: 4px 24px 4px 8px;
    margin: 1px 8px 1px 0;

    .multiselect__tag-icon {
      &::after {
        color: $white;
      }

      &:hover {
        background: rgba(var(--colors-primary-500));

        &::after {
          color: $red500;
        }
      }
    }
  }

  .multiselect > .multiselect__clear {
    &::before,
    &::after {
      width: 2px;
      background: rgba(var(--colors-gray-400));
    }

    &:hover {
      &::before,
      &::after {
        background: rgba(var(--colors-red-400));
      }
    }
  }

  .multiselect__single {
    background-color: $white;
    color: $slate600;

    font-size: 14px;
    line-height: 18px;
    font-weight: 700;
    min-height: 18px;

    padding-top: 2px;
    padding-left: 8px;

    color: $slate600;

    .dark & {
      color: rgba(var(--colors-gray-400));
      background-color: $slate900;
    }
  }

  .multiselect__spinner {
    background-color: $white;
    color: $slate600;

    .dark & {
      background-color: $slate900;
      color: $slate400;
    }

    &:before,
    &:after {
      border-color: rgba(var(--colors-primary-500)) transparent transparent;
    }
  }

  .multiselect__content-wrapper {
    border-color: $slate300;
    transition: none;

    .dark & {
      border-color: $slate700;
    }

    li > span.multiselect__option {
      background-color: #fff;
      color: $slate400;

      min-height: 32px;
      font-size: 14px;
      line-height: 14px;

      .dark & {
        background-color: $slate900;
      }
    }

    .multiselect__element {
      background-color: $white;
      color: $slate600;

      .dark & {
        background-color: $slate900;
        color: $slate400;
      }

      .multiselect__option {
        color: $slate600;

        padding: 8px 12px;
        min-height: 32px;
        font-size: 14px;
        line-height: 14px;

        .dark & {
          color: $slate400;

          &--disabled {
            color: $slate500 !important;
            background-color: $slate800 !important;
            opacity: 0.9;
          }
        }

        &.multiselect__option--selected {
          color: rgba(var(--colors-primary-500));
          background-color: $white;

          .dark & {
            background-color: $slate900;
          }
        }

        &.multiselect__option--group {
          color: rgba(var(--colors-primary-500));
          background-color: $white;

          .dark & {
            color: $slate500 !important;
            background-color: $slate1000 !important;
          }
        }

        &.multiselect__option--highlight {
          background-color: rgba(var(--colors-primary-500));
          color: $white;

          &::after {
            background-color: rgba(var(--colors-primary-500));
            font-weight: 600;
          }

          &.multiselect__option--selected {
            background-color: $red400;

            .dark & {
              background-color: $red400;
            }
          }
        }
      }
    }
  }

  .reorder__tag {
    background-color: rgba(var(--colors-primary-500));
    border-radius: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 5px;
    font-weight: 600;
    transition: all 0.2s ease-in-out;

    &:hover {
      cursor: pointer;
      opacity: 0.8;
    }
  }

  .multiselect__select {
    height: 34px;
    width: 32px;
  }

  .multiselect--disabled {
    opacity: 0.7;
    .multiselect__tags {
      background-color: rgba(var(--colors-gray-50));
      color: rgba(var(--colors-gray-600));
      .dark & {
        background-color: rgba(var(--colors-gray-700));
        color: rgba(var(--colors-gray-400));
      }
    }
  }

  .multiselect--disabled .multiselect__current,
  .multiselect--disabled .multiselect__select {
    background: none;
  }

  .multiselect__placeholder {
    margin-bottom: 8px;
    padding-top: 0px;
    padding-left: 8px;
    min-height: 16px;
    line-height: 16px;
    cursor: default;

    color: #475569;

    .dark & {
      color: #64748b;
    }
  }

  .multiselect__clear {
    position: absolute;
    right: 36px;
    top: 8px;
    height: 20px;
    width: 20px;
    display: block;
    cursor: pointer;
    z-index: 2;

    &::before,
    &::after {
      content: '';
      display: block;
      position: absolute;
      width: 3px;
      height: 16px;
      background: #aaa;
      top: 0;
      right: 0;
      left: 0;
      bottom: 0;
      margin: auto;
    }

    &::before {
      transform: rotate(45deg);
    }

    &::after {
      transform: rotate(-45deg);
    }
  }
}
</style>
