<template>
  <default-field :field="field" :showHelpText="showHelpText" :errors="errors">
    <template slot="field">

      <div class="nts-multiselect-field flex flex-col relative" :class="">
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
            :group-select="field.groupSelect || false"
            :ref="`multiselect-${resourceId}`"
            :id="resourceId"
            :value="selected"
            :options="field.apiUrl ? asyncOptions : computedOptions"
            :internal-search="!field.apiUrl"
            :class="field.activeImport?'import':''"
            :disabled="isReadonly"
            :placeholder="field.placeholder || field.name"
            :close-on-select="field.max === 1 || !isMultiselect"
            :multiple="isMultiselect"
            :max="max || field.max || null"
            :optionsLimit="field.optionsLimit || 1000"
            :limitText="count => __('novaMultiselect.limitText', { count: String(count || '') })"
            selectLabel=""
            :loading="isLoading"
            selectGroupLabel=""
            selectedLabel=""
            deselectLabel=""
            deselectGroupLabel=""
            :clearOnSelect="field.clearOnSelect || false"
          >

            <template slot="maxElements">
              {{ __('novaMultiselect.maxElements', { max: String(field.max || '') }) }}
            </template>

            <template slot="noResult">
              {{ __('novaMultiselect.noResult') }}
            </template>

            <template slot="noOptions">
              {{ field.apiUrl ? __('novaMultiSelect.startTypingForOptions') : __('novaMultiselect.noOptions') }}
            </template>

            <template slot="clear">
              <div
                class="multiselect__clear"
                v-if="field.nullable && (isMultiselect ? value.length : value)"
                @mousedown.prevent.stop="value = isMultiselect ? [] : null"
              ></div>
            </template>
            <template slot="singleLabel" slot-scope="props">
              <nitsnets-multiselect-option :field="field" :value="props.option"/>
            </template>
            <template slot="option" slot-scope="props">
              <nitsnets-multiselect-option :field="field" :value="props.option"/>
            </template>
            <template slot="search" slot-scope="props">
              <nitsnets-multiselect-option :field="field" :value="props.option"/>
            </template>
          </multiselect>
          <!-- csv import -->
          <form v-if="field.activeImport" enctype="multipart/form-data" class="import-container">
            <input  type="file" :id="fileid" name="name" class="form-file-input select-none" @change="handleFile" ref="file">
            <label :for="fileid" class="form-file-btn btn btn-default btn-primary select-none">
              <span>Import</span>
            </label>
          </form>
        </div>

        <!-- Reorder mode field -->
        <div v-if="reorderMode && !field.listed" class="form-input-bordered py-1">
          <vue-draggable tag="ul" v-model="value" class="flex flex-col pl-0" style="list-style: none; margin-top: 5px">
            <transition-group>
              <li v-for="(s, i) in selected" :key="i + 0" class="reorder__tag text-sm mb-1 px-2 py-1 text-white">
                {{ s.label }}
              </li>
            </transition-group>
          </vue-draggable>
        </div>

        <div
          v-if="field.reorderable && listed"
          class="ml-auto mt-2 text-sm font-bold text-primary cursor-pointer dim"
          @click="reorderMode = !reorderMode"
        >
          {{ __(reorderMode ? 'novaMultiselect.doneReordering' : 'novaMultiselect.reorder') }}
        </div>

        <template v-if="field.htmlAfterInput">
          <div class="p3" v-html="this.field.htmlAfterInput"></div>
        </template>


        <!-- item's list -->
        <div v-if="listed && !reorderMode" class="py-2">
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
      </div>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import HandlesFieldValue from '../mixins/HandlesFieldValue';
import Multiselect from 'vue-multiselect';
import VueDraggable from 'vuedraggable';
import debounce from 'lodash/debounce';

