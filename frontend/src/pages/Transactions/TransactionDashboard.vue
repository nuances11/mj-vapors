<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Add Transaction</span>

      </div>
    </div>
    <q-card class="my-card" flat bordered>

      <q-card-section horizontal>
        <q-card-section class="col-6">
          <q-select
            square
            clearable
            use-input
            class="q-mb-md"
            filled
            v-model="form.product"
            :options="options"
            map-options
            label="Product *"
            hint="Select Product"
            option-label="name"
            emit-value
            :rules="[(v) => !!v || 'Please select something']"
            @filter="filterFn"
            @update:model-value="showAttribute"
          >
            <template v-slot:no-option>
              <q-item>
                <q-item-section class="text-grey">
                  No results
                </q-item-section>
              </q-item>
            </template>
          </q-select>

          <div>
            <q-list bordered separator>

              <q-item v-for="(attribute, index) in productAttributes.skus" :key="index" clickable v-ripple>
                <q-item-section>
                  <q-item-label>{{ attribute.code }}</q-item-label>
                  <q-item-label caption>{{ getAttributeLabel(attribute.variants) }}</q-item-label>
                </q-item-section>
                <q-item-section side >
                  <q-btn
                    icon="add"
                    label="Add"
                    dense
                    filled
                    size="sm"
                    class="bg-grey-9 text-white"
                    @click="addProductToCart(attribute)"
                  >

                  </q-btn>
<!--                  <q-input style="max-width: 100px" bottom-slots v-model="text" label="Qty" dense>-->
<!--                    <template v-slot:prepend>-->
<!--                      <i style="color: red; font-size: small" class="fa-solid fa-minus"></i>-->
<!--                    </template>-->
<!--                    <template v-slot:append>-->
<!--                      <i class="fa-solid fa-plus" style="color:green; font-size: small"></i>-->
<!--                    </template>-->
<!--                  </q-input>-->
                </q-item-section>
              </q-item>

            </q-list>
          </div>
        </q-card-section>

        <q-separator vertical />

        <q-card-section class="col-6">
          <q-markup-table flat dense wrap-cells>
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td class="text-center">dasdadasdasdasdasdasdasdasdasdasd</td>
              <td class="text-center">123</td>
              <td class="text-center">12</td>
              <td class="text-center">111</td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card-section>

      <q-separator />

      <q-card-section class="flex items-center justify-between">
        <div class="text-bold text-h5">TOTAL</div>
        <div class="text-bold text-h5">0.00</div>
      </q-card-section>

    </q-card>

  </div>

</template>

<script setup>

import {onMounted, ref} from "vue";
import {useProductRequest} from "src/composables/useProductRequest";

const productRequest = useProductRequest()
const productOptionsLoading = ref(false)
const productOptions = ref([])
const form = ref({
  product: null,
  sku: null
})
const productDefaultOptions = ref([])
const options = ref(productOptions)
const skuData = ref([])
const productAttributes = ref([]);


const addProductToCart = (item) => {
  console.log(item)
}

const getAttributeLabel = (variant) => {
  let label = []
  variant.forEach((item, index) => {
    console.log(item)
    console.log(index)
    label.push(`${item.attribute.name} : ${item.attribute_option.value}`)
  })

  return label.toString()
}

const showAttribute = (value) => {
  console.log(value)
  productAttributes.value = value
}

const filterFn = (val, update) => {
  update(() => {
    if (val === "") productOptions.value = productDefaultOptions.value;
    else {
      const needle = val.toLowerCase();
      productOptions.value = productDefaultOptions.value.filter(
        (v) => v.name.toLowerCase().indexOf(needle) > -1
      );
    }
  });
}

const itemName = (item) => {
  return `${item.code} - ${item.product.name}`
}

const getProductOptions = async () => {
  productOptionsLoading.value = true;
  let query = {}
  query.display_all = true
  query.for_options = true

  const { data } = await productRequest.getProducts(query);
  console.log(data);
  skuData.value = data;
  let productData = data.map(v => v.product)
  productOptions.value = productData;
  productDefaultOptions.value = productData;

  productOptionsLoading.value = false;
};

onMounted(() => {
  getProductOptions()
})
</script>

<style lang="scss">

</style>
