<template>
  <div class="card-border-shadow q-mt-md">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Transaction History</span>

        <div class="flex items-center justify-end">
          <q-input
            v-model="keyword"
            debounce="300"
            clearable
            dense
            filled
            bg-color="white"
            square
            placeholder="Search Branch"
            :class="{ 'full-width q-mt-sm': $q.screen.lt.sm }"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
          <transition name="rotate-center" mode="out-in">
            <q-btn
              :class="$q.screen.lt.md ? 'full-width q-mt-sm' : ''"
              v-if="!filter"
              color="primary"
              size="md"
              icon="filter_alt"
              text-color="white"
              class="q-ml-md"
              label="Show Filters"
              :loading="loading"
              @click="filter = true"
            />
            <q-btn
              v-else
              :class="$q.screen.lt.md ? 'full-width q-mt-sm' : ''"
              color="negative"
              size="md"
              icon="close"
              text-color="white"
              class="q-ml-md"
              label="Close Filters"
              :loading="loading"
              @click="filter = false"
            />
          </transition>

        </div>
      </div>
      <transition name="slide-top" mode="out-in">
        <div v-if="filter" class="office-users__filters flex gap-sm q-mt-sm">
          <div class="row">
            <q-select
              :class="$q.screen.lt.md ? 'col q-mb-xs' : ''"
              bg-color="white"
              v-model="filters.status"
              dense
              filled
              square
              label="Status"
              style="min-width: 200px"
              :options="statusOptions"
              map-options
              option-label="label"
              option-value="value"
              emit-value
              clearable
            />

            <q-select
              :class="$q.screen.lt.md ? 'col q-mb-xs' : 'q-ml-xs'"
              bg-color="white"
              v-model="filters.transaction_type"
              dense
              filled
              square
              label="Transaction Type"
              style="min-width: 200px"
              :options="transactionTypeOptions"
              map-options
              option-label="label"
              option-value="value"
              emit-value
              clearable
            />

            <q-select
              :class="$q.screen.lt.md ? 'col q-mb-xs' : 'q-ml-xs'"
              bg-color="white"
              v-model="filters.user"
              dense
              filled
              square
              label="User"
              style="min-width: 200px"
              :options="userOptions"
              map-options
              option-label="full_name"
              clearable
            />

            <q-select
              :class="$q.screen.lt.md ? 'col q-mb-xs' : 'q-ml-xs'"
              bg-color="white"
              v-model="filters.branch"
              dense
              filled
              square
              label="Branch"
              style="min-width: 200px"
              :options="branchOptions"
              map-options
              option-label="name"
              clearable
            />
          </div>
        </div>
        <div v-else-if="hasFilters">
          <div class="row" v-for="(filter, key, index) in filters" :key="`filter-${index}`">
            <q-chip
              v-if="filter"
              removable
              color="primary"
              text-color="white"
              icon="search"
              :ripple="false"
              @remove="removeFilter(filters, key)"
            >
              <span v-if="key === 'user'">
                {{ filter.full_name }}
              </span>
              <span v-else-if="key === 'branch'">
                {{ filter.name }}
              </span>
              <span v-else>
                {{ commonHelper.titleCase(filter) }}
              </span>
