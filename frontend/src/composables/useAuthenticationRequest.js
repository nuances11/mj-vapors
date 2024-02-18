import request from "../helpers/request";

export function useAuthenticationRequest() {
  const login = (data)=> {
    return request.post("/api/login", data);
  }

  const logout = (data) => {
    return request.post("/api/logout", data);
  }

  return {
    login,
    logout
  }
}
