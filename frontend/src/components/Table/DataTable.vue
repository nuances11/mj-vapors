<template>
  <div>
    <TableSkeleton v-if="props.loading"></TableSkeleton>

    <q-table
      v-else
      flat
      v-bind="$attrs"
    >
      <template v-slot:loading>
        <q-inner-loading showing color="primary" />
      </template>

      <template v-slot:body-cell-sku="props">
        <q-td :props="props">
          <q-chip
            filled
            color="grey-8"
            text-color="white"
            @click="copyToClipboard(props.row.code)"
            clickable
          >
            <strong>{{ props.row.code }}</strong>
            <q-tooltip>Click to copy</q-tooltip>

          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-product_sku="props">
        <q-td :props="props">
          <q-chip
            filled
            color="grey-8"
            text-color="white"
            @click="copyToClipboard(props.row.product_sku)"
            clickable
          >
            <strong>{{ props.row.product_sku }}</strong>
            <q-tooltip>Click to copy</q-tooltip>

          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-variants="props">
        <q-td :props="props">
          <q-chip square dense filled v-for="(variant, index) in props.row.variants" :key="`attr-${index}`">
            {{ variant.attribute.name }} : {{ variant.attribute_option.value }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-attribute_options="props">
        <q-td :props="props">
        <span v-if="props.row.attribute_options">
          <q-chip dense square v-for="(option, index) in props.row.attribute_options" :key="index">
            {{ option.value }}
          </q-chip>

        </span>
          <span v-else>
              N/A
        </span>
        </q-td>

      </template>

      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="props.value === 'active' ? 'positive' : 'negative'"
            text-color="white"
            dense
            size="md"
            :icon="props.value === 'active' ? 'check' : 'stop'"
          >
            <span class="text-caption">{{ capitalize(props.value) }}</span>
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <div v-if="isTransactionHistory">
            <q-btn
              class="q-mr-xs"
              size="sm"
              color="primary"
              icon="visibility"
              :loading="loading"
              @click="showTransaction(props)"
            >
              <q-tooltip>
                View Transaction
              </q-tooltip>
            </q-btn>
            <q-btn
              v-if="props.row.status === 'success'"
              class="q-mr-xs"
              size="sm"
              color="warning"
              icon="production_quantity_limits"
              :loading="loading"
              @click="voidTransaction(props)"
            >
              <q-tooltip>
                Void Transaction
              </q-tooltip>
            </q-btn>
            <q-btn
              v-if="props.row.status === 'voided'"
              class="q-mr-xs"
              size="sm"
              color="negative"
              icon="delete_forever"
              :loading="loading"
              @click="cancelTransaction(props)"
            >
              <q-tooltip>
                Cancel Transaction
              </q-tooltip>
            </q-btn>
          </div>
          <div v-else>
            <q-btn
              class="q-mr-xs"
              size="sm"
              color="primary"
              icon="edit"
              label="Edit"
              :loading="loading"
              @click="editItem(props)"
            />
            <q-btn
              class="q-mr-xs"
              size="sm"
              color="negative"
              icon="delete"
              label="Delete"
              :loading="loading"
              @click="deleteItem(props)"
            />
          </div>

        </q-td>
      </template>

    </q-table>

  </div>

</template>

<script setup>

import TableSkeleton from "components/skeleton/TableSkeleton.vue";
import {capitalize} from "vue";
import {useCommonHelper} from "src/composables/useCommonHelper";

const emit = defineEmits([
  'deleteItem',
  'editItem',
  'showTransaction',
  'voidTransaction',
  'cancelTransaction'
])

const commonHelper = useCommonHelper()

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  isTransactionHistory: {
    type: Boolean,
    default: false,
  },
});


console.log(props.isTransactionHistory)

const copyToClipboard = (string) => {
  commonHelper.copyToClipboard(string)
}
const deleteItem = (props) => {
  emit('deleteItem', props.row)
}

const editItem = (props) => {
  emit('editItem', props.row)
}

const showTransaction = (props) => {
  emit('showTransaction', props.row)
}

const voidTransaction = (props) => {
  emit('voidTransaction', props.row)
}

const cancelTransaction = (props) => {
  emit('cancelTransaction', props.row)
}
</script>

<style lang="scss">

</style>
