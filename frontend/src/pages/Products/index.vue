
<template>
  <div>
    <div class="q-gutter-y-md">
      <div class="row" style="justify-content: space-between">
        <q-card class="my-card col-12">
          <q-card-section class="card-title-wrp">
            <div class="text-h6">Product Listing</div>
            <div class="text-subtitle2">{{ latestLabel }}</div>
          </q-card-section>
          <q-card-section class="q-pt-none list-tabs">
            <ProductsTable></ProductsTable>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script>

import {defineComponent, ref} from "vue";
import ProductsTable from "components/products/ProductsTable.vue";

export default defineComponent({
  name: 'MainLayout',
  components: {ProductsTable},

  setup() {

    const latestList = ref([])
    const latestCount = ref(0)
    const latestLabel = ref(0)

    return {

    }
  }
})
</script>
