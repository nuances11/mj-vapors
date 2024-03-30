import { defineStore } from 'pinia';
import {reactive, watch} from "vue";
import {LocalStorage} from "quasar";


const defaultSetting = {
  company: {
    company_name: "",
    logo: 'default-logo.png',
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

  return {
    setting,
    setCompanySetting,
    resetSetting
  }

});
