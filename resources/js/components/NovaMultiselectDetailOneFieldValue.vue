<template>
  <div class="relative rounded-lg bg-white shadow border border-60" v-if="value.label.url">
    <div class="overflow-hidden rounded-b-lg rounded-t-lg">
        <div v-if="value.label.url" class="block">
            <div class="inline-block w-1/12">
              <img v-viewer class="w-auto" v-if="value.label.img" :src="value.label.img" :alt="value.label.code" >
            </div>
            <div class="inline-block w-8/12">
              <span class="whitespace-no-wrap text-base font-semibold">{{ value.label.title }}</span><br>
              <span class="whitespace-no-wrap text-xs tracking-loose text-80">{{ value.label.url }}</span><br>
              <span class="whitespace-no-wrap text-base">{{ value.label.code }}</span>
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
