<template>
  <div class="q-pa-md" style="max-width: 400px">

    <q-form
      ref="profileFormRef"
      @submit="onSubmit"
      class="q-gutter-md"
    >
      <h6 class="q-mb-xs">User Profile</h6>
      <q-input
        filled
        dense
        square
        v-model="form.first_name"
        label="First Name"
        lazy-rules
        :rules="[ val => val && val.length > 0 || 'Please type something']"
      />

      <q-input
        filled
        dense
        square
        v-model="form.last_name"
        label="Last Name"
        lazy-rules
        :rules="[ val => val && val.length > 0 || 'Please type something']"
      />

      <q-input
        disable
        filled
        dense
        square
        v-model="form.email"
        label="Email"
        lazy-rules
        :rules="[ val => val && val.length > 0 || 'Please type something']"
      />

      <q-input
        disable
        filled
        dense
        square
        v-model="form.user_name"
        label="Username"
        lazy-rules
        :rules="[ val => val && val.length > 0 || 'Please type something']"
      />

      <div align="right" >
        <q-btn flat label="Update" type="submit" color="primary"/>
      </div>
    </q-form>

    <q-form ref="changePasswordFormRef" @submit="changePassword">
      <q-card-section>
        <div class="text-h6">Change Password</div>
      </q-card-section>
      <q-card-section class="q-pt-none q-pb-none">
        <q-input
          dense
          class="q-mb-md"
          filled
          v-model="changePasswordForm.old_password"
          label="Old Password *"
          hint="Input Password"
          lazy-rules
          hide-bottom-space
          :rules="[ val => val && val.length > 0 || 'Please type something', passwordLength]"
        />
        <q-input
          dense
          class="q-mb-md"
          filled
          v-model="changePasswordForm.password"
          label="New Password *"
          hint="Input Password"
          lazy-rules
          hide-bottom-space
          :rules="[ val => val && val.length > 0 || 'Please type something', passwordLength]"
        >
          <template #append>
            <q-icon
              class="cursor-pointer clickable-icons"
              name="content_copy"
              @click="copyToClipboard(changePasswordForm.password)"
            >
              <q-tooltip>Copy to clipboard</q-tooltip>
            </q-icon>
          </template>
        </q-input>
      </q-card-section>
      <q-card-actions align="right" class="q-pt-none">
        <q-btn flat label="Change Password" type="submit" color="primary" />
      </q-card-actions>
    </q-form>


  </div>
</template>

<script setup>
import { useQuasar } from 'quasar'
import {onMounted, ref} from 'vue'
import {useUserRequest} from "src/composables/useUserRequest";
import {useUserStore} from "stores/user-store";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useValidationHelper} from "src/composables/useValidationHelper";

const validationHelper = useValidationHelper()
const commonHelper = useCommonHelper()
const userRequest = useUserRequest()
const userStore = useUserStore()
const $q = useQuasar()
const profileFormRef = ref(null)
const changePasswordFormRef = ref(null)
const changePasswordForm = ref({
  password: null,
  old_password: null,
  profile_page: true
})

const form = ref({
  id: null,
  first_name: null,
  last_name: null,
  email: null,
  username: null
});

const onSubmit = async () => {
  const result = await profileFormRef.value.validate();
  if (!!!result) {
    return;
  }
  await userRequest.updateUser(form.value.id, form.value)
    .then((response) => {
      if (response.success) {
        $q.notify({
          type: "positive",
          message: response.message,
        });
        userStore.setUser(response.data)
        // window.location.reload()
      } else {
        $q.notify({
          type: "negative",
          message: response.message,
        });
      }
    })
}

const passwordLength = (password) => {
  return validationHelper.passwordLength(password) || "Password must be atleast 6 characters"
}

const changePassword = async () => {
  if (changePasswordForm.value.password && changePasswordForm.value.old_password) {
    await userRequest.updateUserPassword(userStore.user.id, changePasswordForm.value)
      .then((response) => {
        if (response.success) {
          $q.notify({
            type: "positive",
            message: response.message,
          });
          changePasswordForm.value.old_password = null
          changePasswordForm.value.password = null
        } else {
          $q.notify({
            type: "negative",
            message: response.message,
          });
        }
      })
  } else {
    $q.notify({
      type: "negative",
      message: 'Fill in the required fields.',
    });
  }

}

const copyToClipboard = (string) => {
  commonHelper.copyToClipboard(string)
}

const getUser = async () => {
  const { data } = await userRequest.getUser(userStore.user.id, {})
  form.value = data
}

onMounted(() => {
  getUser()
})



</script>


<style scoped lang="scss">

</style>
