import request from "../helpers/request";

export function useUserRequest() {
  const api = "api/users";

  const addUser = (data) => {
    return request.post(api, data);
  }

  const getUsers = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getUser = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateUser = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteUser = (id) => {
    return request.delete(api + "/" + id);
  };

  const checkUserPassword = (id, data) => {
    return request.get(api + "/" + id + "/check-password", {
      params: data,
    });
  };

  return {
    getUsers,
    getUser,
    addUser,
    updateUser,
    deleteUser,
    checkUserPassword
  }
}
