<template>
  <div class="relative rounded-lg bg-white shadow border border-60" v-if="values">
    <div class="overflow-hidden rounded-b-lg rounded-t-lg">
      <div class="border-b border-50 cursor-text font-mono text-sm py-2 px-4 p-3 pb-2" v-for="(item, i) of values" :key="i">
          <div v-if="item.label" class="block">
            <div class="inline-block w-16 pr-2">
              <img v-viewer v-if="item.label.img" :src="item.label.img" :alt="item.label.code">
            </div>
            <div class="inline-block w-10/12 align-top">
              <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ item.label.title }}</span><br>
              <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ item.label.url }}</span><br>
              <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ item.label.code }}</span>
            </div>
          </div>
        <span v-else>
            {{ item }}
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
