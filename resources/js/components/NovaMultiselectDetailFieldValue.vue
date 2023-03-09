<template>
  <div class="relative rounded-lg bg-white shadow border border-60" v-if="values">
    <div class="overflow-hidden rounded-b-lg rounded-t-lg">
      <div class="border-b border-50 cursor-text font-mono text-sm py-2 px-4 p-3 pb-2" v-for="(item, i) of values" :key="i">
          <div v-if="item.label && item.label.title" class="block">
            <div class="inline-block w-16 pr-2">
              <img v-viewer v-if="item.label.img" :src="item.label.img" :alt="item.label.code">
            </div>
            <div class="inline-block w-10/12 align-top">
              <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ item.label.title }}</span><br>
              <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ item.label.url }}</span><br>
              <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ item.label.code }}</span>
            </div>
            <div class="absolute top-3 right-3">
              <i class="cursor-pointer" v-on:click="$this.parent.removeList(i)">
                <svg class="w-4" xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </i>
            </div>
          </div>
        <span v-else-if="item.label">
            {{ item.label }}
          </span>
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
