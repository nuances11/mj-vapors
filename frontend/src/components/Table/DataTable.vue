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
      </template>

      <template v-slot:body-cell-variants="props">
        <q-chip square dense filled v-for="(variant, index) in props.row.variants" :key="`attr-${index}`">
          {{ variant.attribute.name }} : {{ variant.attribute_option.value }}
        </q-chip>
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

        </q-td>
      </template>

    </q-table>

  </div>

</template>

<script setup>

import TableSkeleton from "components/skeleton/TableSkeleton.vue";
import {capitalize} from "vue";
import {useCommonHelper} from "src/composables/useCommonHelper";

const emit = defineEmits(['deleteItem', 'editItem'])

const commonHelper = useCommonHelper()

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
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
</script>

<style lang="scss">

</style>
