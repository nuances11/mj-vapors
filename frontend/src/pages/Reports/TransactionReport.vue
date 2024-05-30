<template>
  <div class="card-border-shadow">
    <div class="q-px-sm q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Transaction Report</span>

        <div class="flex items-center justify-end">
          <q-btn
            color="positive"
            size="md"
            icon="download"
            text-color="white"
            class="q-ml-md"
            :class="$q.screen.lt.md ? 'full-width q-mt-xs' : ''"
            label="Export as CSV"
            :loading="loading"
            @click="exportAsCsv"
          />
          <transition name="rotate-center" mode="out-in">
            <q-btn
              v-if="!filter"
              color="primary"
              size="md"
              icon="filter_alt"
              text-color="white"
              class="q-ml-md"
              :class="$q.screen.lt.md ? 'full-width q-mt-xs' : ''"
              label="Show Filters"
              :loading="loading"
              @click="filter = true"
            />
            <q-btn
              v-else
              color="negative"
              size="md"
              icon="close"
              text-color="white"
              class="q-ml-md"
              :class="$q.screen.lt.md ? 'full-width q-mt-xs' : ''"
              label="Close Filters"
              :loading="loading"
              @click="filter = false"
            />
          </transition>

        </div>

      </div>

      <q-slide-transition>
        <div v-if="filter">
          <div  class="row q-mt-sm">
            <q-input
              class="col"
              v-model="keyword"
              debounce="300"
              clearable
              dense
              filled
              bg-color="white"
              square
              placeholder="Search"
              :class="$q.screen.lt.md ? ' col-12 q-mb-xs' : ''"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
            <q-select
              class="col"
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
              :class="$q.screen.lt.md ? 'q-mb-xs' : 'q-ml-xs'"
            />
            <q-select
              class="col"
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
              :class="$q.screen.lt.md ? 'q-mb-xs' : 'q-ml-xs'"
            />
          </div>
          <div
            class="row"
            :class="!$q.screen.lt.md ? 'q-mt-sm' : ''"
          >
            <q-select
              class="col"
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
              :class="$q.screen.lt.md ? 'q-mb-xs' : 'q-ml-xs'"
            />
            <q-select
              class="col"
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
              :class="$q.screen.lt.md ? 'q-mb-xs' : 'q-ml-xs'"
            />

            <q-input
              bg-color="white"
              class="q-ml-xs col"
              label="Transaction Created"
              v-model="createdDateRange"
              dense

            >
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy
                    ref="dateProxy"
                    @hide="triggerFilter"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-date
                      v-model="filters.transaction_date"
                      range
                      mask="YYYY-MM-DD"
                    >
                      <div class="row items-center justify-end">
                        <q-btn
                          v-close-popup
                          label="Close"
                          color="primary"
                          flat
                        />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
              <template v-if="createdDateRange !== ''" v-slot:append>
                <q-icon
                  name="cancel"
                  @click.stop="filters.transaction_date = null"
                  class="cursor-pointer"
                ></q-icon>
              </template>
            </q-input>

          </div>
        </div>

        <div v-else-if="hasFilters">
          <div v-for="(filter, key, index) in filters" :key="`filter-${index}`">
            <q-chip
              v-if="filter"
              removable
              color="primary"
              text-color="white"
              :icon="getIcon(key, filter)"
              :ripple="false"
              @remove="removeFilter(filters, key)"
            >
              {{ capitalize(filter) }}
            </q-chip>
          </div>
        </div>
      </q-slide-transition>
    </div>

    <DataTable
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      @request="getTransactions"
      v-model:pagination="pagination"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
      :is-transaction-history="true"
      :is-transaction-report="true"
      @row-click="onRowClick"
    />

    <q-dialog class="alertDialog" persistent v-model="transactionDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">Transaction #{{ currentTransactionId }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-mt-xs q-pt-xs">
          <div class="q-pa-md">
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
                  Transaction Type:
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

import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import DataTable from "components/Table/DataTable.vue";
import {useTransactionRequest} from "src/composables/useTransactionRequest";
import {useTransactionHelper} from "src/composables/useTransactionHelper";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useUserRequest} from "src/composables/useUserRequest";
import {useQuasar} from "quasar";
import {deepClone} from "src/helpers/common";
import request from "src/helpers/request";
import {useProductRequest} from "src/composables/useProductRequest";

