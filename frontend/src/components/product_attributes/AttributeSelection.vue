<template>
  <q-select
    class="q-ma-xs"
    dense
    v-model="attributeValue"
    :options="options"
    :loading="loading"
    emit-value
    use-input
    option-label="name"
    label="Attribute"
    lazy-rules
    @update:model-value="emit('new-input', {
      name: currentField,
      value: attributeValue,
      index: index,
      type: 'attribute'
    })"
    :rules="[(v) => !!v || 'Please select something']"
  />
</template>

<script setup>

import {onMounted, ref} from "vue";

const attributeValue = ref('')

const props = defineProps({
  initialValue: {
    type: [String, Number, Object, Array],
    default: null,
  },
  currentField: {
    type: String,
    default: null,
  },
  options: {
    type: [Object, Array],
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  index: {
    type: Number,
    default: 0,
  }
})

const emit = defineEmits(['new-input'])

onMounted(() => {
  if (typeof props.initialValue !== "undefined")
    if (props.initialValue !== null) {
      attributeValue.value = props.initialValue;
      emit("new-input", {
        name: props.currentField,
        value: attributeValue.value,
      });
    }
})


</script>

<style scoped lang="scss">

</style>
