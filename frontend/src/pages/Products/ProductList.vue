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

import {useQuasar} from "quasar";
import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import {useProductRequest} from "src/composables/useProductRequest";
import {useAttributeRequest} from "src/composables/useAttributeRequest";
import {deepClone} from "src/helpers/common";
import {useListingHelper} from "src/composables/useListingHelper";
import ProductTab from "components/products/ProductTab.vue";
import ProductListingTab from "components/products/ProductListingTab.vue";


const productFormTitle = ref('Add Product')
const pagination = ref({
  sortBy: "created_at",
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
});
const filters = reactive({
  status: null,
});

const statusOptions = ref([
  {
    label: 'Active',
    value: 'active',
  },
  {
    label: 'Inactive',
    value: 'inactive',
  },
]);
const attributeOptions = ref([])
const attributeSelectionOptions = ref([])
const attributeOptionsLoading = ref(false)
const attributeSelectionOptionsLoading = ref([])

const productForm = ref({
  id: null,
  name: '',
  description: '',
})

const form = ref({
  id: null,
  price: 0,
  product_id: null,
  attribute_options: [],
})
const loading = ref(false);
const productListingFormDialog = ref(false);
const productFormDialog = ref(false);
const productListingFormRef = ref(null);
const isAddMode = ref(false)
const isAddProductMode = ref(false)
const attributeComponentKey = ref(0);
const attributeOptionsComponentKey = ref(0);
const attributeOptionSelectionRef = ref(null);
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