<!--              {{ commonHelper.titleCase(filter) }}-->
            </q-chip>
          </div>
        </div>
      </transition>
    </div>
    <DataTable
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      @request="getTransactions"
      v-model:pagination="pagination"
      @showTransaction="showTransaction"
      @voidTransaction="voidTransaction"
      @cancelTransaction="cancelTransaction"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
      :is-transaction-history="true"
    />

    <q-dialog
      v-model="statusDialog"
      persistent
    >
      <q-card style="width: 400px">
        <q-form ref="transactionStatusFormRef" @submit="proceedUpdate">
          <q-card-section>
            <div class="text-h6">{{  statusTitle }}</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            Are you sure you want to {{ statusText }} this transaction?
            <q-input
              dense
              class="q-mt-xs"
              filled
              square
              v-model="statusForm.password"
              label="Your Password *"
              hint="Enter you password to confirm."
              lazy-rules
              type="password"
              :rules="[ val => val && val.length > 0 || 'Please type something', passwordLength]"
            >
            </q-input>
          </q-card-section>

          <q-card-actions align="right" class="bg-white">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Update" text-color="secondary" type="submit" />
          </q-card-actions>
        </q-form>
      </q-card>

    </q-dialog>

    <q-dialog class="alertDialog" persistent v-model="transactionDialog">
      <q-card style="width: 700px; max-width: 80vw;">
          <q-card-section class="card-section-header dialog-header">
            <div class="text-h6">Transaction #{{ currentTransactionId }}</div>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-mt-xs q-pt-xs">
            <div
              :class="!$q.screen.lt.md ? 'q-pa-md' : 'q-pa-none'"
            >
              <div class="row">
                <div class="col">
                  <strong>
                    Date:
                  </strong>
                  {{ readableDate(currentTransaction.created_at) }}
                </div>
                <div class="col">
                  <strong>
                    Status:
                  </strong>
                  <q-chip
                    size="xs"
                    :color="getStatusColor(currentTransaction.status)"
                    text-color="white"
                  >
                    {{ capitalize(currentTransaction.status) }}
                  </q-chip>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <strong>
                    Branch:
                  </strong>
                  {{ currentTransaction.branch.name ?? 'N/A' }}
                </div>
                <div class="col">
                  <strong>
                    Vendor:
                  </strong>
                  {{ currentTransaction.user.full_name ?? 'N/A' }}
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <strong>
                    Transaction Type:
                  </strong>
                  {{ capitalize(currentTransaction.transaction_type) ?? 'N/A' }}
                </div>
                <div class="col" v-if="currentTransaction.reference_number">
                  <strong>
                    Reference #:
                  </strong>
                  {{ capitalize(currentTransaction.reference_number) ?? 'N/A' }}
                </div>
              </div>
              <q-markup-table class="q-mt-md q-mb-md">
                <thead>
                <tr>
                  <th class="text-left">Qty</th>
                  <th>Item Name</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
                </thead>
                <tbody v-if="currentTransactionItems.length > 0">
                <tr v-for="(item, index) in currentTransactionItems" :key="index">
                  <td class="text-left">{{ item.quantity }}</td>
                  <td class="text-center">

                    <q-item>
                      <q-item-section>
                        <q-item-label class="text-bold">{{ item.product_name }}</q-item-label>
                        <q-item-label caption class="text-italic">{{ getAttributeLabel(item.skus.variants) }}</q-item-label>
                      </q-item-section>
                    </q-item>

                  </td>
                  <td class="text-center">{{ formatNumber(item.unit_price) }}</td>
                  <td class="text-center">{{ formatNumber(item.total_price) }}</td>
                </tr>
                </tbody>
              </q-markup-table>

              <div class="row">
                <div class="col">
                  <span class="text-h6">
                    Total Items:
                  </span>
                </div>
                <div class="col fit row wrap justify-end items-start content-start">
                  <span class="text-h6 justify-end">
                    {{ currentTransaction.total_items }}
                  </span>
                </div>
              </div>

              <div class="row">
                <div class="col">
                  <span class="text-h6">
                    Grand Total:
                  </span>
                </div>
                <div class="col fit row wrap justify-end items-start content-start">
                  <span class="text-h6 justify-end">
                    {{ formatNumber(currentTransaction.total_amount) }}
                  </span>
                </div>
              </div>

            </div>
          </q-card-section>

          <q-card-actions align="right" class="dialog_bottom">
            <q-btn flat label="Close" color="primary" v-close-popup />
          </q-card-actions>
      </q-card>
    </q-dialog>
  </div>

</template>

<script setup>

import {useTransactionRequest} from "src/composables/useTransactionRequest";
import {useTransactionHelper} from "src/composables/useTransactionHelper";
import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import {useQuasar} from "quasar";
import {useCommonHelper} from "src/composables/useCommonHelper";
import DataTable from "components/Table/DataTable.vue";
import {useProductRequest} from "src/composables/useProductRequest";
import {useValidationHelper} from "src/composables/useValidationHelper";
import {useUserRequest} from "src/composables/useUserRequest";
import {useBranchRequest} from "src/composables/useBranchRequest";

const branchRequest = useBranchRequest()
const userRequest = useUserRequest()
const validationHelper = useValidationHelper()
const productRequest = useProductRequest()
const transactionRequest = useTransactionRequest()
const transactionHelper = useTransactionHelper()
const commonHelper = useCommonHelper()

