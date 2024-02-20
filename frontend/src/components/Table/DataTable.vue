<template>
  <div>
    <TableSkeleton v-if="props.loading"></TableSkeleton>

    <q-table
      v-else
      flat
      v-bind="$attrs"
    >
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
              @click="editItem(props)"
            />
            <q-btn
              v-if="parseInt(currentUserId) !== props.row.id"
              class="q-mr-xs"
              size="sm"
              color="negative"
              icon="delete"
              label="Delete"
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
import Cookies from "js-cookie";

const emit = defineEmits(['deleteItem', 'editItem'])

const currentUserId = Cookies.get("user_id");
const currentUserType = Cookies.get("user_type");
// const currentUserId = cookieData.user_id

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
});

const deleteItem = (props) => {
  emit('deleteItem', props.row)
}

const editItem = (props) => {
  emit('editItem', props.row)
}
</script>

<style lang="scss">

</style>
