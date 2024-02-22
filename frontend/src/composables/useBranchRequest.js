import request from "../helpers/request";

export function useBranchRequest() {
  const api = "api/branches";

  const addBranch = (data) => {
    return request.post(api, data);
  }

  const getBranches = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getBranch = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateBranch = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteBranch = (id) => {
    return request.delete(api + "/" + id);
  };

  return {
    getBranches,
    getBranch,
    addBranch,
    updateBranch,
    deleteBranch,
  }
}
