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
  </div>

</template>

<script setup>

import {onMounted, ref} from "vue";

const week = ref(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']);
// const timerID = setInterval(updateTime, 1000);
const time = ref('')
const date = ref('')

const showTimeInDialog = () => {
  alert('Test')
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
