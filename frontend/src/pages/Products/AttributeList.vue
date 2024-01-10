<template>
  <div>
    <div class="q-gutter-y-md">
      <div class="row" style="justify-content: space-between">
        <q-card class="my-card col-12">
          <q-card-section class="card-title-wrp">
            <div class="text-h6">Attribute Listing</div>
            <div class="text-subtitle2">{{ latestLabel }}</div>
          </q-card-section>
          <q-card-section class="q-pt-none list-tabs">
            <ProductAttributesTable></ProductAttributesTable>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>

</template>

<script setup>


import ProductAttributesTable from "components/products/ProductAttributesTable.vue";
</script>

<style scoped lang="sass">

</style>
