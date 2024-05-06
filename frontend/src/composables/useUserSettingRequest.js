import request from "../helpers/request";

export function useUserSettingRequest() {
  const api = "api/setting/user";

  const getUserSetting = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateUserSetting = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  return {
    getUserSetting,
    updateUserSetting,
  }
}
