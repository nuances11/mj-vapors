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

  const getUser = (data) => {
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

  return {
    getUsers,
    getUser,
    addUser,
    updateUser,
    deleteUser
  }
}
