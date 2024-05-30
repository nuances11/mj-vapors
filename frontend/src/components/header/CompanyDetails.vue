<script setup>

const props = defineProps({
  companyLogo: {
    type: String,
    default: null,
  },
  companyName: {
    type: String,
    default: null,
  },
  currentBranch: {
    type: String,
    default: null,
  },
  isMobile: {
    type: Boolean,
    default: false
  }
})

</script>

<template>
  <div>
<!--    <q-btn flat no-caps no-wrap class="q-ml-xs" v-if="$q.screen.gt.xs">-->
    <q-btn flat no-caps no-wrap :class="q-ml-xs">
      <img
        alt="Theme logo"
        :src="companyLogo"
        style="width: 50px;"
      >
      <q-toolbar-title shrink class="text-weight-bold ">
        {{ companyName.toUpperCase() }}
      </q-toolbar-title>

      <q-toolbar-title shrink class="text-weight-bold">
        {{ currentBranch }}
      </q-toolbar-title>
    </q-btn>
  </div>
</template>

<style lang="scss">

</style>
