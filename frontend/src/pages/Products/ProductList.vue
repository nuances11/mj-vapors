<template>
  <div class="card-border-shadow">
    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
      narrow-indicator
    >
      <q-tab
        name="products"
        label="Products"
        content-class="product-tabs"
        active-color="teal-10"
      />
      <q-tab
        name="listings"
        label="Listings"
        content-class="product-tabs"
        active-color="teal-10"
      />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="tab" animated>
      <q-tab-panel name="products">
        <ProductTab/>
      </q-tab-panel>

      <q-tab-panel name="listings">
        <ProductListingTab/>
      </q-tab-panel>
    </q-tab-panels>

  </div>

</template>

<script setup>

import {ref} from "vue";
import ProductTab from "components/products/ProductTab.vue";
import ProductListingTab from "components/products/ProductListingTab.vue";

const tab = ref('products')



</script>

<style lang="scss">

.product-tabs {

  .q-tab__label {
    font-size: 20px;

  }

.q-tab__indicator {
    height: 3px;
    &.text-primary {
      color: #424242 !important;
    }
  }

}

.q-tab--active {
  &.text-primary {
    color: #424242 !important;
  }
}

</style>
