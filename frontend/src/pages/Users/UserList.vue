<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">User List</span>

        <div class="flex items-center justify-end">
          <q-input
            v-model="keyword"
            debounce="300"
            clearable
            dense
            filled
            bg-color="white"
            square
            placeholder="Search User"
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
              @click="filter = false"
            />
          </transition>
          <q-btn
            icon="add"
            label="Add User"
            text-color="white"
            color="secondary"
            class="q-ml-md"/>
        </div>

      </div>
      <transition name="slide-top" mode="out-in">
        <div v-if="filter" class="office-users__filters flex gap-sm q-mt-sm">
          <q-select
            bg-color="white"
            v-model="filters.status"
            dense
            filled
            square
            label="Status"
            style="min-width: 200px"
            :options="statusOptions"
          />
        </div>
        <div v-else-if="hasFilters">
          <q-chip
            v-for="(status) in filters.status"
            :key="status"
            removable
            color="primary"
            text-color="white"
            icon="check_circle"
            :ripple="false"
          >
            {{ status }}
          </q-chip>
        </div>
      </transition>
    </div>
    <DataTable
      v-model:pagination="pagination"
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      hide-bottom
      @request="getUsers"
    />
  </div>
</template>

<script setup>
import {computed, reactive, ref, watch} from "vue";
import {useQuasar} from "quasar";
import DataTable from "components/Table/DataTable.vue";

const $q = useQuasar()
const keyword = ref("");
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
const pagination = ref({
  sortBy: "created_at",
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
});
const statusOptions = ref(["Active", "Inactive"]);

const filters = reactive({
  status: [],
});

const hasFilters = computed(() => {
  return filters.status.length > 0;
});

const getUsers = async (props) => {
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = filters;

  pagination.value = props.pagination;

  const { data, meta } = await officeUserRequest.getOfficeUsers(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
};


watch(keyword, () => {
  getOffcieUsers    ({
    pagination: pagination.value,
  });
});


watch(
  () => filters.status,
  () => {
    getOffcieUsers({
      pagination: pagination.value,
    });
  }
);

</script>

<style lang="scss">

</style>
