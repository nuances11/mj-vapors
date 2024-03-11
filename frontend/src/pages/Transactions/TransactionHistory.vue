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
    </div>
    <DataTable
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      @request="getTransactions"
      v-model:pagination="pagination"
      @delete-item="deleteTransaction"
      @edit-item="editTransaction"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
    />
  </div>

</template>

<script setup>

import {useTransactionRequest} from "src/composables/useTransactionRequest";
import {useTransactionHelper} from "src/composables/useTransactionHelper";
import {onMounted, reactive, ref} from "vue";
import {useQuasar} from "quasar";
import {useCommonHelper} from "src/composables/useCommonHelper";
import DataTable from "components/Table/DataTable.vue";

const transactionRequest = useTransactionRequest()
const transactionHelper = useTransactionHelper()
const commonHelper = useCommonHelper()

const $q = useQuasar();
const keyword = ref('')
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
const pagination = ref(commonHelper.defaultPagination());
const loading = ref(false)
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

const getColumns = () => {
  columns.value = transactionHelper.getColumns();
}

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

onMounted(() => {
  getColumns();
  getTransactions({
    pagination: pagination.value,
  });
})

</script>

<style lang="scss">

</style>
