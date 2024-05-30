import request from "../helpers/request";

export function useTimeTrackingRequest() {
  const api = "api/time-trackings";

  const getTimeTrackings = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const logTime = (data) => {
    return request.post(api + '/log-time', data);
  }

  const checkLogData = (data) => {
    return request.get(api + "/check-log-data", {
      params: data,
    });
  };

  return {
    logTime,
    checkLogData,
    getTimeTrackings
  }

}
