import { defineStore } from 'pinia';
import {reactive, watch} from "vue";
import {LocalStorage} from "quasar";


const defaultUser = {
  id: null,
  first_name: "",
  last_name: "",
  full_name: "",
  email: "",
  user_type: "",
  status: "",
  roles: [],
  permissions: [],
  branch: []
}

export const useUserStore = defineStore('user', () => {
  const user = reactive(defaultUser);

  if (LocalStorage.getItem("user")) {
    Object.assign(user, JSON.parse(LocalStorage.getItem("user")));
  }

  watch(
    user,
    (userValue) => {
      LocalStorage.set("user", JSON.stringify(userValue));
    },
    { deep: true, immediate: true }
  );

  const resetUser = () => {
    Object.assign(user, defaultUser)
    console.log('resetUser defaultUser', defaultUser)
    console.log('resetUser', user)
    LocalStorage.clear()
  }

  const setUser = (userData) => {
    user.id = userData.id || null;
    user.first_name = userData.first_name || "";
    user.last_name = userData.last_name || "";
    user.full_name = userData.full_name || "";
    user.email = userData.email || "";
    user.status = userData.status || "";
    user.user_type = userData.user_type || "";
  }

  const setUserRoles = (roles) => {
    user.roles = roles || [];
  }

  const setUserPermissions = (permissions) => {
    user.permissions = permissions || [];
  }

  const setUserBranch = (branch) => {
    user.branch = branch;
  }

  return {
    user,
    resetUser,
    setUser,
    setUserRoles,
    setUserPermissions,
    setUserBranch
  }

});
