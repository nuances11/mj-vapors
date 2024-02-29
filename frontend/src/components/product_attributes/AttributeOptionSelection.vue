<template>
  <q-select
    class="q-ma-xs"
    dense
    v-model="attributeOption"
    :options="options"
    :loading="loading"
    use-input
    option-label="value"
    label="Options"
    lazy-rules
    @update:model-value="emit('new-input', {
      name: currentField,
      value: attributeOption,
      index: index,
      type: 'attribute_option'
    })"
    :rules="[(v) => !!v || 'Please select something']"
  />
</template>

<script setup>

import {onMounted, ref} from "vue";

const attributeOption = ref('')

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
      attributeOption.value = props.initialValue;
      emit("new-input", {
        name: props.currentField,
        value: attributeOption.value,
      });
    }
})


</script>

<style scoped lang="scss">

</style>
