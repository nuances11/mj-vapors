<template>
  <div>
    <div class="row" v-if="!loading">
      <div class="company-wrp col-12 col-md-6">
        <div class="column col-12">
          <div class="logo-company-uploader logo-wrp relative-position">
            <input
              type="file"
              class="input-file"
              @change="
                  (evt) => {
                    onFilesChange(evt);
                  }
                "
              accept="image/png"
            />
            <q-avatar rounded class="logo-inner">
              <img
                alt="Theme logo"
                :src="`${endpointRoute}/img/${profile.logo}?r=${rand}`"
              >
            </q-avatar>
            <q-badge color="blue-5" class="badge-logo" floating>
              <q-icon
                name="backup"
                round
                color="white"
                size="sm"
                class="q-ma-xs"
              />
            </q-badge>
            <q-tooltip content-style="font-size: 12px" :offset="[10, 10]"
            >Upload Photo</q-tooltip
            >
          </div>
          <div class="q-pa-sm upload-txt text-center text-white">
            Click the image to upload company logo <br />
            <small class="text-weight-medium">
              *Note: Logo suggested size is 400x400 pixel in .jpg, .png and
              .gif format
            </small>
          </div>
        </div>
        <div
          class="column col-12 flex justify-center items-center company-info-text bg-grey-9"
        >
          <h2 class="company-title">{{ profile.company_name }}</h2>
        </div>
      </div>
      <div
        :class="$q.screen.lt.sm ? 'q-py-lg' : 'q-pa-xl'"
        class="col-12 col-md-6 row justify-center items-center"
      >
        <q-form
          class="q-gutter-sm form-company"
          ref="companySettingsFormRef"
          enctype="multipart/form-data"
        >
          <q-input
            filled
            label="Company Name *"
            class="q-py-lg"
            v-model="profile.company_name"
            lazy-rules
            dense
            autocomplete="off"
            required
            :rules="[(val) => (val && val.length > 0) || 'Please type name']"
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
  </div>

</template>

<script setup>

import {inject, onMounted, ref} from "vue";
import Cookies from "js-cookie";
import {useQuasar} from "quasar";
import axios from "axios";
import {useSettingRequest} from "src/composables/useSettingRequest";
import {deepClone} from "src/helpers/common";
import {useSettingStore} from "stores/setting-store";

const $q = useQuasar()
const settingRequest = useSettingRequest()

const profile = ref({
  id: null,
  company_name: null,
  logo: null,
})
const loading = ref(false)
const isUpdating = ref(false);
const endpointRoute = ref(process.env.API_ROUTE)
const companySettingsFormRef = ref(null)
const rand = ref(null)
const settingStore = useSettingStore()
const bus = inject("bus");

const onSubmitChanges = async () => {
  const result = await companySettingsFormRef.value.validate();
  if (!!!result) {
    return;
  }
  loading.value = true
  let form = deepClone(profile.value);
  delete form.logo;

  isUpdating.value = true;
  settingRequest
    .updateCompanySetting(profile.value.id, form)
    .then((response) => {
      $q.notify({
        type: "positive",
        message: "Company Setting updated successfully.",
        timeout: 2500,
        position: "top",
      });
      profile.value = response.data;
      settingStore.setCompanySetting(response.data)
      bus.emit('company-setting-updated')
    })
    .catch((error) => {
      console.log(error);
    })
    .finally(() => {
      isUpdating.value = false;
      loading.value = false
    });
}

const onFilesChange = (e) => {
  const files = e.target.files || e.dataTransfer.files;
  let file = null;

  let filepath = null;

  if (!files.length) {
    file = null;
    filepath = "";
    return;
  }
  file = files[0];
  filepath = file.name;

  const formData = new FormData();
  formData.append("logo", file);
  formData.append("_method", "PUT");
  const token = Cookies.get("token");
  const config = {
    headers: {
      "Content-type": "multipart/form-data",
      Authorization: `Bearer ${token}`,
    },
  };

  axios
    .post(
      `${endpointRoute.value}/api/setting/company/upload-logo/1`,
      formData,
      config
    )
    .then((response) => {
      const ONE_SECOND = 1000;
      profile.value = response.data.data;
      settingStore.setCompanySetting(response.data.data)
      rand.value = new Date().getTime() / ONE_SECOND;
      bus.emit('company-setting-updated')
    })
    .catch((error) => {
      let message = error.message;
      if (error.response.data && error.response.data.errors)
        message = error.response.data.errors;
      else if (error.response.data && error.response.data.error)
        message = error.response.data.error;

      $q.notify({
        type: "negative",
        message: message,
        timeout: 2500,
        position: "top",
      });
    });
}

const geCompanySettings = async () => {
  loading.value = true;
  const { data } = await settingRequest.getCompanySetting(1);
  profile.value = data;
  if (!profile.value.logo)
    profile.value.logo = "default-logo.png";

  const ONE_SECOND = 1000;
  rand.value = new Date().getTime() / ONE_SECOND;
  loading.value = false;
}

onMounted(() => {
  geCompanySettings();
})

</script>

<style lang="scss">
.page-title {
  font-size: 1.2rem;
  line-height: 1.2rem;
  padding: 0.5rem 0;
  font-weight: 500;
  margin: 1rem 1rem 1.5rem 1rem;
}

.form-company {
  width: 100%;
  label {
    width: 100%;
    margin-bottom: 20px;
  }
}

.company-title {
  font-size: 40px;
  color: #fff;
  font-weight: 700;
  margin: 0;
  padding: 0;
  text-transform: uppercase;
  text-align: center;
}

.company-wrp {
  background: #24292e;
  position: relative;
  justify-content: space-between;
  display: flex;
  flex-direction: column;
}

.logo-company-uploader {
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  width: 250px;
  height: auto;
  margin: 70px auto 25px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  img {
    width: 100%;
    height: auto;
    border: 4px solid rgba(225, 255, 255, 0.7);
    padding: 10px;
  }
}

.logo-inner {
  width: 100%;
  height: 100%;
  img {
    background: $blue-grey-5;
  }
}

.badge-logo {
  top: -11px;
  right: -12px;
  border-radius: 999px;
  padding: 7px;
}

.company-info-text {
  //background: $blue-grey-6;
  margin-top: 20px;
  padding: 20px;
  // position: absolute;
  width: 100%;
  // bottom: 0;
  span {
    display: block;
    font-size: 18px;
    color: #fff;
    opacity: 0.7;
  }
}

.input-file {
  opacity: 0; /* invisible but it's there! */
  width: 100%;
  height: 250px;
  position: absolute;
  cursor: pointer;
  z-index: 5;
}

.logo-company-uploader:hover {
  border-color: #409eff;
}
.logo-company-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 60px;
  height: 50px;
  line-height: 50px;
  text-align: center;
}

@media only screen and (max-width: 600px) {
  .logo-company-uploader {
    width: 150px;
  }
  .company-title {
    font-size: 23px;
    line-height: inherit;
  }
  .company-info-text {
    position: relative;
    span {
      font-size: 13px;
      opacity: 1;
    }
  }
  .upload-txt {
    opacity: 1;
    padding: 0 40px;
  }
  .form-company label {
    width: 100%;
  }
}

</style>
