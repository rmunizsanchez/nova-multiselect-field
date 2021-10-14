<template>
  <div class="relative rounded-lg bg-white shadow border border-60 p-3 pb-2" v-if="value.label.url">
    <div class="overflow-hidden rounded-b-lg rounded-t-lg">
        <div v-if="value.label.url" class="block">
            <div class="inline-block w-16 pr-2">
              <img v-viewer class="w-auto" v-if="value.label.img" :src="value.label.img" :alt="value.label.code" >
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

  <div v-else>—</div>
</template>

<script>
export default {
  props: ['field', 'value'],
};
</script>
