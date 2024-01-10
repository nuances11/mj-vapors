<script setup>

import TableSkeleton from "components/skeleton/TableSkeleton.vue";
import {ref} from "vue";

const loading = ref(false)
const productAttributeFormRef = ref(null)
const showProductAttributeFormDialog = ref(false)
const filter = ref('')
const headerTitle = ref('Add Product')

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
  name: '',
  options: [],
})

const createAttribute = () => {
  if(form.value.options.length === 0)
    addOption();
  showProductAttributeFormDialog.value = true;
}

const addOption = () => {
  form.value.options.push({
    value: '',
  })
}

const removeOption = (index) => {
  form.value.options.splice(index, 1);
}
</script>

<template>
<div class="q-pa-md products-attribute-table">
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
                  <svg
                    v-if="index === (form.options.length - 1)"
                    @click="addOption"
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
                    v-if="index !== 0 || form.options.length > 1"
                    @click="removeOption(index)"
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

<!--      <q-separator />-->

      <q-card-actions align="right" class="dialog_bottom">
        <q-btn flat label="Submit" color="primary" v-close-popup />
        <q-btn flat label="Cancel" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>

</div>
</template>

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
