<template>
  <div
    class="multiselect-detail-field-value relative o1-rounded border bg-white o1-border-gray-200 dark:o1-bg-gray-900 dark:o1-border-gray-700"
    v-if="values"
  >
    <div v-if="field.belongsToManyResourceName && field.viewable" class="overflow-hidden">
      <div v-for="(option, i) of values" :key="i" class="multiselect-detail-row border-b o1-border-gray-200 dark:o1-border-slate-800 o1-text-sm hover:o1-bg-slate-50 dark:hover:o1-bg-gray-800">
        <Link
          class="link-default block o1-py-1 o1-px-2"
          :href="$url(`/resources/${field.belongsToManyResourceName}/${field.value[i]}`)"
        >
          {{ i }}
        </Link>
      </div>
    </div>
    <div v-else class="overflow-hidden">
      <div
        class="multiselect-detail-row border-b o1-border-gray-200 dark:o1-border-slate-800 o1-text-sm o1-py-1 o1-px-2 hover:o1-bg-slate-50 dark:hover:o1-bg-gray-800"
        v-for="(value, i) of values"
        :key="i"
      >
        <div v-if="value.label.url" class="relative">
          <div class="inline-block w-16 pr-2" v-if="value.label.img">
            <img v-viewer class="w-auto"  :src="value.label.img" :alt="value.label.code" >
          </div>
          <div class="inline-block w-10/12 align-top">
            <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ value.label.title }}</span><br>
            <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ value.label.url }}</span><br>
            <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ value.label.code }}</span>
          </div>
        </div>
        <span v-else>
    {{ value.label }}
  </span>
      </div>
    </div>
  </div>

  <div v-else>—</div>
</template>

<script>
export default {
  props: ['field', 'values'],
};
</script>

<style lang="scss" scoped>
.multiselect-detail-field-value {
  .multiselect-detail-row {
    &:last-child {
      border-bottom: none;
    }
  }
}
</style>
