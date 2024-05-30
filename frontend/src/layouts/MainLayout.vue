<template>
  <q-layout view="hHh lpR fFf" class="bg-grey-1">
    <q-header elevated class="text-white q-py-xs" style="background: #24292e" height-hint="58">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          @click="toggleLeftDrawer"
          aria-label="Menu"
          icon="menu"
        />

        <CompanyDetails
          v-if="!$q.screen.lt.md"
          :key="headerComponentKey"
          :company-logo="companyLogo"
          :company-name="companyName"
          :current-branch="currentBranch ? currentBranch.name : ''"
        />

        <q-space />
        <clock-container/>
        <q-space />

        <div v-if="!$q.screen.lt.md" class="q-gutter-sm row items-center no-wrap">
          <span v-if="first_name" class="text-subtitle1">
            Welcome, <strong>{{ first_name }}</strong>
          </span>
          <q-icon
            class="YL__fullscreen"
            size="28px"
            @click="$q.fullscreen.toggle()"
            :name="$q.fullscreen.isActive ? 'fullscreen_exit' : 'fullscreen'"
          >
            <q-tooltip>Go Fullscreen</q-tooltip>
          </q-icon>
          <q-btn round dense flat color="grey-8" icon="notifications">
            <q-badge color="red" text-color="white" floating>
              2
            </q-badge>
            <q-tooltip>Notifications</q-tooltip>
          </q-btn>
          <q-btn round flat>
            <q-avatar size="26px">
              <img src="https://cdn.quasar.dev/img/boy-avatar.png">
            </q-avatar>
            <q-tooltip>Account</q-tooltip>
          </q-btn>

          <div class="q-pa-md">
            <q-btn
              class=""
              color="red-5"
              icon="fa-solid fa-power-off"
              label="LOGOUT"
              @click="logout"
              flat
              dense
            />
          </div>
        </div>
        <div v-else>
          <q-btn
            flat
            rounded
            padding="sm"
            icon="settings_power"
            color="red-5"
            size="lg"
            aria-label="Logout"
            @click="logout"
          >
            <q-tooltip> Logout </q-tooltip>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      class="bg-grey-9 text-white"
      :width="240"
    >
      <q-scroll-area class="fit">
        <div class="q-ml-none" style="display: block">

          <CompanyDetails
            v-if="$q.screen.lt.md"
            :is-mobile="true"
            :key="headerComponentKey"
            :company-logo="companyLogo"
            :company-name="companyName"
            :current-branch="currentBranch ? currentBranch.name : ''"
          />
        </div>
        <q-list padding>
          <q-item
            v-ripple
            clickable
            to="/transactions"
            class="text-subtitle1"
          >
            <q-item-section avatar>
              <q-icon size="medium" name="fa-solid fa-dollar" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Transactions</q-item-label>
            </q-item-section>
          </q-item>
          <q-expansion-item
            :content-inset-level="0.5"
            expand-separator
            icon="fa-solid fa-bag-shopping"
            label="Products"
            class="text-medium"
            v-if="isAdmin"
            style="font-size: 1rem; font-weight: 400;"
            >

              <q-item
                v-ripple
                clickable
                to="/products"
                class="text-subtitle1 color-white"
              >
                <q-item-section avatar>
                  <q-icon size="medium" color="grey" name="fa-solid fa-rectangle-list" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>List</q-item-label>
                </q-item-section>
              </q-item>

            <q-item
              v-ripple
              clickable
              to="/products/attributes"
              class="text-subtitle1 color-white"
              v-if="isAdmin"
            >
              <q-item-section avatar>
                <q-icon size="medium" color="grey" name="fa-solid fa-filter-circle-dollar" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Attributes</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>
          <q-item
            v-ripple
            clickable
            to="/users"
            class="text-subtitle1 color-white"
            v-if="isAdmin"
          >
            <q-item-section avatar>
              <q-icon size="medium" name="fa-solid fa-users" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Users</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-ripple
            clickable
            to="/branches"
            class="text-subtitle1 color-white"
            v-if="isAdmin"
          >
            <q-item-section avatar>
              <q-icon size="medium" name="fa-solid fa-store" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Branches</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-ripple
            clickable
            to="/inventory"
            class="text-subtitle1 color-white"
            v-if="isAdmin || isBranchAdmin"
          >
            <q-item-section avatar>
              <q-icon size="medium" name="fa-solid fa-warehouse" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Inventory</q-item-label>
            </q-item-section>
          </q-item>

          <q-expansion-item
            :content-inset-level="0.5"
            expand-separator
            icon="summarize"
            label="Reports"
            class="text-medium"
            v-if="isAdmin || isBranchAdmin"
            style="font-size: 1rem; font-weight: 400;"
          >

            <q-item
              v-ripple
              clickable
              to="/reports/transactions"
              class="text-subtitle1 color-white"
            >
              <q-item-section avatar>
                <q-icon size="medium" color="grey" name="receipt_long" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Transactions</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-ripple
              clickable
              to="/reports/users"
              class="text-subtitle1 color-white"
              v-if="isAdmin"
            >
              <q-item-section avatar>
                <q-icon size="medium" color="grey" name="account_box" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Users</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-ripple
              clickable
              to="/reports/time-tracking"
              class="text-subtitle1 color-white"
            >
              <q-item-section avatar>
                <q-icon size="medium" color="grey" name="schedule" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Time Tracking</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-item
            v-ripple
            clickable
            to="/settings"
            class="text-subtitle1 color-white"
            v-if="isAdmin"
          >
            <q-item-section avatar>
              <q-icon size="medium" name="settings" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Settings</q-item-label>
            </q-item-section>
          </q-item>

        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <div class="main_wrap" :style="$q.screen.lt.md ? 'padding: 10px' : ''">
        <router-view />
      </div>
    </q-page-container>

    <q-dialog class="alertDialog" persistent v-model="branchSelectorDialog">
      <q-card style="width: 300px;">
        <q-form
          ref="branchFormRef"
          class="q-gutter-md"
          @submit="submitBranchForm"
        >
          <q-card-section style="padding-bottom: 0;">
            <div>

              <q-select
                v-model="branchForm.branch"
                filled
                outlined
                square
                label="Select Branch"
                :options="branchOptions"
                :loading="branchOptionsLoading"
                hint="Select branch to start transactions."
                map-options
                option-label="name"
                class="q-mb-md"
                :rules="[(v) => !!v || 'Please select something']"
              />

            </div>
          </q-card-section>

          <q-card-actions align="right" style="margin-top: 0 !important;">
            <q-btn flat label="Submit" color="primary" type="submit" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-layout>
