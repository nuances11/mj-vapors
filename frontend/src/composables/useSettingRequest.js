import request from "../helpers/request";

export function useSettingRequest() {
  const api = "api/setting";

  const getCompanySetting = (id, data) => {
    return request.get(api + "/company/" + id, {
      params: data,
    });
  };

  const updateCompanySetting = (id, data) => {
    return request.patch(api + "/company/" + id, data);
  };


  return {
    getCompanySetting,
    updateCompanySetting
  }
}
