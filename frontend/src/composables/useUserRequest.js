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

  const getUserBranch = (id) => {
    return request.get(api + "/" + id + "/branch");
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

  const generateUserReport = (id, data) => {
    return request.get(api + "/report/" + id, {
      params: data,
    });
  }

  const updateUserPassword = (id, data) => {
    return request.patch(api + "/update-password/" + id, data);
  };

  return {
    getUsers,
    getUser,
    addUser,
    updateUser,
    deleteUser,
    checkUserPassword,
    generateUserReport,
    getUserBranch,
    updateUserPassword
  }
}
