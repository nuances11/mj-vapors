<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Add Transaction</span>

        <q-btn
          v-if="cartItem.length > 0"
          icon="fa-solid fa-money-bill-trend-up"
          label="Submit Transaction"
          text-color="white"
          color="secondary"
          class="q-ml-md"
          :loading="loading"
          @click="addTransaction"
        />

      </div>
    </div>
    <q-card class="my-card" flat bordered>

      <q-card-section horizontal>
        <q-card-section class="col-6">
          <div class="row">
            <div class="col-md-6" v-if="!isVendor">
              <q-select
                class="col-6 q-mb-md"
                bg-color="white"
                dense
                filled
                square
                label="User"
                style="min-width: 200px"
                :options="userOptions"
                map-options
                option-label="full_name"
                option-value="id"
                emit-value
                v-model="transactionForm.user_id"
                :rules="[(v) => !!v || 'Please select something']"
              />
            </div>
            <div class="col-6" v-if="!isVendor">
              <q-select
                class="col-6 q-mb-md"
                bg-color="white"
                dense
                filled
                square
                label="Branch"
                style="min-width: 200px"
                :options="branchOptions"
                map-options
                option-label="name"
                option-value="id"
                emit-value
                v-model="transactionForm.branch_id"
                @update:model-value="getProductOptions"
                :rules="[(v) => !!v || 'Please select something']"
              />
            </div>
          </div>

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
            <q-list bordered separator v-if="productAttributes.length">

              <q-item v-for="(attribute, index) in productAttributes" :key="index" clickable v-ripple>
                <q-item-section avatar>
                  <q-avatar color="grey-7" text-color="white">
                    {{ attribute.inventory[0].stock_quantity }}
                  </q-avatar>
<!--                  <span class=" text-center text-weight-medium"></span>-->
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ attribute.code }}</q-item-label>
                  <q-item-label caption>{{ getAttributeLabel(attribute.variants) }}</q-item-label>
                </q-item-section>
                <q-item-section side >

                  <div class="q-mt-sm" style="width: 100%">
                    <q-btn
                      icon="add"
                      label="Add"
                      dense
                      filled
                      size="sm"
                      class="bg-grey-9 text-white"
                      @click="addProductToCart(attribute, productAttributes)"
                      style="width: 100%"
                    >

                    </q-btn>
                  </div>

                </q-item-section>
              </q-item>

            </q-list>

            <div v-else>
              No listing found.
            </div>
          </div>
        </q-card-section>

        <q-separator vertical />

        <q-card-section class="col-6">
          <q-markup-table flat dense wrap-cells>
            <thead>
              <tr>
                <th class="text-left">Qty</th>
                <th>Item Name</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody v-if="cartItem.length > 0">
              <tr v-for="(item, index) in cartItem" :key="index">
                <td class="text-left">{{ item.qty }}</td>
                <td class="text-center">

                  <q-item>
                    <q-item-section>
                      <q-item-label>{{ item.product_name }}</q-item-label>
                      <q-item-label caption class="text-italic">{{ getAttributeLabel(item.variants) }}</q-item-label>
                    </q-item-section>
                  </q-item>

                </td>
                <td class="text-center">{{ formatNumber(item.price) }}</td>
                <td class="text-center">{{ formatNumber(item.sub_total) }}</td>
                <td class="text-center">
                  <q-icon
                    name="fa-regular fa-pen-to-square"
                    class="text-green q-mr-sm cursor-pointer"
                    @click="updateQuantity(1, item)"
                  />
                  <q-icon
                    name="fa-solid fa-trash-can"
                    class="text-red cursor-pointer"
                    @click="deleteCartItem(index)"
                  />
                </td>
              </tr>
            </tbody>
          </q-markup-table>
          <div class="totals" v-if="cartItem.length > 0">
            <div class="flex items-center justify-between q-mt-md">
              <div class="text-bold text-h6">ITEMS</div>
              <div class="text-bold text-h6">{{ totalItems }}</div>
            </div>
            <div class="flex items-center justify-between q-mt-xs">
              <div class="text-bold text-h6">TOTAL</div>
              <div class="text-bold text-h6">{{ formatNumber(grandTotal) }}</div>
            </div>
          </div>

        </q-card-section>
      </q-card-section>

    </q-card>

    <TransactionHistory :key="transactionHistoryKey"/>

    <q-dialog class="alertDialog" persistent v-model="transactionFormDialog">
      <q-card style="width: 900px; max-width: 80vw;">
        <q-form
          ref="transactionFormRef"
          class="q-gutter-md"
          @submit="submitTransactionForm"
        >
          <q-card-section class="card-section-header dialog-header">
            <div class="text-h6">Submit Transaction</div>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-mt-xs q-pt-xs">
            <div class="q-pa-md">

              <q-select
                bg-color="white"
                v-model="transactionForm.transaction_type"
                dense
                square
                label="Transaction Type"
                style="min-width: 200px"
                :options="transactionType"
                map-options
                option-label="label"
                option-value="value"
                emit-value
                class="q-mb-md"
                lazy-rules
                :rules="[(v) => !!v || 'Please select something']"
              />

              <q-input
                v-if="transactionForm.transaction_type !== null && transactionForm.transaction_type !== 'cash'"
                class="q-mb-md"
                filled
                dense
                v-model="transactionForm.bank"
                label="Bank Name"
                hint="Indicate recipient of the payment"
                lazy-rules
                :rules="[ val => val && val.length > 0 || 'Please type something']"
                hide-bottom-space
              />

              <q-input
                v-if="transactionForm.transaction_type !== null && transactionForm.transaction_type !== 'cash'"
                class="q-mb-md"
                filled
                dense
                v-model="transactionForm.reference_number"
                label="Reference Number"
                hint="Indicate reference of the payment"
                lazy-rules
                :rules="[ val => val && val.length > 0 || 'Please type something']"
                hide-bottom-space
              />

            </div>
          </q-card-section>

          <q-card-actions align="right" class="dialog_bottom">
            <q-btn flat label="Submit" color="primary" type="submit" />
            <q-btn flat label="Cancel" color="primary" @click="closeTransactionFormDialog" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

  </div>

