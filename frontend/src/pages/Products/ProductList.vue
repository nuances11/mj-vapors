<template>
  <div class="card-border-shadow">
    <div class="q-px-md q-py-sm text-white bg-grey-9">
      <div class="flex items-center justify-between">
        <span class="text-weight-regular text-h6">Product List</span>

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
            label="Add Product"
            text-color="white"
            color="secondary"
            class="q-ml-md"
            :loading="loading"
            @click="addProduct"
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
      @request="getProducts"
      v-model:pagination="pagination"
      @delete-item="deleteProduct"
      @edit-item="editProduct"
      no-data-label="I didn't find anything for you"
      no-results-label="The filter didn't uncover any results"
      wrap-cells
    />

    <q-dialog class="alertDialog" persistent v-model="productFormDialog">
      <q-card style="width: 900px; max-width: 80vw;">
        <q-form
          ref="productFormRef"
          class="q-gutter-md"
          @submit="submitForm"
        >
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">{{ formTitle }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-pa-md">
            <q-input
              class="q-mb-md"
              filled
              v-model="form.name"
              label="Name"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Please type something']"
            />
            <q-input
              class="q-mb-md"
              filled
              v-model="form.price"
              label="Price"
              lazy-rules
              step="any"
              type="number"
              :rules="[ val => val && val > 0 || 'Price must be greater than 0']"
            >
              <template v-slot:prepend>
                <i class="fa-solid fa-peso-sign"></i>
              </template>
            </q-input>

            <q-input
              class="q-mb-md"
              v-model="form.description"
              filled
              autogrow
              label="Description"
            />

              <div class="text-h6">Attribute Options</div>
              <q-separator />
            {{filteredAttributeOptions}}

              <q-markup-table flat class="q-mt-md">
                <thead>
                <tr>
                  <th width="30%">Attribute</th>
                  <th width="30%">Options</th>
                  <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="(option, index) in form.attribute_options" :key="`attr-${index}`">
                  <td>
                    <AttributeSelection
                      :key="attributeComponentKey"
                      :index="index"
                      :current-field="`attribute_${index}`"
                      :options="filteredAttributeOptions"
                      :loading="attributeOptionsLoading"
                      :initial-value="form.attribute_options[index]?.attribute"
                      @new-input="updateAttributeValue"
                    />
                  </td>
                  <td>
                    <AttributeOptionSelection
                      ref="attributeOptionSelectionRef"
                      :key="attributeOptionsComponentKey"
                      :index="index"
                      :current-field="`attribute_option_${index}`"
                      :options="attributeSelectionOptions[index]"
                      :loading="attributeSelectionOptionsLoading[index]"
                      :initial-value="form.attribute_options[index]?.attribute_option"
                      @new-input="updateAttributeValue"
                    />
                  </td>
                  <td class="text-center">

                    <q-btn
                      v-if="index === (form.attribute_options.length - 1) && filteredAttributeOptions.length > 0"
                      @click="addAttributeOption"
                      class="q-mr-xs q-pa-xs"
                      size="xs"
                      color="green-6"
                      icon="add"
                      label="Add Field"
                      dense
                    >
                      <q-tooltip>Add option field</q-tooltip>
                    </q-btn>
                    <template v-if="form.attribute_options[index]?.id">
                      <q-btn
                        class="q-mr-xs q-pa-xs"
                        size="xs"
                        color="red-6"
                        icon="delete"
                        label="Delete existing"
                        flat
                        dense
                      >
                        <q-tooltip>Delete existing option</q-tooltip>
                      </q-btn>
                    </template>

                    <template v-else>
                      <q-btn
                        v-if="index !== 0 || form.attribute_options.length > 1"
                        @click="removeAttributeOption(index)"
                        class="q-mr-xs q-pa-xs"
                        size="xs"
                        color="red-6"
                        icon="delete"
                        label="Delete Field"
                        dense
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
          <q-btn flat label="Cancel" color="primary" @click="closeProductFormDialog" />
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
import {useProductHelper} from "src/composables/useProductHelper";
import {useProductRequest} from "src/composables/useProductRequest";
import {useAttributeRequest} from "src/composables/useAttributeRequest";
import {deepClone} from "src/helpers/common";
import AttributeSelection from "components/product_attributes/AttributeSelection.vue";
import AttributeOptionSelection from "components/product_attributes/AttributeOptionSelection.vue";

const attributeRequest = useAttributeRequest()
const productHelper = useProductHelper()
const productRequest = useProductRequest()
const $q = useQuasar()
const keyword = ref("");
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
const formTitle = ref('Add Product')
const pagination = ref({
  sortBy: "created_at",
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
});
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
const attributeOptions = ref([])
const attributeSelectionOptions = ref([])
const attributeOptionsLoading = ref(false)
const attributeSelectionOptionsLoading = ref([])

const form = ref({
  id: null,
  price: 0,
  name: '',
  description: '',
  attribute_options: [],
})
const loading = ref(false);
const productFormDialog = ref(false);
const productFormRef = ref(null);
const isAddMode = ref(false)
const attributeComponentKey = ref(0);
const attributeOptionsComponentKey = ref(0);
const attributeOptionSelectionRef = ref(null);

