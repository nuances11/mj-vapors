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
            class="q-ml-md"
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
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      hide-bottom
      @request="getUsers"
      v-model:pagination="pagination"
      @delete-item="deleteUser"
      @edit-item="editUser"
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
              v-if="!isAddMode"
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
  </div>
</template>

<script setup>
import {computed, onMounted, reactive, ref, watch} from "vue";
import {useQuasar} from "quasar";
import DataTable from "components/Table/DataTable.vue";
import {useUserHelper} from "src/composables/useUserHelper";
import {useUserRequest} from "src/composables/useUserRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useValidationHelper} from "src/composables/useValidationHelper";

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
const userFormDialog = ref(false)
const isAddMode = ref(false);
const formTitle = ref('Add User');
const userFormRef = ref(null);

const filters = reactive({
  status: [],
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

const submitForm = async () => {
  if (isAddMode.value) await saveUser()
  // else await updateUser()
}

const saveUser = async () => {
  const result = await userFormRef.value.validate();
  if (!!!result) {
    return;
  }
  // loading.value = true;
  try {
    await userRequest.addUser(userForm.value)
      .then((response) => {
        if (!response.success) {
          $q.notify({
            type: "negative",
            message: response.message,
          });
        } else {
          $q.notify({
            type: "positive",
            message: response.message,
          });
        }
      });

    resetForm()
    userFormDialog.value = false;
    getUsers()

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

const closeFormDialog = () => {
  resetForm()
  userFormDialog.value = false;
}

const addUser = () => {
  isAddMode.value = true;
  userFormDialog.value = true;
}
const deleteUser = async (props) => {
  console.log('deleteUser', props)
}

const editUser = async (props) => {

  userForm.value = props
  isAddMode.value = false;
  userFormDialog.value = true;
  console.log('editUser', userForm.value)
}

const hasFilters = computed(() => {
  return filters.status.length > 0;
});

const getUsers = async (props) => {
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = filters;

  pagination.value = props.pagination;

  const { data, meta } = await userRequest.getUsers(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
};

const getColumns = () => {
  columns.value = userHelper.getColumns();
}


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

onMounted(() => {
  getColumns()
  getUsers({
    pagination: pagination.value,
  })
})

</script>

<style lang="scss">

</style>
