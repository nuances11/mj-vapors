<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Branch List</span>

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
          <q-btn
            icon="add"
            label="Add Branch"
            text-color="white"
            color="secondary"
            class="q-ml-md"
            :loading="loading"
            @click="addBranch"
          />

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
            map-options
            option-label="label"
            option-value="value"
            emit-value
          />
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
      @request="getBranches"
      v-model:pagination="pagination"
      @delete-item="deleteBranch"
      @edit-item="editBranch"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
    />

    <q-dialog
      v-model="branchFormDialog"
      persistent
    >
      <q-card style="width: 700px; max-width: 80vw;">
        <q-form ref="branchFormRef" @submit="submitForm">
          <q-card-section>
            <div class="text-h6">{{ formTitle }}</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <q-select
              class="q-mb-md"
              dense
              filled
              v-model="branchForm.status"
              :options="statusOptions"
              map-options
              option-label="label"
              option-value="value"
              label="Branch Status *"
              hint="Select Branch Status"
              emit-value
              :rules="[(v) => !!v || 'Please select something']"
            />

            <q-input
              dense
              class="q-mb-md"
              filled
              v-model="branchForm.name"
              label="Branch Name *"
              hint="Input Branch Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />

          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" @click="closeFormDialog" />
            <q-btn flat label="Submit" type="submit" color="primary" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <q-dialog
      v-model="deleteBranchDialog"
      persistent
    >
      <q-card style="width: 300px">
        <q-form ref="deleteBranchFormRef" @submit="proceedDelete">
          <q-card-section>
            <div class="text-h6">Delete Branch</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            Are you sure you want to delete <strong>{{ deleteForm.name }}</strong>?
            <q-input
              dense
              class="q-mt-xs"
              filled
              square
              v-model="deleteForm.password"
              label="Your Password *"
              hint="Enter you password confirm."
              lazy-rules
              type="password"
              :rules="[ val => val && val.length > 0 || 'Please type something', passwordLength]"
            >
            </q-input>
          </q-card-section>

          <q-card-actions align="right" class="bg-white">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Delete" text-color="red" type="submit" />
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
import {useBranchHelper} from "src/composables/useBranchHelper";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useUserRequest} from "src/composables/useUserRequest";
import {useValidationHelper} from "src/composables/useValidationHelper";

const validationHelper = useValidationHelper()
const userRequest = useUserRequest()
const commonHelper = useCommonHelper()
const branchRequest = useBranchRequest()
const branchHelper = useBranchHelper()
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
const deleteBranchDialog = ref(false)
const deleteBranchFormRef = ref(null)
const isAddMode = ref(true);
const formTitle = ref('Add Branch')
const branchFormDialog = ref(false);
const branchFormRef = ref(null);
const branchForm = ref({
  name: '',
  status: null
})

const passwordLength = (password) => {
  return validationHelper.passwordLength(password) || "Password must be atleast 6 characters"
}

const iconFilters = {
  status: { inactive: "close", active: "check_circle" },
};

const hasFilters = computed(() => {
  return (
    filters.status !== null
  );
});

const deleteForm = ref({
  id: null,
  name: '',
  password: '',
})

const proceedDelete = async () => {
  const result = await deleteBranchFormRef.value.validate();
  if (!!!result) {
    return;
  }

  try {
    await userRequest.checkUserPassword(deleteForm.value.id, {id: deleteForm.value.id, password: deleteForm.value.password})
      .then(async (response) => {
        if (!response.data.matched) {
          $q.notify({
            type: "negative",
            icon: 'report_problem',
            message: response.message,
          });
        } else {
          await branchRequest.deleteBranch(deleteForm.value.id)
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
                deleteBranchDialog.value = false;
                deleteForm.value.id = null;
                deleteForm.value.name = '';
                deleteForm.value.password = '';
                refreshList()
              }
            });
        }
      });

  } catch (error) {
    console.log(error);
  }
}

const deleteBranch = async (props) => {
  deleteBranchDialog.value = true
  deleteForm.value.id = props.id
  deleteForm.value.name = props.name
}

const editBranch = async (props) => {

  formTitle.value = 'Update Branch'
  branchForm.value = commonHelper.deepClone(props)
  isAddMode.value = false;
  branchFormDialog.value = true;
}

const closeFormDialog = () => {
  resetForm()
  branchFormDialog.value = false;
}

const resetForm = () => {
  branchForm.value = {
    id: null,
    name: '',
    status: null
  }
}

const updateBranch = async () => {
  const result = await branchFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  try {
    await branchRequest.updateBranch(branchForm.value.id, branchForm.value)
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
          branchFormDialog.value = false;
          refreshList()
        }
        loading.value = false
      });

  } catch (error) {
    console.log(error);
  }
}

const refreshList = async () => {
  await getBranches({
    pagination: pagination.value,
  })
}

const saveBranch = async () => {
  const result = await branchFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true;
  try {
    await branchRequest.addBranch(branchForm.value)
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
          branchFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    console.log(error);
  }
}

const submitForm = async () => {
  if (isAddMode.value) await saveBranch()
  else await updateBranch()
}

const addBranch = () => {
  isAddMode.value = true;
  branchFormDialog.value = true;
}

const removeFilter = (selected, index) => {
  delete selected[index]
  // selected.splice(index, 1);
};

const getIcon = (key, value) => {
  if (key === "status") {
    return iconFilters["status"][value.toLowerCase()];
  } else {
    return iconFilters[key];
  }
};

const getColumns = () => {
  columns.value = branchHelper.getColumns();
}

const getBranches = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await branchRequest.getBranches(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

watch(keyword, () => {
  getBranches({
    pagination: pagination.value,
  });
});


watch(
  () => filters,
  () => {
    getBranches({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

onMounted(() => {
  getColumns()
  getBranches({
    pagination: pagination.value,
  });
})

</script>

<style scoped lang="scss">

</style>
