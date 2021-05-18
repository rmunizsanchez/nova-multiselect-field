<template>
  <div v-if="value.label.url">
    <div class="inline-block w-1/12" v-if="value.label.img">
      <img v-viewer class="w-auto"  :src="value.label.img" :alt="value.label.code" >
    </div>
    <div class="inline-block w-8/12">
      <span class="whitespace-no-wrap text-base font-semibold">{{ value.label.title }}</span><br>
      <span class="whitespace-no-wrap text-xs tracking-loose text-80">{{ value.label.url }}</span><br>
      <span class="whitespace-no-wrap text-base">{{ value.label.code }}</span>
    </div>
    <div class="inline-block w-2/12">
      <i class="cursor-pointer" v-on:click="$this.parent.removeList(i)">
        <svg class="w-4" xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
        </svg>
      </i>
    </div>
  </div>
  <span v-else>
    {{ s.label }}
  </span>
</template>
<script>
export default {
  props: ['field', 'value'],
  removeList(i) {
    $this.parent.removeList(i);
  }
};
</script>
