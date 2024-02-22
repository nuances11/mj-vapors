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
            label="Add User"
            text-color="white"
            color="secondary"
            class="q-ml-md"
            :loading="loading"
            @click="addUser"
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
      @request="getUsers"
      v-model:pagination="pagination"
      @delete-item="deleteUser"
      @edit-item="editUser"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
    />

    <q-dialog
      v-model="userFormDialog"
      persistent
    >
      <q-card style="width: 700px; max-width: 80vw;">
        <q-form ref="userFormRef" @submit="submitForm">
          <q-card-section>
            <div class="text-h6">{{ formTitle }}</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <q-select
              class="q-mb-md"
              v-if="!isAddMode && userForm.id !== parseInt(currentUserId)"
              dense
              filled
              v-model="userForm.status"
              :options="statusOptions"
              map-options
              option-label="label"
              option-value="value"
              label="Status"
              hint="Input Status"
              emit-value
            />
            <q-select
              v-if="userForm.id !== parseInt(currentUserId)"
              class="q-mb-md"
              dense
              filled
              v-model="userForm.user_type"
              :options="userTypeOptions"
              map-options
              option-label="label"
              option-value="value"
              label="User Type"
              hint="Input User Type"
              emit-value
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />

            <q-input
              dense
              class="q-mb-md"
              filled
              v-model="userForm.first_name"
              label="Your First Name *"
              hint="Input First Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />
            <q-input
              dense
              class="q-mb-md"
              filled
              v-model="userForm.last_name"
              label="Your Last Name *"
              hint="Input Last Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />
            <q-input
              dense
              class="q-mb-md"
              filled
              v-model="userForm.email"
              label="Your Email *"
              hint="Input Email"
              lazy-rules
              type="email"
              :rules="[ val => val && val.length > 0 || 'Please type something', isEmailValid]"
            />

            <q-input
              dense
              class="q-mb-md"
              filled
              v-model="userForm.user_name"
              label="Your User Name *"
              hint="Input User Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />

            <q-input
              v-if="isAddMode"
              dense
              class="q-mb-md"
              filled
              v-model="userForm.password"
              label="Your Password *"
              hint="Input Password"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something', passwordLength]"
            >
              <template #append>
                <q-icon
                  class="q-mr-xs cursor-pointer clickable-icons"
                  name="change_circle"
                  @click="generatePassword"
                >
                  <q-tooltip>Generate Password</q-tooltip>
                </q-icon>
                <q-icon
                  class="cursor-pointer clickable-icons"
                  name="content_copy"
                  @click="copyToClipboard(userForm.password)"
                >
                  <q-tooltip>Copy to clipboard</q-tooltip>
                </q-icon>
              </template>
            </q-input>

          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" @click="closeFormDialog" />
            <q-btn flat label="Submit" type="submit" color="primary" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <q-dialog
      v-model="deleteUserDialog"
      persistent
    >
      <q-card style="width: 300px">
        <q-form ref="deleteUserFormRef" @submit="proceedDelete">
          <q-card-section>
            <div class="text-h6">Delete User</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            Are you sure you want to delete <strong>{{ deleteForm.full_name }}</strong>?
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
import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import {useQuasar} from "quasar";
import DataTable from "components/Table/DataTable.vue";
import {useUserHelper} from "src/composables/useUserHelper";
import {useUserRequest} from "src/composables/useUserRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useValidationHelper} from "src/composables/useValidationHelper";
import Cookies from "js-cookie";

const validationHelper = useValidationHelper()
const commonHelper = useCommonHelper()
const userHelper = useUserHelper()
const userRequest = useUserRequest()
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
const userTypeOptions = ref([
  {
    label: 'Admin',
    value: 'admin',
  },
  {
    label: 'Vendor',
    value: 'vendor',
  },
]);
const iconFilters = {
  status: { inactive: "close", active: "check_circle" },
};
const userFormDialog = ref(false)
const isAddMode = ref(false);
const formTitle = ref('Add User');
const userFormRef = ref(null);
const deleteUserFormRef = ref(null);
const currentUserId = Cookies.get("user_id");
const deleteUserDialog = ref(false);
const loading = ref(false);

const filters = reactive({
  status: null,
});

const userForm = ref({
  id: null,
  first_name: '',
  last_name: '',
  user_type: null,
  email: null,
  user_name: null,
  password: '',
  status: null
})

const deleteForm = ref({
  id: null,
  full_name: '',
  password: '',
})

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

const submitForm = async () => {
  if (isAddMode.value) await saveUser()
  else await updateUser()
}

const updateUser = async () => {
  const result = await userFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  try {
    await userRequest.updateUser(userForm.value.id, userForm.value)
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
          userFormDialog.value = false;
          refreshList()
        }
        loading.value = false
      });

  } catch (error) {
    console.log(error);
  }
}

const saveUser = async () => {
  const result = await userFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true;
  try {
    await userRequest.addUser(userForm.value)
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
          userFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    console.log(error);
  }
}

const passwordLength = (password) => {
  return validationHelper.passwordLength(password) || "Password must be atleast 6 characters"
}

const copyToClipboard = (string) => {
  commonHelper.copyToClipboard(string)
}
const generatePassword = () => {
  userForm.value.password = commonHelper.useGeneratePassword()
}

const isEmailValid = (email) => {
  return validationHelper.emailValidation(email) || "Invalid email";
};

const resetForm = () => {
  userForm.value = {
    id: null,
    first_name: '',
    last_name: '',
    user_type: null,
    email: null,
    user_name: null,
    password: '',
    status: null
  }
}


const refreshList = async () => {
  await getUsers({
    pagination: pagination.value,
  })
}
const closeFormDialog = () => {
  resetForm()
  userFormDialog.value = false;
}

const addUser = () => {
  isAddMode.value = true;
  userFormDialog.value = true;
}

const proceedDelete = async () => {
  const result = await deleteUserFormRef.value.validate();
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
          await userRequest.deleteUser(deleteForm.value.id)
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
                deleteUserDialog.value = false;
                deleteForm.value.id = null;
                deleteForm.value.full_name = '';
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

const deleteUser = async (props) => {
  deleteUserDialog.value = true
  deleteForm.value.id = props.id
  deleteForm.value.full_name = props.full_name
}

const editUser = async (props) => {

  formTitle.value = 'Update User'
  userForm.value = commonHelper.deepClone(props)
  isAddMode.value = false;
  userFormDialog.value = true;
}

const hasFilters = computed(() => {
  return (
    filters.status !== null
  );
});

const getUsers = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await userRequest.getUsers(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

const getColumns = () => {
  columns.value = userHelper.getColumns();
}


watch(keyword, () => {
  getUsers({
    pagination: pagination.value,
  });
});


watch(
  () => filters,
  () => {
    getUsers({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

onMounted(() => {
  getColumns()
  getUsers({
    pagination: pagination.value,
  })
})

</script>

<style lang="scss">

</style>
