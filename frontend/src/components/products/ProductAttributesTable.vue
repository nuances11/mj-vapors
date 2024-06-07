<template>
<div class="q-pa-md products-attribute-table">
  <TableSkeleton v-if="loading"></TableSkeleton>
  <q-table
    v-else
    flat
    title="Attributes"
    :rows="attributeRows"
    :columns="attributeColumns"
    color="primary"
    row-key="name"
    @request="getAttributes"
    v-model:pagination="pagination"
  >
    <template v-slot:top>

      <q-space v-if="!$q.screen.lt.sm" />

      <q-input
        borderless
        dense
        debounce="300"
        class="search-item-cat"
        v-model="keyword"
        placeholder="Search"
        :class="$q.screen.lt.sm ? 'full-width' : ''"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>

      <q-btn
        v-if="!$q.screen.lt.sm"
        unelevated
        :disable="loading"
        class="primary_btn"
        icon="add"
        size="md"
        padding="sm md"
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
    </template>
    <template #body-cell-attribute_options="props">
      <q-td :props="props">
        <span v-if="props.row.attribute_options">
          <q-chip square v-for="(option, index) in props.row.attribute_options" :key="index">
            {{ option.value }}
          </q-chip>

        </span>
        <span v-else>
              N/A
        </span>
      </q-td>

    </template>

    <template #body-cell-actions="props" >
      <q-td :props="props">
        <q-btn
          class="q-mr-xs"
          size="sm"
          color="blue-6"
          icon="edit"
          label="Edit"
          @click="editAttribute(props.row)"
        >
          <q-tooltip>Edit</q-tooltip>
        </q-btn>

        <q-btn
          class="q-mr-xs"
          size="sm"
          color="red-6"
          icon="delete"
          label="Delete"

        >
          <q-tooltip>Delete</q-tooltip>
        </q-btn>
      </q-td>
    </template>

  </q-table>

  <q-dialog class="alertDialog" persistent v-model="showProductAttributeFormDialog">
    <q-card style="width: 700px; max-width: 80vw;">
      <q-card-section class="card-section-header dialog-header">
        <div class="text-h6">{{ headerTitle }}</div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="q-pa-md">

          <q-form
            ref="productAttributeFormRef"
            class="q-gutter-md"
          >
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
                    filled
                    dense
                    v-model="form.options[index].value"
                    label="Option Value"
                    lazy-rules
                    :rules="[ val => val && val.length > 0 || 'Please type something']"
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

                    >
                      <q-tooltip>Delete field</q-tooltip>
                    </q-btn>
                  </template>


                </td>
              </tr>
              </tbody>
            </q-markup-table>

          </q-form>

        </div>
      </q-card-section>

<!--      <q-separator />-->

      <q-card-actions align="right" class="dialog_bottom">
        <q-btn flat label="Submit" color="primary" @click="saveAttribute" />
        <q-btn flat label="Cancel" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>

</div>
</template>

<script setup>

import TableSkeleton from "components/skeleton/TableSkeleton.vue";
import {onMounted, ref} from "vue";
import {useAttributeRequest} from "src/composables/useAttributeRequest";
import {useAttributeHelper} from "src/composables/useAttributeHelper";
import {useQuasar} from "quasar";
import {deepClone} from "src/helpers/common";

const loading = ref(false)
const productAttributeFormRef = ref(null)
const showProductAttributeFormDialog = ref(false)
const filter = ref(false)
const keyword = ref("")
const headerTitle = ref('Add Attribute')
const attributeRequest = useAttributeRequest()
const attributeHelper = useAttributeHelper()
const attributeRows = ref([])
const attributeColumns = ref([])
const $q = useQuasar()

const form = ref({
  id: null,
  name: '',
  options: [],
})

const pagination = ref({
  sort_by: "created_at",
  descending: false,
  page: 1,
  rows_per_page: 10,
  per_page: 10,
  rowsNumber: 1,
});

const closeForm = () => {
  resetForm()
}

const editAttribute = (attr) => {
  showProductAttributeFormDialog.value = true;
  let data = deepClone(attr)
  form.value.id = data.id
  form.value.name = data.name
  form.value.options = data.attribute_options

}
const getAttributeTableColumns = () => {
  attributeColumns.value = attributeHelper.getColumns();
};

const getAttributes = async (props) => {
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  // query.filters = filters;

  pagination.value = props.pagination;

  const { data, meta } = await attributeRequest.getAttributes(query)
  attributeRows.value = data;

  pagination.value.per_page = meta.total;
  pagination.value.rowsNumber = meta.total;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
  pagination.value.page = meta.current_page;
  pagination.value.rows_per_page =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);
};

const createAttribute = () => {
  if(form.value.options.length === 0)
    addOption();
  showProductAttributeFormDialog.value = true;
}

const resetForm = () => {
  const form = ref({
    id: null,
    name: '',
    options: [],
  })
  form.value.name = "";
  form.value.options = [];
}

const addOption = () => {
  form.value.options.push({
    attribute_id: null,
    value: '',
  })
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
    const { data } = await attributeRequest.addAttribute(form.value);
    $q.notify({
      type: "positive",
      message: "Attribute created successfully",
    });
    resetForm()
    showProductAttributeFormDialog.value = false;

  } catch (error) {
    console.log(error);
  }
}

onMounted(() => {
  getAttributes({
    pagination: pagination.value,
  })
  getAttributeTableColumns()
})
</script>

<style lang="scss">
.products-attribute-table,
.fullscreen {
  .q-table__top {
    gap: 10px !important;
    margin: 0 0 20px !important;
}
  .q-table__top label {
    margin: 0 !important
}


}

</style>
