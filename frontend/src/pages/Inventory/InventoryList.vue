<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Inventory List</span>

        <div class="flex items-center justify-end">
          <q-input
            v-model="keyword"
            debounce="300"
            clearable
            dense
            filled
            bg-color="white"
            square
            placeholder="Search Inventory"
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
          <q-btn
            icon="add"
            label="Add Inventory"
            text-color="white"
            color="secondary"
            class="q-ml-md"
            :loading="loading"
            @click="addInventory"
          />

        </div>

      </div>

      <transition name="slide-top" mode="out-in">
        <div v-if="filter" class="office-users__filters flex gap-sm q-mt-sm">

          <q-select
            class="q-ml-xs"
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
      @request="getInventory"
      v-model:pagination="pagination"
      @delete-item="deleteInventory"
      @edit-item="editInventory"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
    />

    <q-dialog
      v-model="inventoryFormDialog"
      persistent
    >
      <q-card style="width: 700px; max-width: 80vw;">
        <q-form ref="inventoryFormRef" @submit="submitForm">
          <q-card-section>
            <div class="text-h6">{{ formTitle }}</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <q-select
              class="q-mb-md"
              dense
              filled
              v-model="form.branch_id"
              :options="branchOptions"
              map-options
              option-label="name"
              option-value="id"
              label="Branch *"
              hint="Select Branch"
              emit-value
              :rules="[(v) => !!v || 'Please select something']"
            />

            <q-select
              class="q-mb-md"
              dense
              filled
              v-model="form.skus_id"
              :options="skusOptions"
              map-options
              :option-label="itemName"
              option-value="id"
              label="SKU *"
              hint="Select SKU"
              emit-value
              :rules="[(v) => !!v || 'Please select something']"
            />

            <q-input
              type="number"
              dense
              class="q-mb-md"
              filled
              v-model="form.stock_quantity"
              label="Stock Qty *"
              hint="Input Quantity"
              lazy-rules
              :rules="[ val => val && val > 0 || 'Please type something']"
            />

          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" @click="closeFormDialog" />
            <q-btn flat label="Submit" type="submit" color="primary" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

  </div>

</template>

<script setup>

import {useQuasar} from "quasar";
import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import DataTable from "components/Table/DataTable.vue";
import {useInventoryHelper} from "src/composables/useInventoryHelper";
import {useInventoryRequest} from "src/composables/useInventoryRequest";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useProductRequest} from "src/composables/useProductRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useListingRequest} from "src/composables/useListingRequest";

const listingRequest = useListingRequest()
const commonHelper = useCommonHelper()
const productRequest = useProductRequest()
const branchRequest = useBranchRequest()
const inventoryRequest = useInventoryRequest()
const inventoryHelper = useInventoryHelper()
const $q = useQuasar();
const keyword = ref('')
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
const loading = ref(false)
const inventoryFormDialog = ref(false)
const inventoryFormRef = ref(null)
const formTitle = ref('Add Inventory')
const form = ref({
  id: null,
  branch_id : null,
  skus_id : null,
  stock_quantity: 0,
})
const loadingBranches = ref(false)
const loadingSkus = ref(false)
const isAddMode = ref(false)
const branchOptions = ref([])
const skusOptions = ref([])
const filters = reactive({
  branch: null,
});

const hasFilters = computed(() => {
  return (
    filters.branch !== null
  );
});

watch(keyword, () => {
  getInventory({
    pagination: pagination.value,
  });
});

watch(
  () => filters,
  () => {
    getInventory({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

const removeFilter = (selected, index) => {
  delete selected[index]
};

const submitForm = async () => {
  if (isAddMode.value) await saveInventory()
  else await updateInventory()
}

const saveInventory = async () => {
  const result = await inventoryFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true;
  try {
    await inventoryRequest.addInventory(form.value)
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
          resetForm()
          inventoryFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    $q.notify({
      type: "negative",
      icon: 'report_problem',
      message: error.response.data.message,
    });
  }
}

const updateInventory = async () => {
  const result = await inventoryFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  try {
    await inventoryRequest.updateInventory(form.value.id, form.value)
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
          resetForm()
          inventoryFormDialog.value = false;
          refreshList()
        }
        loading.value = false
      });

  } catch (error) {
    $q.notify({
      type: "negative",
      icon: 'report_problem',
      message: error.response.data.message,
    });
  }
}

const deleteInventory = async (props) => {
  loading.value = true
  $q.dialog({
    title: 'Delete Record',
    message: `Are you sure you want to delete this inventory record?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await inventoryRequest.deleteInventory(props.id)
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
          refreshList()
        }
      });

    loading.value = false
  })
}

const refreshList = async () => {
  await getInventory({
    pagination: pagination.value,
  })
}

const editInventory = async (props) => {
  await getBranches();
  await getProducts()
  formTitle.value = 'Update Inventory'
  form.value = commonHelper.deepClone(props)
  isAddMode.value = false;
  inventoryFormDialog.value = true;
}

const itemName = (item) => {
  return `${item.code} - ${item.product.name}`
}

const closeFormDialog = () => {
  resetForm()
  inventoryFormDialog.value = false;
  loading.value = false
}

const resetForm = () => {
  form.value = {
    id: null,
    branch_id : null,
    skus_id : null,
    stock_quantity: 0,
  }
}

const addInventory = async () => {
  isAddMode.value = true
  await getBranches();
  await getProducts()
  inventoryFormDialog.value = true
}

const getColumns = () => {
  columns.value = inventoryHelper.getColumns();
}

const getInventory = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);
  query.get_active_branch = true;

  pagination.value = props.pagination;

  const { data, meta } = await inventoryRequest.getInventories(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

const getBranches = async () => {
  loadingBranches.value = true;
  let query = {}
  query.display_all = true
  query.get_active_branch = true;

  const { data } = await branchRequest.getBranches(query);
  if (data) {
    branchOptions.value = data
  }

  loadingBranches.value = false;
};

const getProducts = async (props) => {
  loading.value = true;
  let query = {}
  query.display_all = true

  // const { data } = await productRequest.getProducts(query);
  const { data } = await listingRequest.getListings()
  console.log(data);
  if (data) skusOptions.value = data
};

onMounted(() => {
  getColumns()
  getBranches()
  getInventory({
    pagination: pagination.value,
  });
})

</script>

<style lang="scss">

</style>
