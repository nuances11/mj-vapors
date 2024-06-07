<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Attribute List</span>

        <div class="flex items-center justify-end">
          <q-input
            v-model="keyword"
            debounce="300"
            clearable
            dense
            filled
            bg-color="white"
            square
            placeholder="Search Product"
            :class="{ 'full-width q-mt-sm': $q.screen.lt.sm }"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>

          <q-btn
            icon="add"
            text-color="white"
            color="secondary"
            class="q-ml-md"
            :loading="loading"
            label="Create Attribute"
            @click="createAttribute"
          />

          <q-page-sticky
            v-if="$q.screen.lt.sm"
            position="bottom-right"
            :offset="[18, 45]"
            style="z-index: 999"
          >
            <q-btn fab icon="add" color="light-blue-7" />
          </q-page-sticky>
        </div>
      </div>
    </div>

    <DataTable
      class="no-box-shadow no-border-radius"
      :rows="rows"
      :columns="columns"
      @request="getAttributes"
      v-model:pagination="pagination"
      @delete-item="deleteAttribute"
      @edit-item="editAttribute"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
    />

    <q-dialog class="alertDialog" persistent v-model="showProductAttributeFormDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-form
          ref="productAttributeFormRef"
          class="q-gutter-md"
          @submit="submitForm"
        >
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">{{ headerTitle }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-pa-md">


              <q-input
                filled
                v-model="form.name"
                label="Attribute Name"
                lazy-rules
                :rules="[ val => val && val.length > 0 || 'Please type something']"
              />

              <div class="text-h6">Options</div>

              <q-separator />

              <q-markup-table flat class="q-mt-md">
                <thead>
                <tr>
                  <th>Options</th>
                  <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="(option, index) in form.options" :key="`attr-${index}`">
                  <td>
                    <q-input
                      dense
                      square
                      class="q-ma-xs"
                      filled
                      v-model="form.options[index].value"
                      label="Option Value"
                      lazy-rules
                      :rules="[ val => val && val.length > 0 || 'Please type something']"
                      hide-bottom-space
                    />
                  </td>
                  <td class="text-center">

                    <q-btn
                      v-if="index === (form.options.length - 1)"
                      @click="addOption"
                      class="q-mr-xs"
                      size="sm"
                      color="green-6"
                      icon="add"
                      label="Add Field"
                      :loading="loading"
                    >
                      <q-tooltip>Add option field</q-tooltip>
                    </q-btn>
                    <template v-if="form.options[index].id">
                      <q-btn
                        class="q-mr-xs"
                        size="md"
                        color="red-6"
                        icon="delete"
                        label="Delete existing"
                        flat
                        :loading="loading"
                        @click="deleteOption(form.options[index].id, index)"
                      >
                        <q-tooltip>Delete existing option</q-tooltip>
                      </q-btn>
                    </template>

                    <template v-else>
                      <q-btn
                        v-if="index !== 0 || form.options.length > 1"
                        @click="removeOption(index)"
                        class="q-mr-xs"
                        size="sm"
                        color="red-6"
                        icon="delete"
                        label="Delete Field"
                        :loading="loading"
                      >
                        <q-tooltip>Delete field</q-tooltip>
                      </q-btn>
                    </template>

                  </td>
                </tr>
                </tbody>
              </q-markup-table>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="dialog_bottom">
          <q-btn flat label="Submit" color="primary" type="submit" />
          <q-btn flat label="Cancel" color="primary" @click="closeFormDialog" />
        </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

  </div>

</template>

<script setup>
import ProductAttributesTable from "components/products/ProductAttributesTable.vue";
import {onMounted, ref, watch} from "vue";
import {useAttributeRequest} from "src/composables/useAttributeRequest";
import {useAttributeHelper} from "src/composables/useAttributeHelper";
import {useQuasar} from "quasar";
import DataTable from "components/Table/DataTable.vue";
import {deepClone} from "src/helpers/common";

const loading = ref(false)
const productAttributeFormRef = ref(null)
const showProductAttributeFormDialog = ref(false)
const filter = ref(false)
const keyword = ref("")
const headerTitle = ref('Add Attribute')
const attributeRequest = useAttributeRequest()
const attributeHelper = useAttributeHelper()
const rows = ref([])
const columns = ref([])
const $q = useQuasar()
const isAddMode = ref(false)

const pagination = ref({
  sortBy: "created_at",
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
});

const form = ref({
  id: null,
  name: '',
  options: [],
})

const deleteOption = (optionId, index) => {
  loading.value = true
  $q.dialog({
    title: 'Delete Record',
    message: `Are you sure you want to delete this item?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await attributeRequest.deleteAttributeOption(optionId)
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
          form.value.options.splice(index, 1);
        }
      });

    loading.value = false
  }).onCancel(() => {
    loading.value = false
  })
}

const submitForm = async () => {
  if (isAddMode.value) await saveAttribute()
  else await updateAttribute()
}

const updateAttribute = async () => {
  const result = await productAttributeFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  try {
    await attributeRequest.updateAttribute(form.value.id, form.value)
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
          showProductAttributeFormDialog.value = false;
          refreshList()
        }
        loading.value = false
      });

  } catch (error) {
    console.log(error);
  }
}

const deleteAttribute = (props) => {
  loading.value = true
  $q.dialog({
    title: 'Delete Record',
    message: `Are you sure you want to delete <strong>${props.name}</strong>?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await attributeRequest.deleteAttribute(props.id)
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

const editAttribute = (props) => {
  isAddMode.value = false
  showProductAttributeFormDialog.value = true;
  let data = deepClone(props)
  form.value.id = data.id
  form.value.name = data.name
  form.value.options = data.attribute_options

}

const removeOption = (index) => {
  form.value.options.splice(index, 1);
}

const saveAttribute = async () => {
  const result = await productAttributeFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true;
  try {
    await attributeRequest.addAttribute(form.value)
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
          showProductAttributeFormDialog.value = false;
          refreshList()
        }
        loading.value = false;
      });

  } catch (error) {
    console.log(error);
  }
}

const refreshList = async () => {
  await getAttributes({
    pagination: pagination.value,
  })
}

const resetForm = () => {
  form.value.id = null;
  form.value.name = "";
  form.value.options = [];
}

const addOption = () => {
  form.value.options.push({
    attribute_id: null,
    value: '',
  })
}

const closeFormDialog = () => {
  resetForm()
  showProductAttributeFormDialog.value = false;
}

const createAttribute = () => {
  isAddMode.value = true;
  if(form.value.options.length === 0)
    addOption();
  showProductAttributeFormDialog.value = true;
}

const getColumns = () => {
  columns.value = attributeHelper.getColumns();
};

const getAttributes = async (props) => {
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;

  pagination.value = props.pagination;

  const { data, meta } = await attributeRequest.getAttributes(query)
  rows.value = data;

  pagination.value.per_page = meta.total;
  pagination.value.rowsNumber = meta.total;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
  pagination.value.page = meta.current_page;
  pagination.value.rows_per_page =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
};

watch(keyword, () => {
  getAttributes({
    pagination: pagination.value,
  })
});

onMounted(() => {
  getColumns()
  getAttributes({
    pagination: pagination.value,
  })
})

</script>

<style scoped lang="sass">

</style>
