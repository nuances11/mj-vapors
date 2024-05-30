<template>
  <div>
    <div class="card-border-shadow">
      <div class="q-px-sm q-py-sm text-white bg-grey-9">
        <div class="flex items-center justify-between">
          <span class="text-weight-regular text-h6">Time Tracking</span>

          <div class="flex items-center justify-end">
            <q-input
              v-model="keyword"
              debounce="300"
              clearable
              dense
              filled
              bg-color="white"
              square
              placeholder="Search Logs"
              :class="{ 'full-width q-mt-sm': $q.screen.lt.sm }"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
<!--            <q-btn-->
<!--              color="positive"-->
<!--              size="md"-->
<!--              icon="download"-->
<!--              text-color="white"-->
<!--              class="q-ml-md"-->
<!--              :class="$q.screen.lt.md ? 'full-width q-mt-xs' : ''"-->
<!--              label="Export as CSV"-->
<!--              :loading="loading"-->
<!--              @click="exportAsCsv"-->
<!--            />-->
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
        <transition name="slide-top" mode="out-in">
          <div v-if="filter" class="office-users__filters flex gap-sm q-mt-sm">
            <div class="row">
              <q-select
                bg-color="white"
                v-model="filters.user_id"
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
                :class="$q.screen.lt.md ? 'col q-mb-xs' : ''"
                clearable
              />
              <q-select
                bg-color="white"
                v-model="filters.branch_id"
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
                :class="$q.screen.lt.md ? 'col q-mb-xs' : 'q-ml-xs'"
                clearable
              />
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
        </transition>

      </div>

      <DataTable
        class="no-box-shadow no-border-radius"
        :rows="rows"
        :columns="columns"
        @request="getTimeTrackings"
        v-model:pagination="pagination"
        no-data-label="I didn't find anything for you"
        no-results-label="The filter didn't uncover any results"
        :is-transaction-history="true"
        :is-transaction-report="true"
        @row-click="onRowClick"
      />
    </div>
  </div>
</template>

<script setup>

import {useQuasar} from "quasar";
import {capitalize, onMounted, reactive, ref, watch} from "vue";
import DataTable from "components/Table/DataTable.vue";
import {useTimeTrackingHelper} from "src/composables/useTimeTrackingHelper";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useTimeTrackingRequest} from "src/composables/useTimeTrackingRequest";
import request from "src/helpers/request";
import {deepClone} from "src/helpers/common";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useUserRequest} from "src/composables/useUserRequest";

const branchRequest = useBranchRequest()
const userRequest = useUserRequest()
const timeTrackingHelper = useTimeTrackingHelper()
const timeTrackingRequest = useTimeTrackingRequest()
const commonHelper = useCommonHelper()
const $q = useQuasar()
const loading = ref(false)
const filter = ref(false)
const columns = ref([])
const rows = ref([])
const keyword = ref('')
const pagination = ref(commonHelper.defaultPagination())
const filters = reactive({
  user_id: null,
  branch_id: null,
});
const visibleColumns = ref([])
const userOptions = ref([])
const branchOptions = ref([])

const getBranchOptions = async () => {
  const { data } = await branchRequest.getBranches({
    display_all: true
  })

  branchOptions.value = data
}

const getUserOptions = async () => {
  const { data } = await userRequest.getUsers({
    display_all: true
  })

  userOptions.value = data
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

    let { data } = await timeTrackingRequest.getTimeTrackings(query);

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
    let filename = `Time_Tracking_Report-${timeStamp}.csv`;

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

const getColumn = () => {
  columns.value = timeTrackingHelper.getColumns()
  visibleColumns.value = timeTrackingHelper.getVisibleColumns()
}

const getTimeTrackings = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await timeTrackingRequest.getTimeTrackings(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
}

watch(keyword, () => {
  getTimeTrackings({
    pagination: pagination.value,
  });
});

watch(
  () => filters,
  () => {
    getTimeTrackings({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

onMounted(() => {
  getColumn()
  getTimeTrackings({
    pagination: pagination.value,
  });
  getBranchOptions()
  getUserOptions()
})

</script>

<style scoped lang="scss">

</style>