const $q = useQuasar();
const productRequest = useProductRequest()
const branchRequest = useBranchRequest()
const userRequest = useUserRequest()
const commonHelper = useCommonHelper()
const transactionRequest = useTransactionRequest()
const transactionHelper = useTransactionHelper()
const keyword = ref('')
const filter = ref(false)
const loading = ref(false)
const columns = ref([]);
const visibleColumns = ref([])
const rows = ref([]);
const pagination = ref(commonHelper.defaultPagination());
const transactionDate = ref('')
const filters = reactive({
  status: null,
  transaction_type: null,
  user: null,
  branch: null,
  transaction_date: null,
});

const hasFilters = computed(() => {
  return (
    filters.status !== null ||
    filters.transaction_type !== null ||
    filters.user !== null ||
    filters.branch !== null ||
    filters.transaction_date !== null
  );
});

const createdDateRange = computed(() => {
  if (filters.transaction_date !== null) {
    if (
      filters.transaction_date.from &&
      filters.transaction_date.to
    )
      return (
        filters.transaction_date.from +
        " to " +
        filters.transaction_date.to
      );
    if (filters.transaction_date.from)
      return filters.transaction_date.from;
    return filters.transaction_date;
  }

  return "";
})

const statusOptions = ref([
  {
    label: 'Success',
    value: 'success',
  },
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

const userOptions = ref([])
const userOptionsLoading = ref(false)
const branchOptions = ref([])
const branchOptionsLoading = ref(false)

const currentTransactionId = ref('')
const currentTransactionItems = ref([])
const currentTransaction = ref([])
const productList = ref([])
const transactionDialog = ref(false)

const triggerFilter = async () => {
  await getTransactions({
    pagination: pagination.value,
  });
}
const formatNumber = (number) => {
  return commonHelper.numberFormat(number)
}

const getAttributeLabel = (variant) => {
  let label = []
  variant.forEach((item, index) => {
    label.push(`${item.attribute.name} : ${item.attribute_option.value}`)
  })

  return label.toString()
}

const getStatusColor = (status) => {
  let color = 'green';
  if (status === 'voided') color = 'warning';
  if (status === 'cancelled') color = 'negative';

  return color;

}

const readableDate = (date) => {
  var d = new Date(date)

  return d.toLocaleString();
}

const getProductName = (id) => {
  let productData = productList.value.find((product) => {
    return product.id === id;
  })
  if (!productData) return ''

  return productData.name;
}

const getProducts = async () => {
  let query = {}
  query.display_all = true;
  query.for_options = true;

  const { data } = await productRequest.getProducts(query);

  productList.value = data
};

const onRowClick = async (evt, row) => {
  await getProducts();
  let { data } = await transactionRequest.getTransactionItems(row.id)
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
const getCsvHeader = () => {
  let header = deepClone(visibleColumns.value)
  return columns.value.filter(function (obj) {
    return header.includes(obj.name)
  }).map(function (obj) {
    return obj.label;
  });
}

const exportAsCsv = async () => {
  loading.value = true;
  $q.dialog({
    title: 'Export data to CSV',
    message: `Are you sure you want to generate this data?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {

    let header = getCsvHeader();
    let query = pagination.value;

    query.keyword = keyword.value;
    query.filters = JSON.stringify(filters);
    query.display_all = 1;

    let { data } = await transactionRequest.getTransactions(query);

    const basePath = process.env.API_ROUTE;
    let response = await request({
      url: `${basePath}/api/transactions/reports/export-to-csv`,
      data: {
        columns: visibleColumns.value,
        data: data,
        header: header
      },
      responseType: "blob", // default
      method: "post",
    });


    // let filename = `worksheet-template.xlsx`;
    let timeStamp = new Date().valueOf();
    let filename = `Transaction_Report-${timeStamp}.csv`;

    let blob = new Blob([response]);
    let link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();

    loading.value = false
  }).onCancel(() => {
    loading.value = false
  })
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

const getColumns = () => {
  columns.value = transactionHelper.getColumns();
  columns.value = columns.value.filter(function (obj) {
    return obj.name !== 'actions'
  })
  visibleColumns.value = transactionHelper.getVisibleColumns()
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
  console.log(props)
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
