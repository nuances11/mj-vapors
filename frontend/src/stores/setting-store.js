import { defineStore } from 'pinia';
import {reactive, watch} from "vue";
import {LocalStorage} from "quasar";


const defaultSetting = {
  company: {
    company_name: "",
    logo: 'default-logo.png',
  },
  user: {
    id: null,
    base_salary: 0,
    commission_threshold: 0,
    commission_rate: 0,
    additional_salary: 0
  }
}

export const useSettingStore = defineStore('setting', () => {
  const setting = reactive(defaultSetting);

  if (LocalStorage.getItem("setting")) {
    Object.assign(setting, JSON.parse(LocalStorage.getItem("setting")));
  }

  watch(
    setting,
    (settingValue) => {
      LocalStorage.set("setting", JSON.stringify(settingValue));
    },
    { deep: true, immediate: true }
  );

  const resetSetting = () => {
    Object.assign(setting, defaultSetting)
    LocalStorage.clear()
  }

  const setCompanySetting = (companyData) => {
    setting.company.company_name = companyData.company_name || null;
    setting.company.logo = companyData.logo || 'default-logo.png';
    console.log('setCompanySetting', setting)
  }

  const setUserSetting = (userData) => {
    setting.user.id = userData.id || null;
    setting.user.base_salary = userData.base_salary || null;
    setting.user.commission_threshold = userData.commission_threshold || null;
    setting.user.commission_rate = userData.commission_rate || null;
    setting.user.additional_salary = userData.additional_salary || null;
  }

  return {
    setting,
    setCompanySetting,
    resetSetting,
    setUserSetting
  }

});
