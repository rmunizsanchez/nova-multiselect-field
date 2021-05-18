<template>
  <div v-if="value.label.url">
    <div class="inline-block w-8" v-if="value.label.img">
      <img class="w-auto"  :src="value.label.img" :alt="value.label.code" >
    </div>
    <div class="inline-block w-8/12">
      <span class="whitespace-no-wrap text-sm font-semibold">{{ value.label.title }}</span><br>
      <span class="whitespace-no-wrap text-xs tracking-loose text-80">{{ value.label.url }}</span><br>
      <span class="whitespace-no-wrap text-xs">{{ value.label.code }}</span>
    </div>
  </div>
  <span v-else>
      {{ value.label }}
  </span>
</template>

<script>
export default {
  props: ['field', 'value'],
};
</script>
