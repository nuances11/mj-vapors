<template>
  <div class="row" v-if="!loading">
    <div
      :class="$q.screen.lt.sm ? 'q-py-md' : 'q-pa-xl'"
      class="col-12 col-md-6 row justify-center items-center"
    >
      <q-form
        class="q-gutter-sm form-company"
        ref="userSettingsFormRef"
        enctype="multipart/form-data"
      >
        <q-input
          step="any"
          type="number"
          filled
          label="Base Salary *"
          class="q-py-md text-right"
          v-model="userSetting.base_salary"
          lazy-rules
          dense
          mask="#.##"
          fill-mask="#"
          reverse-fill-mask
          required
          :rules="[(val) => (val && val > 0) || 'Value should be more than 0']"
        />

        <q-input
          step="any"
          type="number"
          filled
          label="Commission Threshold *"
          class="q-py-md text-right"
          v-model="userSetting.commission_threshold"
          lazy-rules
          dense
          mask="#.##"
          fill-mask="#"
          reverse-fill-mask
          required
          :rules="[(val) => (val && val > 0) || 'Value should be more than 0']"
        />

        <q-input
          step="any"
          type="number"
          filled
          label="Commission Rate *"
          class="q-py-md text-right"
          v-model="userSetting.commission_rate"
          lazy-rules
          dense
          mask="#.##"
          fill-mask="#"
          reverse-fill-mask
          required
          :rules="[(val) => (val && val > 0) || 'Value should be more than 0']"
        />

        <q-input
          step="any"
          type="number"
          filled
          label="Additional Salary *"
          class="q-py-md text-right"
          v-model="userSetting.additional_salary"
          lazy-rules
          dense
          mask="#.##"
          fill-mask="#"
          reverse-fill-mask
          required
          :rules="[(val) => (val && val > 0) || 'Value should be more than 0']"
        />
        <div class="q-ma-none">
          <q-btn
            unelevated
            label="Update"
            size="md"
            class="q-py-d primary_btn"
            :class="isUpdating ? 'q-px-lg' : 'q-px-md'"
            :loading="isUpdating"
            @click="onSubmitChanges"
          >
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              Updating
            </template>
          </q-btn>
        </div>
      </q-form>
    </div>
  </div>

</template>

<script setup>
import {useQuasar} from "quasar";
import {onMounted, ref} from "vue";
import {useUserSettingRequest} from "src/composables/useUserSettingRequest";
import {useSettingStore} from "stores/setting-store";

const settingStore = useSettingStore()
const userSettingRequest = useUserSettingRequest()
const $q = useQuasar()
const loading = ref(false)
const isUpdating = ref(false)
const userSetting = ref({
  id: null,
  base_salary: 0,
  commission_threshold: 0,
  commission_rate: 0,
  additional_salary: 0
})
const userSettingsFormRef = ref(null)

const getSettings = async() => {
  loading.value = true;
  const { data } = await userSettingRequest.getUserSetting(1);
  userSetting.value = data;
  loading.value = false;
}

const onSubmitChanges = async () => {
  const result = await userSettingsFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  isUpdating.value = true
  userSettingRequest
    .updateUserSetting(userSetting.value.id, userSetting.value)
    .then((response) => {
      $q.notify({
        type: "positive",
        message: "User Setting updated successfully.",
        timeout: 2500,
        position: "top",
      });
      userSetting.value = response.data;
      settingStore.setCompanySetting(response.data)
    })
    .catch((error) => {
      console.log(error);
    })
    .finally(() => {
      isUpdating.value = false
      loading.value = false
    });
}

onMounted(() => {
  getSettings()
})

</script>

<style lang="scss">

</style>
