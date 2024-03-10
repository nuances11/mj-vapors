<template>
  <div class="q-px-md q-py-sm text-white bg-grey-9">
    <div class="flex items-center justify-between">
      <span class="text-weight-regular text-h6">Product</span>

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
        @submit="submitProductForm"
      >
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">{{ productFormTitle }}</div>
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
              v-model="form.description"
              filled
              autogrow
              label="Description"
            />

          </div>
        </q-card-section>

        <q-card-actions align="right" class="dialog_bottom">
          <q-btn flat label="Submit" color="primary" type="submit" />
          <q-btn flat label="Cancel" color="primary" @click="closeProductFormDialog" />
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
import {useProductHelper} from "src/composables/useProductHelper";

const commonHelper = useCommonHelper()
const productHelper = useProductHelper()
const productRequest = useProductRequest()
const $q = useQuasar()
const keyword = ref("");
const filter = ref(false);
const columns = ref([]);
const rows = ref([]);
const productFormTitle = ref('Add Product')
const pagination = ref(commonHelper.defaultPagination());
const form = ref({
  id: null,
  name: '',
  description: '',
})
const loading = ref(false);
const productFormDialog = ref(false);
const productFormRef = ref(null);
const isAddMode = ref(false)
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
const hasFilters = computed(() => {
  return (
    filters.status !== null
  );
});

const deleteProduct = async (props) => {
  loading.value = true
  $q.dialog({
    title: 'Delete Record',
    message: `Are you sure you want to delete <strong>${props.name}</strong>?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await productRequest.deleteProduct(props.id)
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

const fetchProductFormData = async (props) => {

  let data = deepClone(props)
  form.value.id = data.id
  form.value.name = data.name
  form.value.description = data.description

  return form.value
}

const editProduct = async (props) => {

  form.value = await fetchProductFormData(props)

  productFormTitle.value = 'Update Product'
  isAddMode.value = false;
  productFormDialog.value = true;
}

const submitProductForm = async () => {
  if (isAddMode.value) await saveProduct()
  else await updateProduct()
}

const updateProduct = async () => {
  const result = await productFormRef.value.validate();
  if (!!!result) {
    return;
  }

  loading.value = true;
  try {
    await productRequest.updateProduct(form.value.id, form.value)
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
          refreshProductForm()
          productFormDialog.value = false;
          refreshList()
        }
        loading.value = true;
      });

  } catch (error) {
    $q.notify({
      type: "negative",
      icon: 'report_problem',
      message: error?.response?.data?.message,
    });
  }

}

const saveProduct = async () => {
  const result = await productFormRef.value.validate();
  if (!!!result) {
    return;
  }

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
          refreshProductForm()
          productFormDialog.value = false;
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
  await getProducts({
    pagination: pagination.value,
  })
}

const closeProductFormDialog = () => {
  refreshProductForm();
  productFormDialog.value = false;
}

const refreshProductForm = () => {
  form.value.id = null;
  form.value.name = '';
  form.value.description = '';
}


const addProduct = async () => {
  isAddMode.value = true
  productFormDialog.value = true;
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

<style scoped lang="scss">

</style>
