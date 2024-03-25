<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container style="margin: 0; width: 100%">
      <div class="login_wrapper">
        <div class="container">
          <div class="row login-box">
            <div class="col-lg-6 col-md-6 q-pa-md form-section">
              <div class="form-inner">
                <a href="/" class="logo">
                  <img
                    src="~assets/logo.jpg"
                    alt="Logo Image"
                    height="130"
                  />
                </a>
                <h3>Enter your details to login your account</h3>
                <q-form ref="loginFormRef" @submit="login">
                  <q-input
                    square
                    outlined
                    filled
                    clearable
                    v-model="form.email"
                    type="email"
                    placeholder="Email"
                    class="login-input "
                    standout
                    :rules="[
                    (val) => !!val || 'Email should not be empty',
                    isEmailValid
                  ]"
                  />
                  <q-input
                    square
                    outlined
                    filled
                    clearable
                    v-model="form.password"
                    placeholder="Password"
                    :type="pwdType"
                    class="login-input"
                    :rules="[
                    (val) =>
                      (val !== '' && val !== null) ||
                      'Password should not be empty',
                      isPasswordLength,
                  ]"
                  />

                  <span class="show-pwd" @click="showPwd"> Show Password </span>

                  <div class="text-grey-8 text-left q-mb-md">
                    <q-checkbox
                      keep-color
                      v-model="rememberMe"
                      label="Remember Me"
                      color="grey-6"
                    />
                  </div>
                  <q-btn
                    type="submit"
                    unelevated
                    color="grey-9"
                    size="lg"
                    class="full-width q-py-xs q-mb-lg"
                    label="Login"
                    :loading="loading"
                  />
                </q-form>

                <div class="clearfix"></div>

              </div>
            </div>
            <div
              class="col-lg-6 col-md-6 flex items-center bg-img"
              v-if="!$q.screen.lt.sm"
            >
              <div class="login_desc">
                  <img src="~assets/logo.jpg" width="300px">
              </div>
            </div>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>

import {useQuasar} from "quasar";
import {onMounted, ref} from "vue";
import {useAuthenticationRequest} from "src/composables/useAuthenticationRequest";
import {useValidationHelper} from "src/composables/useValidationHelper";
import Cookies from "js-cookie";
import {useRoute, useRouter} from "vue-router";
import {useUserStore} from "stores/user-store";
import {useBranchRequest} from "src/composables/useBranchRequest";

let $q = useQuasar()
const branchRequest = useBranchRequest()
const userStore = useUserStore()
let authRequest = useAuthenticationRequest()
let validationHelper = useValidationHelper()
const router = useRouter()
const route = useRoute()

const loginFormRef = ref(null)
const form = ref({
  email: '',
  password: '',
  user_type: null,
  branch_id: null,
})
const loading = ref(false);
const pwdType = ref('password')
const rememberMe = ref(false)
const branchOptions = ref([])
const branchOptionsLoading = ref(false)

const isPasswordLength = (password) => {
  return (
    validationHelper.passwordLength(password) ||
    "Password length must be greater than 6 character."
  );
}

const isEmailValid = (email) => {
  return validationHelper.emailValidation(email) || "Invalid email";
}

const showPwd = () => {
  if (pwdType.value === "password") pwdType.value = "";
  else pwdType.value = "password";
}

const login = async () => {
  const result = await loginFormRef.value.validate();
  if (!!!result) {
    return;
  }

  loading.value = false;

  try {

    let response = await authRequest.login({
      email: form.value.email,
      password: form.value.password
    }).then((response) => {
      let data = response.data
      if (data.access_token) {
        if (self.rememberMe) {
          Cookies.set("token", data.access_token, {expires: 365});
          Cookies.set("roles", data.roles, {expires: 365});
          Cookies.set("user_first_name", data.user.first_name, {expires: 365});
          Cookies.set("user_id", data.user.id, {expires: 365});
          Cookies.set("user_type", data.user.roles[0].name, {
            expires: 365,
          });
          Cookies.set("permissions", data.permissions, {
            expires: 365,
          });

        } else {
          Cookies.set("user_id", data.user.id);
          Cookies.set("user_first_name", data.user.first_name);
          Cookies.set("token", data.access_token);
          Cookies.set("roles", data.roles);
          Cookies.set("user_type", data.user.roles[0].name);
          Cookies.set("permissions", data.permissions);

        }
        userStore.setUser(data.user)
        userStore.setUserRoles(data.roles)
        userStore.setUserPermissions(data.permissions)

        routeRedirectToUserDashboard();
      }
    })
  } catch (error) {
    console.log(error)
    let message = "Error encountered while submitting your data";
    if (typeof error.response.data !== "undefined") {
      if (typeof error.response.data.errors !== "undefined") {
        const keys = Object.keys(error.response.data.errors);
        message = error.response.data.errors[keys[0]][0];
      } else if (
        typeof error.response.data.message !== "undefined" &&
        !error.response.data.sucess
      ) {
        message = error.response.data.message;
      } else {
        const messages = Object.values(error.response.data);
        if (messages.length > 0) {
          message = messages[0];
        }
      }
    } else {
      message = error.response.message;
    }
    $q.notify({
      type: "negative",
      message: message,
    });

    loading.value = false;
  } finally {
    const timeout = 1000;
    setTimeout(() => {
      loading.value = false;
    }, timeout);
  }
}

const routeRedirectToUserDashboard = () => {
  router.push(
    { path: "/", params: { ...route.params } },
    () => {
      router.go(0);
    }
  );
}



</script>

<style scoped>

</style>