const $q = useQuasar();
const keyword = ref('')
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
// const pagination = ref(commonHelper.defaultPagination());
const pagination = ref({
  sortBy: "created_at",
  descending: true,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
});
const loading = ref(false)
const filters = reactive({
  status: null,
  transaction_type: null,
  user: null,
  branch: null,
});
const statusOptions = ref([
  {
    label: 'Voided',
    value: 'voided',
  },
  {
    label: 'Cancelled',
    value: 'cancelled',
  },
]);
const transactionTypeOptions = ref([
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

const transactionDialog = ref(false)
const currentTransactionId = ref('')
const currentTransactionItems = ref([])
const productList = ref([])
const currentTransaction = ref([])
const statusDialog = ref(false)
const statusForm = ref({
  id: null,
  status: '',
  password: '',
})
const statusTitle = ref('')
const statusText = ref('')
const transactionStatusFormRef = ref(null)

const hasFilters = computed(() => {
  return (
    filters.status !== null ||
    filters.transaction_type !== null ||
    filters.user !== null ||
    filters.branch !== null
  );
});
const userOptions = ref([])
const userOptionsLoading = ref(false)
const branchOptions = ref([])
const branchOptionsLoading = ref(false)

const removeFilter = (selected, index) => {
  delete selected[index]
};

const getStatusColor = (status) => {
  let color = 'green';
  if (status === 'voided') color = 'warning';
  if (status === 'cancelled') color = 'negative';

  return color;

}

const getBranch = async () => {
  branchOptionsLoading.value = true;
  let query = {}
  query.display_all = true
  query.for_options = true

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

const proceedUpdate = async () => {
  const result = await transactionStatusFormRef.value.validate();
  if (!!!result) {
    return;
  }

  try {
    await userRequest.checkUserPassword(statusForm.value.id, {id: statusForm.value.id, password: statusForm.value.password})
      .then(async (response) => {
        if (!response.data.matched) {
          $q.notify({
            type: "negative",
            icon: 'report_problem',
            message: response.message,
          });
        } else {
          await transactionRequest.updateTransactionStatus(statusForm.value.id, statusForm.value)
            .then(async (response) => {
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
                statusDialog.value = false;
                statusForm.value.id = null;
                statusForm.value.status = '';
                statusForm.value.password = '';
                await getTransactions({
                  pagination: pagination.value,
                });
              }
            });
        }
      });

  } catch (error) {
    console.log(error);
  }
}

const clearStatusForm = () => {
  statusForm.value = {
    id: null,
    status: '',
    password: '',
  }
}

const voidTransaction = (props) => {
  clearStatusForm();
  statusTitle.value = 'Void Transaction #' + props.id
  statusText.value = 'void'
  statusForm.value.id = props.id
  statusForm.value.status = 'voided'
  statusDialog.value = true;
}

const cancelTransaction = (props) => {
  clearStatusForm();
  statusTitle.value = 'Cancel Transaction #' + props.id
  statusText.value = 'cancel'
  statusForm.value.id = props.id
  statusForm.value.status = 'cancelled'
  statusDialog.value = true;
}

const passwordLength = (password) => {
  return validationHelper.passwordLength(password) || "Password must be atleast 6 characters"
}

const readableDate = (date) => {
  var d = new Date()

  return d.toLocaleString();
}
const getProductName = (id) => {
  let productData = productList.value.find((product) => {
    return product.id === id;
  })
  if (!productData) return ''

  return productData.name;
}

const getAttributeLabel = (variant) => {
  let label = []
  variant.forEach((item, index) => {
    label.push(`${item.attribute.name} : ${item.attribute_option.value}`)
  })

  return label.toString()
}

const showTransaction = async (props) => {
  await getProducts();
  let { data } = await transactionRequest.getTransactionItems(props.id)
  let transaction = data.transaction;
  let transactionItems = data.items;
  currentTransaction.value = transaction
  currentTransactionId.value = transaction.id
  currentTransactionItems.value = transactionItems
  for (const item of currentTransactionItems.value) {
    const index = currentTransactionItems.value.indexOf(item);
    item.product_name = await getProductName(item.skus.product_id)
  }

  transactionDialog.value = true;

}

const formatNumber = (number) => {
  return commonHelper.numberFormat(number)
}

const getColumns = () => {
  columns.value = transactionHelper.getColumns();
}

watch(keyword, () => {
  getTransactions({
    pagination: pagination.value,
  });
});

watch(
  () => filters,
  () => {
    getTransactions({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

const getTransactions = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await transactionRequest.getTransactions(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

const getProducts = async () => {
  let query = {}
  query.display_all = true;
  query.for_options = true;

  const { data } = await productRequest.getProducts(query);

  productList.value = data
};

onMounted(() => {
  getColumns();
  getUsers()
  getBranch()
  getTransactions({
    pagination: pagination.value,
  });
})

</script>

<style lang="scss">


</style>