const updateAttributeValue = async (data) => {

  if (data.type === 'attribute') {
    form.value.attribute_options[data.index].attribute = data.value;

    form.value.attribute_options[data.index].attribute_option = null;
    attributeOptionsComponentKey.value++
    // attributeOptionSelectionRef.value[data.index].reset()
    let option = await getAttributeSelectionOptions(data.value, data.index);
    attributeSelectionOptions.value.splice(data.index, 0, option)
  } else if (data.type === 'attribute_option') {
    form.value.attribute_options[data.index].attribute_option = data.value;
  }
}

const fetchProductFormData = async (props) => {

  let data = deepClone(props)
  form.value.id = data.id
  form.value.price = data.price
  form.value.name = data.product.name
  form.value.description = data.product.description
  form.value.attribute_options = data.attributes_options

  return form.value
}

const editProduct = async (props) => {

  await loadAttributes().then(async (response) => {
    form.value = await fetchProductFormData(props)
  })

  let attributeIds = [];
  if (form.value.attribute_options.length > 0) {
    form.value.attribute_options.forEach((attr, index) => {
      attributeIds.push(parseInt(attr['attribute']?.id))
    })
  }

  attributeOptions.value.filter(function (attr) {
    return !attributeIds.includes(attr.id)
  });

  formTitle.value = 'Update Product'
  isAddMode.value = false;
  productFormDialog.value = true;
}

const submitForm = async () => {
  console.log(isAddMode.value)
  if (isAddMode.value) await saveProduct()
  else await updateProduct()
}

const updateProduct = async () => {

}

const checkAttributeEntry = async () => {
  if (form.value.attribute_options.length > 0) {
    const uniqueValues = new Set(form.value.attribute_options.map(v => v.attribute.id));
    if (uniqueValues.size < form.value.attribute_options.length) {
      $q.notify({
        type: "negative",
        icon: 'report_problem',
        message: 'Duplicate <strong>Attribute</strong> field. Re-check entries and submit again.',
        html: true,
      });
      return false
    } else {
      return true
    }
  }

  return false
}

const saveProduct = async () => {
  const result = await productFormRef.value.validate();
  console.log(result)
  if (!!!result) {
    return;
  }
  let validateAttributeEntry = await checkAttributeEntry()
  if (!validateAttributeEntry) return

  loading.value = true;
  try {
    await productRequest.addProduct(form.value)
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
          refreshForm()
          productFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    console.log(error);
  }
}

const refreshList = async () => {
  await getProducts({
    pagination: pagination.value,
  })
}

const removeAttributeOption = (index) => {
  attributeSelectionOptions.value.splice(index, 1)
  form.value.attribute_options.splice(index, 1);
  // delete form.value.attribute_options[index]
  console.log(form.value)
  console.log(attributeSelectionOptions.value)
  attributeComponentKey.value++
  attributeOptionsComponentKey.value++
  // console.log('removeAttributeOption option', attributeSelectionOptions.value)
  // console.log('removeAttributeOption form', form.value)
}

const closeProductFormDialog = () => {
  refreshForm();
  productFormDialog.value = false;
}

const refreshForm = () => {
  form.value.id = null;
  form.value.name = "";
  form.value.price = 0;
  form.value.description = "";
  form.value.attribute_options = [];
}

const clearAttributeSelectionOptions = () => {
  attributeSelectionOptionsLoading.value = true;
  attributeSelectionOptions.value = []
  attributeSelectionOptionsLoading.value = false;
}

const getAttributeSelectionOptions = async (value, index) => {

  if (!value) return
  attributeSelectionOptionsLoading.value[index] = true;
  let query = {};
  query.display_all = true
  query.for_options = true
  query.attribute = value
  const { data } = await attributeRequest.getAttributeOptions(query);
  if (data) {
    attributeSelectionOptionsLoading.value[index] = false;
    return data
  }
  attributeSelectionOptionsLoading.value[index] = false;


}
const loadAttributes = async () => {
  attributeOptionsLoading.value = true;
  let query = {};
  query.display_all = true
  query.for_options = true
  const { data } = await attributeRequest.getAttributes(query);
  attributeOptions.value = data
  attributeOptionsLoading.value = false;
}

const addProduct = () => {
  isAddMode.value = true
  loadAttributes()
  if(form.value.attribute_options.length === 0)
    addAttributeOption();
  productFormDialog.value = true;
}

const addAttributeOption = () => {
  form.value.attribute_options.push({
    // attribute_id: null,
    attribute: null,
    // attribute_option_id: null,
    attribute_option: null,
  })
}

const getColumns = () => {
  columns.value = productHelper.getColumns();
}

const getProducts = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await productRequest.getProducts(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

watch(keyword, () => {
  getProducts({
    pagination: pagination.value,
  });
});

const filteredAttributeOptions = computed(() => {
  let attributeIds = [];

  if (form.value.attribute_options.length > 0) {
    form.value.attribute_options.forEach((attr, index) => {
      attributeIds.push(parseInt(attr['attribute']?.id))
    })
  }

  return attributeOptions.value.filter(function (attr) {
    return !attributeIds.includes(attr.id);
  });

})


watch(
  () => filters,
  () => {
    getProducts({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

onMounted(() => {
  getColumns()
  getProducts({
    pagination: pagination.value,
  });
})

</script>

<style lang="scss">

</style>
