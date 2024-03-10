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
            @clear="clearData"
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
            <q-list bordered separator v-if="productAttributes">

              <q-item v-for="(attribute, index) in productAttributes.skus" :key="index" clickable v-ripple>
                <q-item-section>
                  <q-item-label>{{ attribute.code }}</q-item-label>
                  <q-item-label caption>{{ getAttributeLabel(attribute.variants) }}</q-item-label>
                </q-item-section>
                <q-item-section side >
<!--                  <div>-->
<!--                    <q-input square style="max-width: 100px" v-model="attribute[index].qty" label="Qty" dense>-->
<!--                      <template v-slot:prepend>-->
<!--                        <i style="color: red; font-size: small" class="fa-solid fa-minus"></i>-->
<!--                      </template>-->
<!--                      <template v-slot:append>-->
<!--                        <i class="fa-solid fa-plus" style="color:green; font-size: small"></i>-->
<!--                      </template>-->
<!--                    </q-input>-->
<!--                  </div>-->

                  <div class="q-mt-sm" style="width: 100%">
                    <q-btn
                      icon="add"
                      label="Add"
                      dense
                      filled
                      size="sm"
                      class="bg-grey-9 text-white"
                      @click="addProductToCart(attribute, index)"
                      style="width: 100%"
                    >

                    </q-btn>
                  </div>

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
      {{ totalItems }}
      <q-card-section class="flex items-center justify-between">
        <div class="text-bold text-h5">TOTAL</div>
        <div class="text-bold text-h5">0.00</div>
      </q-card-section>

    </q-card>

  </div>

</template>

<script setup>

import {computed, onMounted, ref} from "vue";
import {useProductRequest} from "src/composables/useProductRequest";
import {deepClone} from "src/helpers/common";

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
const cartItem = ref([]);


const clearData = () => {
  skuData.value = [];
  productAttributes.value = [];
}

const addProductToCart = (item, index) => {
  // console.log(item)
  // console.log(cartItem.value)
  // let productToCart = deepClone(item)
  // item.qty = 1;
  // const exists = cartItem.value.some(obj => obj.code === item.code);
  // if (!exists) {
  //   cartItem.value.push(item)
  // } else {
  //
  // }
  let found = false;

  // Add the item or increase qty
  let itemInCart = cartItem.value.filter(obj => obj.code === item.code);
  let isItemInCart = itemInCart.length > 0;

  if (isItemInCart === false) {
    cartItem.value.push(item);
  } else {
    itemInCart[0].qty += 1;
  }

  item.qty = 1;

}

const totalItems = computed(() => {
  return cartItem.value.reduce((accumulator , item) => {
    return accumulator  + item.qty;
  }, 0);

})

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
  if (value) {
    productAttributes.value = value
  }

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
  skuData.value = data.skus;
  let productData = data
  productOptions.value = data;
  productDefaultOptions.value = productData;

  productOptionsLoading.value = false;
};

onMounted(() => {
  getProductOptions()
})
</script>

<style lang="scss">

</style>