</template>

<script setup>

import {computed, onMounted, ref} from "vue";
import {useProductRequest} from "src/composables/useProductRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useQuasar} from "quasar";
import {useTransactionRequest} from "src/composables/useTransactionRequest";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useUserRequest} from "src/composables/useUserRequest";
import TransactionHistory from "pages/Transactions/TransactionHistory.vue";
import {useListingRequest} from "src/composables/useListingRequest";
import {useUserStore} from "stores/user-store";

const userStore = useUserStore()
const $q = useQuasar()
const listingRequest = useListingRequest()
const branchRequest = useBranchRequest()
const userRequest = useUserRequest()
const transactionRequest = useTransactionRequest()
const commonHelper = useCommonHelper()
const productRequest = useProductRequest()
const productOptionsLoading = ref(false)
const productOptions = ref([])
const form = ref({
  product: null,
  sku: null
})
const transactionForm = ref({
  transaction_type: null,
  bank: null,
  user_id: null,
  branch_id: null,
  reference_number: null,
})
const transactionFormRef = ref(false)
const productDefaultOptions = ref([])
const options = ref(productOptions)
const skuData = ref([])
const productAttributes = ref([]);
const cartItem = ref([]);
const transactionFormDialog = ref(false)
const transactionType = ref([
  {
    label: 'Cash',
    value: 'cash',
  },
  {
    label: 'Bank Transfer',
    value: 'bank_transfer',
  },
  {
    label: 'GCash',
    value: 'gcash',
  },
  {
    label: 'Maya',
    value: 'maya',
  },
]);
const userOptions = ref([])
const branchOptions = ref([])
const loading = ref(false)
const branchOptionsLoading = ref(false)
const userOptionsLoading = ref(false)
const transactionHistoryKey = ref(0)

const userType = computed(() => userStore.user.user_type)
const isVendor = computed(() => userStore.user.user_type === 'vendor')

const submitTransactionForm = async () => {
  await saveTransaction()
  transactionHistoryKey.value++
}

const clearTransaction = () => {
  form.value.product = null;
  transactionForm.value.branch_id = null;
  transactionForm.value.user_id = null;
  clearData();
  cartItem.value = [];
}

const getBranch = async () => {
  branchOptionsLoading.value = true;
  let query = {}
  query.display_all = true
  query.for_options = true
  query.get_active_branch = true

  const { data } = await branchRequest.getBranches(query);
  branchOptions.value = data;

  branchOptionsLoading.value = false;
}

