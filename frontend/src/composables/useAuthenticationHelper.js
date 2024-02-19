import Cookies from "js-cookie"
export function useAuthenticationHelper() {

  const removeAllCookies = () => {
    Cookies.remove("user_id");
    Cookies.remove("user_first_name");
    Cookies.remove("roles");
    Cookies.remove("token");
    Cookies.remove("user_type");
    Cookies.remove("permissions");
    Cookies.remove("show_change_password");
  }

  return {
    removeAllCookies
  }
}
