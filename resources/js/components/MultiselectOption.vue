<template>
  <div v-if="values.label.url">
    <div class="inline-block w-16 pr-2" v-if="values.label.img">
      <img class="w-auto"  :src="values.label.img" :alt="values.label.code" >
    </div>
    <div class="inline-block w-10/12 align-top">
      <span class="whitespace-no-wrap text-gray-900 text-sm font-medium">{{ values.label.title }}</span><br>
      <span class="whitespace-no-wrap mt-1 text-gray-500 text-xs">{{ values.label.url }}</span><br>
      <span class="whitespace-no-wrap mt-1 text-gray-500 text-sm">{{ values.label.code }}</span>
    </div>
  </div>
  <span v-else>
      {{ values.label}}
  </span>
</template>

<script>
export default {
  props: ['field', 'values'],
};
</script>
