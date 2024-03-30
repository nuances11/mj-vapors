<template>
  <div>
    <q-splitter
      v-model="splitterModel"
    >

      <template v-slot:before>
        <q-tabs
          v-model="tab"
          vertical
          class="bg-grey-2 text-grey-7"
          indicator-color="grey-8"
        >
          <q-tab name="company" icon="business" label="Company" />
          <q-tab name="users" icon="manage_accounts" label="Users" />
        </q-tabs>
      </template>

      <template v-slot:after>
        <q-tab-panels
          v-model="tab"
          animated
          swipeable
          vertical
          transition-prev="jump-up"
          transition-next="jump-up"
        >
          <q-tab-panel name="company">
            <div class="text-h5 q-mb-md">Company Settings</div>
            <CompanySettings/>
          </q-tab-panel>

          <q-tab-panel name="users">
            <div class="text-h4 q-mb-md">Alarms</div>
            <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla ullam. In, libero.</p>
            <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla ullam. In, libero.</p>
          </q-tab-panel>
        </q-tab-panels>
      </template>

    </q-splitter>
  </div>
</template>

<script setup>

import {ref} from "vue";
import CompanySettings from "components/settings/CompanySettings.vue";

const tab = ref('company')
const splitterModel = ref(20)

</script>

<style lang="scss">

</style>