const getUsers = async () => {
  userOptionsLoading.value = true;
  let query = {}
  query.display_all = true
  query.for_options = true
  query.for_transaction = true

  const { data } = await userRequest.getUsers(query);
  userOptions.value = data;

  userOptionsLoading.value = false;
}

const saveTransaction = async () => {
  const result = await transactionFormRef.value.validate();
  if (!!!result) {
    return;
  }

  if (!transactionForm.value.user_id)
    transactionForm.value.user_id = userStore.user.id

  if (!transactionForm.value.branch_id)
    transactionForm.value.branch_id = userStore.user.branch.id

  let formData = {
    transaction_form : transactionForm.value,
    items : cartItem.value,
    total_amount : grandTotal.value
  }

  loading.value = true;
  try {
    await transactionRequest.addTransaction(formData)
      .then((response) => {
        if (!response.success) {
          $q.notify({
            type: "negative",
            icon: 'report_problem',
            message: response.message,
          });
        } else {
          $q.notify({
            type: "positive",
            icon: 'check_circle',
            message: response.message,
          });
          refreshTransactionForm()
          transactionFormDialog.value = false;
          clearTransaction()
        }
        loading.value = false;
      });

  } catch (error) {
    loading.value = false;
    $q.notify({
      type: "negative",
      icon: 'report_problem',
      message: error.response.data.message,
    });
  }
}

const closeTransactionFormDialog = () => {
  refreshTransactionForm();
  transactionFormDialog.value = false;
  loading.value = false
}

const refreshTransactionForm = () => {
  transactionForm.value.transaction_type = null;
  transactionForm.value.bank = null;
  transactionForm.value.reference_number = null;
}

const addTransaction = () => {
  transactionFormDialog.value = true;
}

const deleteCartItem = (index) => {

  $q.dialog({
    title: 'Delete cart item',
    message: 'Are you sure you want to delete this item?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    cartItem.value.splice(index, 1);
  })
}

const updateQuantity = (step, item) => {
  let qty = item.qty
  $q.dialog({
    title: 'Update quantity',
    prompt: {
      model: qty,
      type: 'number',

      // native attributes:
      min: 1,
      step: 1
    },
    cancel: true,
    persistent: true
  }).onOk(data => {
    console.log('>>>> OK, received', data)
    item.qty = parseInt(data);
    item.sub_total = item.qty * item.price
  }).onCancel(() => {
    console.log('>>>> Cancel')
  })
}

const formatNumber = (number) => {
  return commonHelper.numberFormat(number)
}

const clearData = () => {
  skuData.value = [];
  productAttributes.value = [];
}

const addProductToCart = (item, productAttributes) => {
  console.log(item);
  let index = cartItem.value.findIndex(x => x.code === item.code);

  if (index === -1) {
    item.product_name = productAttributes.name
    item.qty = 1;
    item.sub_total = item.qty * parseFloat(item.price)
    cartItem.value.push(item);
  } else {
    cartItem.value[index].qty += 1
    item.sub_total = item.qty * parseFloat(item.price)
  }

}

const totalItems = computed(() => {
  return cartItem.value.reduce((accumulator , item) => {
    return accumulator  + item.qty;
  }, 0);

})

const grandTotal = computed(() => {
  return cartItem.value.reduce((accumulator , item) => {
    return accumulator + item.sub_total;
  }, 0);
})

const getAttributeLabel = (variant) => {
  let label = []
  variant.forEach((item, index) => {
    label.push(`${item.attribute.name} : ${item.attribute_option.value}`)
  })

  return label.toString()
}

const showAttribute = async (value) => {
  console.log(value);
  if (value) {
    // productAttributes.value = value
    let query = {};
    query.display_all = true;
    query.branch_id = transactionForm.value.branch_id ?? userStore.user.branch.id
    query.product_id = form.value.product
    // query.

    let { data } = await listingRequest.getListings(query)
    productAttributes.value = data
  } else {
    productAttributes.value = []
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
  productAttributes.value = []
  form.value.product = null;
  productOptionsLoading.value = true;
  let query = {}
  query.display_all = true
  query.for_options = true
  query.branch_id = transactionForm.value.branch_id

  const { data } = await productRequest.getProducts(query);
  skuData.value = data.skus;
  let productData = data
  productOptions.value = data;
  productDefaultOptions.value = productData;

  productOptionsLoading.value = false;
};

onMounted(() => {
  getProductOptions()
  getBranch()
  getUsers()
})
</script>

<style lang="scss">

</style>
