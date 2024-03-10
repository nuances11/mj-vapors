<template>
  <div class="q-px-md q-py-sm text-white bg-grey-9">
    <div class="flex items-center justify-between">
      <span class="text-weight-regular text-h6">Product Listing</span>

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
          label="Add Listing"
          text-color="white"
          color="secondary"
          class="q-ml-md"
          :loading="loading"
          @click="addListing"
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
    @request="getListings"
    v-model:pagination="pagination"
    @delete-item="deleteListing"
    @edit-item="editListing"
    no-data-label="I didn't find anything for you"
    no-results-label="The filter didn't uncover any results"
    wrap-cells
  />

  <q-dialog class="alertDialog" persistent v-model="productListingFormDialog">
    <q-card style="width: 900px; max-width: 80vw;">
      <q-form
        ref="productListingFormRef"
        class="q-gutter-md"
        @submit="submitProductListingForm"
      >
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">{{ listingFormTitle }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-pa-md">

            <q-select
              class="q-mb-md"
              dense
              filled
              v-model="form.product_id"
              :options="productOptions"
              map-options
              option-label="name"
              option-value="id"
              label="Product *"
              hint="Select Product"
              emit-value
              :rules="[(v) => !!v || 'Please select something']"
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

            <div class="text-h6">Attribute Options</div>
            <q-separator />

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
          <q-btn flat label="Cancel" color="primary" @click="closeProductListingFormDialog" />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>

</template>

<script setup>

import {capitalize, computed, onMounted, reactive, ref, watch} from "vue";
import AttributeOptionSelection from "components/product_attributes/AttributeOptionSelection.vue";
import DataTable from "components/Table/DataTable.vue";
import AttributeSelection from "components/product_attributes/AttributeSelection.vue";
import {useListingHelper} from "src/composables/useListingHelper";
import {useQuasar} from "quasar";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {deepClone} from "src/helpers/common";
import {useAttributeRequest} from "src/composables/useAttributeRequest";
import {useListingRequest} from "src/composables/useListingRequest";
import {useProductRequest} from "src/composables/useProductRequest";

const productRequest = useProductRequest()
const attributeRequest = useAttributeRequest()
const commonHelper = useCommonHelper()
const listingHelper = useListingHelper()
const listingRequest = useListingRequest()
const $q = useQuasar()
const keyword = ref("");
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
const listingFormTitle = ref('Add Listing')
const pagination = ref(commonHelper.defaultPagination());
const form = ref({
  id: null,
  price: 0,
  product_id: null,
  attribute_options: [],
})
const loading = ref(false);
const productListingFormDialog = ref(false);
const productListingFormRef = ref(null);
const isAddMode = ref(false)
const attributeComponentKey = ref(0);
const attributeOptionsComponentKey = ref(0);
const attributeOptionSelectionRef = ref(null);
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
const productOptions = ref([])
const hasFilters = computed(() => {
  return (
    filters.status !== null
  );
});

const deleteListing = async (props) => {
  loading.value = true
  $q.dialog({
    title: 'Delete Record',
    message: `Are you sure you want to delete <strong>${props.code}</strong>?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await listingRequest.deleteListing(props.id)
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

const updateAttributeValue = async (data) => {

  if (data.type === 'attribute') {
    form.value.attribute_options[data.index].attribute = data.value;

    form.value.attribute_options[data.index].attribute_option = null;
    attributeOptionsComponentKey.value++

    let option = await getAttributeSelectionOptions(data.value, data.index);
    attributeSelectionOptions.value.splice(data.index, 0, option)
  } else if (data.type === 'attribute_option') {
    form.value.attribute_options[data.index].attribute_option = data.value;
  }
}

const fetchProductListingFormData = async (props) => {

  let data = deepClone(props)
  form.value.id = data.id
  form.value.price = data.price
  form.value.product_id = data.product.name
  form.value.attribute_options = data.attributes_options

  return form.value
}

const editListing = async (props) => {

  await loadAttributes().then(async (response) => {
    form.value = await fetchProductListingFormData(props)
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

  listingFormTitle.value = 'Update Listing'
  isAddMode.value = false;
  productListingFormDialog.value = true;
}

const submitProductListingForm = async () => {
  if (isAddMode.value) await saveProductListing()
  else await updateProductListing()
}

const updateProductListing = async () => {
  const result = await productListingFormRef.value.validate();
  console.log(result)
  if (!!!result) {
    return;
  }

  let validateAttributeEntry = await checkAttributeEntry()
  if (!validateAttributeEntry) return

  loading.value = true;
  try {
    await listingRequest.updateListing(form.value.id, form.value)
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
          refreshListingForm()
          productListingFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    console.log(error?.response?.statusText)
    $q.notify({
      type: "negative",
      icon: 'report_problem',
      message: error?.response?.statusText,
    });
  }

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

const saveProductListing = async () => {
  const result = await productListingFormRef.value.validate();
  if (!!!result) {
    return;
  }
  let validateAttributeEntry = await checkAttributeEntry()
  if (!validateAttributeEntry) return

  loading.value = true;
  try {
    await listingRequest.addListing(form.value)
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
          refreshListingForm()
          productListingFormDialog.value = false;
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

const refreshList = async () => {
  await getListings({
    pagination: pagination.value,
  })
}

const removeAttributeOption = (index) => {
  attributeSelectionOptions.value.splice(index, 1)
  form.value.attribute_options.splice(index, 1);

  attributeComponentKey.value++
  attributeOptionsComponentKey.value++
}

const closeProductListingFormDialog = () => {
  refreshListingForm();
  productListingFormDialog.value = false;
}

const refreshListingForm = () => {
  form.value.id = null;
  form.value.product_id = null;
  form.value.price = 0;
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

const addListing = async () => {
  isAddMode.value = true
  await loadAttributes()
  await loadProductOptions()
  if(form.value.attribute_options.length === 0)
    addAttributeOption();
  productListingFormDialog.value = true;
}

const loadProductOptions = async () => {
  let query = {}
  query.display_all = true;
  const { data } = await productRequest.getProducts(query);
  productOptions.value = data;
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
  columns.value = listingHelper.getColumns();
}

const getListings = async (props) => {
  loading.value = true;
  let query = props.pagination ? props.pagination : pagination.value;
  query.keyword = keyword.value;
  query.filters = JSON.stringify(filters);

  pagination.value = props.pagination;

  const { data, meta } = await listingRequest.getListings(query);

  rows.value = data;

  pagination.value.rowsNumber = meta.total;
  pagination.value.page = meta.current_page;
  pagination.value.rowsPerPage =
    parseInt(meta.per_page) === meta.total ? 0 : parseInt(meta.per_page);

  loading.value = false;
};

watch(keyword, () => {
  getListings({
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
    getListings({
      pagination: pagination.value,
    });
  },
  { deep: true }
);

onMounted(() => {
  getColumns()
  getListings({
    pagination: pagination.value,
  });
})

</script>

<style scoped lang="scss">

</style>