</template>

<script>
import {computed, defineComponent, inject, onMounted, ref} from 'vue'
import { fabYoutube } from '@quasar/extras/fontawesome-v6'
import {LocalStorage, useQuasar} from "quasar";
import Cookies from "js-cookie";
import {useAuthenticationRequest} from "src/composables/useAuthenticationRequest";
import {useAuthenticationHelper} from "src/composables/useAuthenticationHelper";
import {useRoute, useRouter} from "vue-router";
import {useUserStore} from "stores/user-store";
import {useBranchRequest} from "src/composables/useBranchRequest";
import {useSettingRequest} from "src/composables/useSettingRequest";
import {useCommonHelper} from "src/composables/useCommonHelper";
import {useSettingStore} from "stores/setting-store";
import CompanyDetails from "components/header/CompanyDetails.vue";
import ClockContainer from "components/time-clock/ClockContainer.vue";
import {useUserRequest} from "src/composables/useUserRequest";

export default defineComponent({
  name: 'MainLayout',
  components: {ClockContainer, CompanyDetails},

  setup () {
    const userRequest = useUserRequest()
    const bus = inject("bus");
    const branchSelectorDialog = ref(false)
    const leftDrawerOpen = ref(false)
    const search = ref('')
    const $q = useQuasar()
    const settingStore = useSettingStore()
    const userStore = useUserStore()
    const commonHelper = useCommonHelper()
    const settingRequest = useSettingRequest()
    const authRequest = useAuthenticationRequest()
    const authHelper = useAuthenticationHelper()
    const router = useRouter()
    const route = useRoute()
    const first_name = Cookies.get('user_first_name');
    const branchOptionsLoading = ref(false)
    const branchFormRef = ref(null)
    const branchOptions = ref([])
    const branchRequest = useBranchRequest()
    const branchForm = ref({
      branch: null
    })
    const companySetting = ref({
      id: null,
      company_name: null,
      logo: null,
    })
    const rand = ref(new Date().getTime() / 1000);
    const endpointRoute = ref(process.env.API_ROUTE)
    const headerComponentKey = ref(0)
    const userBranch = ref('')

    const currentPath = computed(() =>route.path)
    const currentBranch = computed( () => {
      if (userStore.user.user_type === 'vendor') {
        return userStore.user.branch
      } else if (userStore.user.user_type === 'branch_admin') {
        return userBranch.value
      }

      return ''
    })
    const companyName = computed(() => settingStore.setting.company.company_name)
    const companyLogo = computed(() => `${endpointRoute.value}/img/${settingStore.setting.company.logo}?r=${rand.value}`)


    const isAdmin = computed(() => ['admin', 'super_admin'].includes(userStore.user.user_type))
    const isBranchAdmin = computed(() => userStore.user.user_type === 'branch_admin')

    onMounted(() => {
      bus.on("company-setting-updated", () => {
        rand.value = new Date().getTime() / 1000
        headerComponentKey.value++
      });

      getUserBranch()

      geCompanySettings()
      let user = JSON.parse(LocalStorage.getItem("user"))
      if (user.user_type === 'vendor' && (user.branch === null || !Object.keys(user.branch).length))
        checkBranch()


    })

    const getUserBranch = async () => {
      if (userStore.user.user_type !== 'branch_admin') return
      const { data } = await userRequest.getUserBranch(userStore.user.id)
      userBranch.value = data.branch ?? null
    }

    const geCompanySettings = async () => {

      const { data } = await settingRequest.getCompanySetting(1);
      companySetting.value = data;
      if (!companySetting.value.logo)
        companySetting.value.logo = "default-logo.png";

      const ONE_SECOND = 1000;
      settingStore.setCompanySetting(data)

    }

    const getBranchOptions = async () => {
      branchOptionsLoading.value = true;
      let query = {}
      query.display_all = true;
      const { data } = await branchRequest.getBranches(query)
      branchOptions.value = data
      branchOptionsLoading.value = false;
    }

    async function submitBranchForm() {
      const result = await branchFormRef.value.validate();
      if (!!!result) {
        return;
      }
      console.log(branchForm.value.branch)
      userStore.setUserBranch(branchForm.value.branch);
      console.log(userStore.user)
      branchSelectorDialog.value = false;
    }

    async function checkBranch() {
      console.log('checkBranch', userStore.user.branch)
      if (!userStore.user.branch || !userStore.user.branch.length > 0) {
        await getBranchOptions()
        branchSelectorDialog.value = true;
      }

    }

    function toggleLeftDrawer() {
      leftDrawerOpen.value = !leftDrawerOpen.value
    }

    function logout() {
      $q.dialog({
        title: "Logout",
        message: "Are you sure you want to logout?",
        ok: {
          color: 'negative'
        },
        cancel: {
          color: 'secondary',
        },
        persistent: true
      })
        .onOk(() => {
          const token = Cookies.get("token");

          authRequest.logout({token: token})
            .then((response) => {
              if (response.success){
                authHelper.removeAllCookies();
                userStore.user.branch = [];
                userStore.resetUser()
                router.replace(
                  { path: "/login", params: { ...route.params } },
                  () => {
                    router.go(0);
                  }
                );
              }

            })

        })
    }

    return {
      fabYoutube,
      currentPath,
      currentBranch,
      isAdmin,
      isBranchAdmin,

      leftDrawerOpen,
      search,
      first_name,

      toggleLeftDrawer,
      logout,
      submitBranchForm,
      checkBranch,
      getUserBranch,
      geCompanySettings,
      branchSelectorDialog,
      branchOptionsLoading,
      branchOptions,
      branchFormRef,
      branchForm,
      companySetting,
      rand,
      commonHelper,
      endpointRoute,
      companyLogo,
      companyName,
      bus,
      headerComponentKey,
      userBranch
    }
  }
})

</script>
<style lang="sass">
.text-medium i
  font-size: medium !important

.q-item.q-router-link--active
  color: #e9ad03
  font-weight: bold
  background: #fafafa
  border-left: none

.active-menu
  background: #fafafa
  border-left: none

.color-coin
  color: #e9ad03
  font-weight: bold
  background: #fafafa
  border-left: none

.main_wrap
  padding: 30px
  border: 0

.YL

  &__fullscreen
    cursor: pointer

  &__toolbar-input-container
    min-width: 100px
    width: 55%

  &__toolbar-input-btn
    border-radius: 0
    border-style: solid
    border-width: 1px 1px 1px 0
    border-color: rgba(0,0,0,.24)
    max-width: 60px
    width: 100%

  &__drawer-footer-link
    color: inherit
    text-decoration: none
    font-weight: 500
    font-size: .75rem

    &:hover
      color: #000
</style>