export default {
  components: { Multiselect, VueDraggable },

  mixins: [FormField, HandlesValidationErrors, HandlesFieldValue],

  props: ['resourceName', 'resourceId', 'field'],

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

    this.fileid = 'input-${this.safeDependsOnAttribute}-file';
    if (this.field.dependsOn) {
      this.options = [];

      Nova.$on(`nts-multiselect-${this.safeDependsOnAttribute}-input`, values => {
        values = Array.isArray(values) ? values : [values]; // Handle singleSelect

        // Clear options
        this.options = [];

        const newOptions = [];
        values.forEach(option => {
          if (!option) return;

          Object.keys(this.field.dependsOnOptions[option.value] || {}).forEach(value => {
            // Only add unique
            if (newOptions.find(o => o.value === value)) return;

            let label = this.field.dependsOnOptions[option.value][value];
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
        const dependsOnMax = this.field.dependsOnMax;
        if (dependsOnMax) {
          const maxValues = values.map(option => {
            return option && (this.field.dependsOnMax[option.value] || null);
          });
          this.max = Math.max(...maxValues) || null;
        }
      });
    }

    if (this.field.distinct) {
      // Handle distinct callback.
      Nova.$on(`nts-multiselect-${this.field.distinct}-distinct`, callback => {
        return callback(this.value);
      });
    }

    if (this.field.listed) {
      this.listed = true
    }


    // Emit initial value
    this.$nextTick(() => {
      Nova.$emit(`nts-multiselect-${this.field.attribute}-input`, this.value);
    });
  },

  destroyed() {
    window.removeEventListener('scroll', this.repositionDropdown);
    if (this.field.distinct) Nova.$off(`nts-multiselect-${this.field.distinct}-distinct`);
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
      const flexibleKey = this.flexibleKey;
      if (!flexibleKey) return this.field.dependsOn;
      return `${flexibleKey}__${this.field.dependsOn}`;
    }
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

        if (this.field.listed) {
          this.listable = valuesArray && valuesArray.length ? valuesArray.map(this.getValueFromOptions).filter(Boolean) : [];
        } else {
          this.value = valuesArray && valuesArray.length ? valuesArray.map(this.getValueFromOptions).filter(Boolean) : [];
        }
      } else {
        if (this.field.listed) {
          this.listable = this.getValueFromOptions(this.field.value);
        } else {
          this.value = this.getValueFromOptions(this.field.value);
        }
      }
    },

    fill(formData) {
      if (this.listed) {
        this.value = this.listable;
      }
      if (this.isMultiselect) {
        if (this.value && this.value.length) {
          this.value.forEach((v, i) => {
            formData.append(`${this.field.attribute}[${i}]`, v.value);
          });
        } else {
          formData.append(this.field.attribute, '');
        }
      } else {
        formData.append(this.field.attribute, (this.value && this.value.value) || '');
      }
    },

    handleChange(value) {
      if (this.listed) {
        this.value = [];
      } else {
        this.value = value;
      }

      this.$nextTick(() => this.repositionDropdown());
      Nova.$emit(`nts-multiselect-${this.field.attribute}-input`, this.value);
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
      Nova.$emit(`nts-multiselect-${this.field.distinct}-distinct`, values => {
        // Validate that current value is not disabled.
        if (values !== this.selected) {
          // Add already used values to distinctValues
          if (this.isMultiselect) this.distinctValues.push(...values.map(value => value.value));
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

        const fromBottom = (window.innerHeight || document.documentElement.clientHeight) - bottom;

        ms.$refs.list.style.position = 'fixed';
        ms.$refs.list.style.width = `${el.clientWidth}px`;

        if (fromBottom < 300) {
          ms.$refs.list.style.top = 'auto';
          ms.$refs.list.style.bottom = `${fromBottom + height}px`;
          ms.$refs.list.style['border-radius'] = '5px 5px 0 0';
        } else {
          ms.$refs.list.style.bottom = 'auto';
          ms.$refs.list.style.top = `${top + height}px`;
          ms.$refs.list.style['border-radius'] = '0 0 5px 5px';
        }
      };

      if (onOpen) this.$nextTick(handlePositioning);
      else handlePositioning();
    },

    fetchOptions: debounce(async function (search) {
      const { data } = await Nova.request().get(`${this.field.apiUrl}`, { params: { search } });

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
          const label = resource.title;
          const value = resource.id.value;
          newOptions.push({ value, label });
        }

        this.asyncOptions = newOptions;
        this.isLoading = false;
        return;
      }

      this.asyncOptions = Object.entries(data).map(entry => ({ label: entry[1], value: entry[0] }));
      console.log(this.asyncOptions);
      this.isLoading = false;
    }, 500),

    tryToFetchOptions(query) {
      if (!this.field.apiUrl) return;

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
.import-container {
  position: absolute;
  top: 0px;
  right:  0px;
}

.multiselect.import {
  width: 80% !important;
}

.multiselect-field {
  position: relative;

  .reorder__tag {
    background: #41b883;
    border-radius: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all 0.25s ease;
    margin-bottom: 5px;

    &:hover {
      cursor: pointer;
      background: #3dab7a;
      transition-duration: 0.05s;
    }
  }

  .multiselect__clear {
    position: absolute;
    right: 41px;
    height: 40px;
    width: 40px;
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
      top: 12px;
      right: 4px;
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
