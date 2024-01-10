<template>
  <div class="q-pa-md products-data-table">
    <TableSkeleton v-if="loading"></TableSkeleton>
    <q-table
      v-else
      flat
      title="Products"
      :rows="rows"
      :columns="columns"
      color="primary"
      row-key="name"
    >
      <template v-slot:top>

        <q-space v-if="!$q.screen.lt.sm" />

        <q-input
          borderless
          dense
          debounce="300"
          class="search-item-cat"
          v-model="filter"
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
          label="Create Product"
          @click="createProduct"
        />

        <q-page-sticky
          v-if="$q.screen.lt.sm"
          position="bottom-right"
          :offset="[18, 45]"
          style="z-index: 999"
          @click="createProduct"
        >
          <q-btn fab icon="add" color="light-blue-7" />
        </q-page-sticky>
      </template>
    </q-table>

    <q-dialog persistent v-model="showProductFormDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section>
          <div class="text-h6">{{ headerTitle }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-pa-md">

            <q-form
              ref="productFormRef"
              class="q-gutter-md"
            >
              <q-input
                filled
                v-model="form.name"
                label="Product Name"
                lazy-rules
                :rules="[ val => val && val.length > 0 || 'Please type something']"
              />

              <q-input
                filled
                type="textarea"
                v-model="form.description"
                label="Descriptin"
                lazy-rules
              />

              <div class="text-h6">Attributes</div>

              <q-separator />

              <q-markup-table flat class="q-mt-md">
                <thead>
                  <tr>
                    <th>Attribute</th>
                    <th>Options</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(attribute, index) in form.attributes" :key="`attr-${index}`">
                    <td>
                      Select Attribute {{ index }}
                    </td>
                    <td>
                      Select Option {{ index }}
                    </td>
                    <td>
                      <svg
                        v-if="index === (form.attributes.length - 1)"
                        @click="addAttribute"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        width="24"
                        height="24"
                        class="ml-2 cursor-pointer"
                      >
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path
                          fill="green"
                          d="M11 11V7h2v4h4v2h-4v4h-2v-4H7v-2h4zm1 11C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477
                                    10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"
                        />
                      </svg>

                      <!--          Remove Svg Icon-->
                      <svg
                        v-if="index !== 0 || form.attributes.length > 1"
                        @click="removeAttribute(index)"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        width="24"
                        height="24"
                        class="ml-2 cursor-pointer"
                      >
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path
                          fill="#EC4899"
                          d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0
                                    16zm0-9.414l2.828-2.829 1.415 1.415L13.414 12l2.829 2.828-1.415 1.415L12 13.414l-2.828 2.829-1.415-1.415L10.586
                                    12 7.757 9.172l1.415-1.415L12 10.586z"
                        />
                      </svg>
                    </td>
                  </tr>
                </tbody>
              </q-markup-table>

            </q-form>

          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Submit" color="primary" v-close-popup />
          <q-btn flat label="Cancel" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>

</template>

<script setup>

import {ref} from "vue";
import TableSkeleton from "components/skeleton/TableSkeleton.vue";

const loading = ref(false)
const showProductFormDialog = ref(false)
const filter = ref('')
const headerTitle = ref('Add Product')
const productFormRef = ref(null);
const columns = [
  { name: 'name', align: 'left', label: 'Name', field: 'name', sortable: true },
  { name: 'description', align: 'left', label: 'Description', field: 'description', sortable: false },
]

const rows = [
  {
    name: 'Item 1',
    description: 'Lorem Ipsum ismet',
  },
  {
    name: 'Item 2',
    description: 'Lorem Ipsum ismet',
  },
]

const form = ref({
  sku: '',
  name: '',
  description: '',
  attributes: []
})

const createProduct = () => {
  if(form.value.attributes.length === 0)
    addAttribute();
  showProductFormDialog.value = true;
}

const addAttribute = () => {
  form.value.attributes.push({
    attribute_id: '',
    attribute_options_id: '',
  })
}

const removeAttribute = (index) => {
  form.value.attributes.splice(index, 1);
}

</script>

<style lang="scss">

.products-data-table,
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
