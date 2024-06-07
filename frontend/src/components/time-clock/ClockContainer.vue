<template>
  <div id="clock">
    <q-item
      clickable
      v-ripple
      @click.prevent="showTimeInDialog"
    >
      <q-item-section>
        <q-item-label class="text-h5 text-weight-bold text-white">{{ time }}</q-item-label>
        <q-item-label class="date text-white text-weight-thin" style="margin-top: 0" caption>{{ date }}</q-item-label>
      </q-item-section>
    </q-item>

    <q-dialog class="alertDialog" persistent v-model="timeInDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="card-section-header dialog-header">
          <div class="text-h6">{{ userStore.user.branch.name }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-mt-xs q-pt-xs">
          <div class="q-pa-xs">
            <div class="row">
              <div class="col">
                <strong>
                  Opening:
                </strong>
                <q-chip
                  size="md"
                  text-color="white"
                  color="grey-7"
                  icon="meeting_room"
                >
                {{ userStore.user.branch.opening }}
                </q-chip>
              </div>
              <div class="col">
                <strong>
                  Closing:
                </strong>
                <q-chip
                  size="md"
                  text-color="white"
                  color="grey-7"
                  icon="door_back"
                >
                  {{ userStore.user.branch.closing }}
                </q-chip>
              </div>
            </div>
          </div>
          <div class="q-pa-md">
            <q-btn-group v-if="!$q.screen.lt.md" spread push>
              <q-btn @click.prevent="logTime('clock_in')" :disable="timeInBtnDisable" color="green" :loading="loading" push glossy label="Time-in" icon="login" />
              <q-btn @click.prevent="logTime('break_in')" :disable="breakInBtnDisable" color="yellow" :loading="loading" push glossy label="Break-in" icon="alarm_on" />
              <q-btn @click.prevent="logTime('break_out')" :disable="breakOutBtnDisable" color="yellow" :loading="loading" push glossy label="Break-out" icon="update" />
              <q-btn @click.prevent="logTime('clock_out')" :disable="timeOutBtnDisable" color="red" :loading="loading" push glossy label="Time-out" icon="logout" />
            </q-btn-group>
            <div v-else >
              <q-btn-group spread push>
                <q-btn class="col" @click.prevent="logTime('clock_in')" :disable="timeInBtnDisable" color="green" :loading="loading" push glossy label="Time-in" icon="login" />
                <q-btn class="col"  @click.prevent="logTime('break_in')" :disable="breakInBtnDisable" color="yellow" :loading="loading" push glossy label="Break-in" icon="alarm_on" />
              </q-btn-group>
              <q-btn-group class="q-mt-xs" spread push>
                <q-btn class="col"  @click.prevent="logTime('break_out')" :disable="breakOutBtnDisable" color="yellow" :loading="loading" push glossy label="Break-out" icon="update" />
                <q-btn class="col"  @click.prevent="logTime('clock_out')" :disable="timeOutBtnDisable" color="red" :loading="loading" push glossy label="Time-out" icon="logout" />
              </q-btn-group>
            </div>
          </div>
          <q-markup-table class="q-mt-md q-mb-md">
            <thead>
            <tr>
              <th>Time-In</th>
              <th>Break-In</th>
              <th>Break-Out</th>
              <th>Time-Out</th>
            </tr>
            </thead>
            <tbody v-if="userTimeData">
              <tr>
                <td
                  class="text-center"
                  :class="userTimeData.clock_in_remarks === 'Late' ? 'text-red' : 'text-green'"
                >{{ convertToTime(userTimeData.clock_in) }}</td>
                <td class="text-center">{{ convertToTime(userTimeData.break_in) }}</td>
                <td class="text-center">{{ convertToTime(userTimeData.break_out) }}</td>
                <td
                  class="text-center"
                  :class="userTimeData.clock_out_remarks === 'Early' ? 'text-red' : 'text-green'"
                >{{ convertToTime(userTimeData.clock_out) }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          <div v-if="userTimeData" class="q-pa-xs">
            <div class="row">
              <div class="col">
                <strong>
                  Break Time:
                </strong>
                <q-chip
                  size="md"
                  text-color="white"
                  color="grey-7"
                  icon="history_toggle_off"
                >
                {{  userTimeData ? formatTime(userTimeData.break_time_in_seconds) : 'N/A' }}
                </q-chip>
              </div>
              <div class="col">
                <strong>
                  Work Time:
                </strong>
                <q-chip
                  size="md"
                  text-color="white"
                  color="grey-7"
                  icon="pending_actions"
                >
                {{  userTimeData ? formatTime(userTimeData.total_hours_in_seconds) : 'N/A'}}
                </q-chip>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="dialog_bottom">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>


</template>

<script setup>

import {capitalize, computed, onMounted, ref} from "vue";
import {useUserStore} from "stores/user-store";
import {useQuasar} from "quasar";
import {useTimeTrackingRequest} from "src/composables/useTimeTrackingRequest";
import {useTimeTrackingHelper} from "src/composables/useTimeTrackingHelper";

const timeTrackingRequest = useTimeTrackingRequest()
const timeTrackingHelper = useTimeTrackingHelper()
const $q = useQuasar()
const userStore = useUserStore()
const timeInDialog = ref(false)
const week = ref(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']);
const time = ref('')
const date = ref('')
const loading = ref(false)
const userTimeData = ref({})

const convertToTime = (dateTime) => {
  if (dateTime === null ) return
  var event = new Date(dateTime);
  return event.toLocaleTimeString()
}

const formatTime = (totalSeconds) => {
  const SEC_IN_HR = 3600;
  let timeStr = [
    `${Math.floor(totalSeconds % (SEC_IN_HR*24) / SEC_IN_HR)}`,
    `${Math.floor(totalSeconds % SEC_IN_HR / 60)}`,
    `${Math.floor(totalSeconds % 60)}`
  ]; // this could be on one line

  return timeStr.map(s=>s.padStart(2,'0')).join(':');
}

const timeInBtnDisable = computed(() => {
  // return userTimeData.value?.clock_in !== null
  return userTimeData.value && userTimeData.value?.clock_in !== null
})

const breakInBtnDisable = computed(() => {
  return userTimeData.value?.break_in !== null
})

const breakOutBtnDisable = computed(() => {
  return userTimeData.value?.break_out !== null
})

const timeOutBtnDisable = computed(() => {
  return userTimeData.value?.clock_out !== null
})


const checkLogData = async () => {
  if (!userStore.user.branch.id) return;
  if (userStore.user.user_type !== 'vendor') return;
  let query = {
    branch_id: userStore.user.branch.id,
    user_id: userStore.user.id,
  }
  const {data} = await timeTrackingRequest.checkLogData(query)
  userTimeData.value = data;
}

const logTime = async (action) => {
  loading.value = true
  if (!userStore.user.id) {
    alert('No user ID')
    return
  }
  if (!userStore.user.branch.id) {
    alert('No Branch selected')
    return
  }

  let logData = {
    action: action,
    user_id: userStore.user.id,
    branch_id: userStore.user.branch.id,
  }
  $q.dialog({
    title: 'Log Time',
    message: `Are you sure you want to proceed?`,
    cancel: true,
    persistent: true,
    html: true
  }).onOk(async () => {
    await timeTrackingRequest.logTime(logData)
      .then( async (response) => {
        if (!response.success) {
          $q.notify({
            type: "negative",
            icon: 'report_problem',
            message: response.message,
          });
        } else {
          $q.notify({
            type: "positive",
            icon: 'check_circle',
            message: response.message,
          });
          await checkLogData()
        }
      }).catch((e) => {
        let message = 'Server error.'
        if (e.response.data.message) {
          message = e.response.data.message
        }
        $q.notify({
            type: "negative",
            icon: 'report_problem',
            message: message,
          });
      });

    loading.value = false
  }).onCancel(() => {
    loading.value = false
  })
}

const showTimeInDialog = async () => {
  if (userStore.user.user_type !== 'vendor') return;
  await checkLogData()
  timeInDialog.value = true
}

const updateTime = () => {
  const cd = new Date();
  time.value = zeroPadding(cd.getHours(), 2) +
    ':' + zeroPadding(cd.getMinutes(), 2) +
    ':' + zeroPadding(cd.getSeconds(), 2);
  date.value = zeroPadding(cd.getFullYear(), 4) +
    '-' + zeroPadding(cd.getMonth()+1, 2) +
    '-' + zeroPadding(cd.getDate(), 2) + ' ' + week.value[cd.getDay()];
}

const zeroPadding = (num, digit) => {
  let zero = '';
  for(let i = 0; i < digit; i++) {
    zero += '0';
  }
  return (zero + num).slice(-digit);
}

onMounted(() => {
  setInterval(updateTime, 1000)
  checkLogData()
})

</script>

<style lang="scss">

#clock {
  //font-family: 'Share Tech Mono', monospace;
  text-align: center;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  color: #daf6ff;
  //text-shadow: 0 0 20px rgba(10, 175, 230, 1),  0 0 20px rgba(10, 175, 230, 0);
  .time {
    letter-spacing: 0.05em;
    font-size: 20px;
    padding: 5px 0;
  }
  .date {
    letter-spacing: 0.1em;
    font-size: 12px;
  }
  .text {
    letter-spacing: 0.1em;
    font-size: 12px;
    padding: 20px 0 0;
  }
}

</style>
