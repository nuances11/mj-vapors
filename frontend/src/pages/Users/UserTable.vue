<template>
  <div class="q-pa-md">
    <TableSkeleton v-if="props.loading"></TableSkeleton>

    <q-table
      v-else
      flat
      title="Users"
      v-bind="$attrs"
    >

    </q-table>

  </div>

</template>

<script setup>

import TableSkeleton from "components/skeleton/TableSkeleton.vue";

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
});
</script>

<style lang="scss">

</style>
