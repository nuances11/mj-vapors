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

        <q-btn flat no-caps no-wrap class="q-ml-xs" v-if="$q.screen.gt.xs">
<!--          <q-icon :name="fabYoutube" color="red" size="28px" />-->
          <img
            alt="Theme logo"
            src="~assets/logo.jpg"
            style="width: 50px;"
          >
          <q-toolbar-title shrink class="text-weight-bold">
            MJ VAPORS
          </q-toolbar-title>
        </q-btn>

        <q-space />


        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">
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
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-grey-9 text-white"
      :width="240"
    >
      <q-scroll-area class="fit">
        <q-list padding>
          <q-expansion-item
            :content-inset-level="0.5"
            expand-separator
            icon="fa-solid fa-bag-shopping"
            label="Products"
            >

              <q-item
                v-ripple
                clickable
                to="/products"
                class="text-subtitle1 color-white"
              >
                <q-item-section avatar>
                  <q-icon color="grey" name="fa-solid fa-rectangle-list" />
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
            >
              <q-item-section avatar>
                <q-icon color="grey" name="fa-solid fa-filter-circle-dollar" />
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
          >
            <q-item-section avatar>
              <q-icon name="fa-solid fa-users" />
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
          >
            <q-item-section avatar>
              <q-icon name="fa-solid fa-store" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Branches</q-item-label>
            </q-item-section>
          </q-item>

        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <div class="main_wrap">
        <router-view />
      </div>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { fabYoutube } from '@quasar/extras/fontawesome-v6'
import { useQuasar } from "quasar";
import Cookies from "js-cookie";
import {useAuthenticationRequest} from "src/composables/useAuthenticationRequest";
import {useAuthenticationHelper} from "src/composables/useAuthenticationHelper";
import {useRoute, useRouter} from "vue-router";

export default defineComponent({
  name: 'MainLayout',

  setup () {
    const leftDrawerOpen = ref(false)
    const search = ref('')
    const $q = useQuasar()
    const authRequest = useAuthenticationRequest()
    const authHelper = useAuthenticationHelper()
    const router = useRouter()
    const route = useRoute()
    const first_name = Cookies.get('user_first_name');

    // $q.loadingBar.setDefaults({
    //   color: 'red',
    //   size: '15px',
    //   position: 'top'
    // })

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

      leftDrawerOpen,
      search,
      first_name,

      toggleLeftDrawer,
      logout,

      links1: [
        {icon: 'fa-solid fa-cash-register', text: 'SALES', to: '#'},
        {icon: 'fa-solid fa-dolly', text: 'Products', to: '/products'},
        {icon: 'fa-solid fa-users', text: 'Users', to: '#'}
      ],
      links2: [
        {icon: 'folder', text: 'Library'},
        {icon: 'restore', text: 'History'},
        {icon: 'watch_later', text: 'Watch later'},
        {icon: 'thumb_up_alt', text: 'Liked videos'}
      ],
      links3: [
        {icon: fabYoutube, text: 'YouTube Premium'},
        {icon: 'local_movies', text: 'Movies & Shows'},
        {icon: 'videogame_asset', text: 'Gaming'},
        {icon: 'live_tv', text: 'Live'}
      ],
      links4: [
        {icon: 'settings', text: 'Settings'},
        {icon: 'flag', text: 'Report history'},
        {icon: 'help', text: 'Help'},
        {icon: 'feedback', text: 'Send feedback'}
      ],
      buttons1: [
        {text: 'About'},
        {text: 'Press'},
        {text: 'Copyright'},
        {text: 'Contact us'},
        {text: 'Creators'},
        {text: 'Advertise'},
        {text: 'Developers'}
      ],
      buttons2: [
        {text: 'Terms'},
        {text: 'Privacy'},
        {text: 'Policy & Safety'},
        {text: 'Test new features'}
      ]
    }
  }
})
</script>
<style lang="sass">
.main_wrap
  padding: 30px

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
