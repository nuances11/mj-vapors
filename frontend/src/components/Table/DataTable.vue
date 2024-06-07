<template>
  <div>
    <TableSkeleton v-if="props.loading"></TableSkeleton>

    <q-table
      v-else
      flat
      v-bind="$attrs"
      dense
      :class="withActions ? 'my-sticky-last-column-table' : ''"
    >
      <template v-slot:loading>
        <q-inner-loading showing color="primary" />
      </template>

      <template v-slot:body-cell-clock_in_remarks="props">
        <q-td :props="props">
          <span :class="props.row.clock_in_remarks === 'Early' ? 'text-green' : 'text-red'">{{ props.row.clock_in_remarks }}</span>
        </q-td>
      </template>

      <template v-slot:body-cell-clock_out_remarks="props">
        <q-td :props="props">
          <span :class="props.row.clock_out_remarks === 'Early' ? 'text-green' : 'text-red'">{{ props.row.clock_out_remarks }}</span>
        </q-td>
      </template>

      <template v-slot:body-cell-price="props">
        <q-td :props="props">
         {{ commonHelper.numberFormat(props.row.price)}}
        </q-td>
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
        <q-td auto-width :props="props">
<!--          <div v-if="isUpdatePassword">-->
<!--            <q-btn-->
<!--              class="q-mr-xs"-->
<!--              size="sm"-->
<!--              color="warning"-->
<!--              icon="lock"-->
<!--              :loading="loading"-->
<!--              :class="$q.screen.lt.md ? 'full-width q-mb-xs' : ''"-->
<!--              @click="updatePassword(props)"-->
<!--            >-->
<!--              <q-tooltip>-->
<!--                Update Password-->
<!--              </q-tooltip>-->
<!--            </q-btn>-->
<!--          </div>-->
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
              v-if="props.row.status === 'voided' && !isVendor"
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
              :loading="loading"
              @click="editItem(props)"
            />
            <q-btn
              class="q-mr-xs"
              size="sm"
              color="negative"
              icon="delete"
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
import {capitalize, computed} from "vue";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useUserStore} from "stores/user-store";
import {useQuasar} from "quasar";

const emit = defineEmits([
  'deleteItem',
  'editItem',
  'showTransaction',
  'voidTransaction',
  'cancelTransaction'
])

const userStore = useUserStore()

const $q = useQuasar()
const commonHelper = useCommonHelper()
const isVendor = computed(() => userStore.user.user_type === 'vendor')

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  isTransactionHistory: {
    type: Boolean,
    default: false,
  },
  isTransactionReport: {
    type: Boolean,
    default: false,
  },
  withActions: {
    type: Boolean,
    default: true,
  },
  isUpdatePassword: {
    type: Boolean,
    default: false
  }
});

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
.my-sticky-last-column-table {
  td {
    &:last-child {
      position: sticky;
      right: 0;
      z-index: 1;
      background-color: white
    }
  }
  th {
    &:last-child {
      position: sticky;
      right: 0;
      z-index: 1;
    }
  }
}

</style>
