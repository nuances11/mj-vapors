import axios from "axios";
import Cookies from "js-cookie";
import router from "src/router";
const url = process.env.API_ROUTE;

const service = axios.create({
  baseURL: url,
});

service.defaults.headers.common["Content-Type"] =
  "application/x-www-form-urlencoded; charset=UTF-8";

// Request interceptor
service.interceptors.request.use(
  (config) => {
    config.headers["Authorization"] = "Bearer " + Cookies.get("token");
    return config;
  },
  (error) => {
    // Do something with request error
    console.log(error); // for debug
    Promise.reject(error);
  }
);

// response pre-processing
service.interceptors.response.use(
  (response) => {
    return response.data;
  },
  async(error) => {
    // if unauthorized, redirect to log in
    if (error.response.status === 401) {
      Cookies.remove("token");
      Cookies.remove("user_type");
      return await router().go()
    }

    return Promise.reject(error);
  }
);

export default service;
