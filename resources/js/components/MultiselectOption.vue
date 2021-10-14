<template>
  <div v-if="value.label.url">
    <div class="inline-block w-16 pr-2" v-if="value.label.img">
      <img class="w-auto"  :src="value.label.img" :alt="value.label.code" >
    </div>
    <div class="inline-block w-10/12 align-top">
      <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ value.label.title }}</span><br>
      <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ value.label.url }}</span><br>
      <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ value.label.code }}</span>
    </div>
  </div>
  <span v-else>
      {{ value.label}}
  </span>
</template>

<script>
export default {
  props: ['field', 'value'],
};
</script>
