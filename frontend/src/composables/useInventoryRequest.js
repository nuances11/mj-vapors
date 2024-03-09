import request from "../helpers/request";

export function useInventoryRequest() {
  const api = "api/inventories";

  const addInventory = (data) => {
    return request.post(api, data);
  }

  const getInventories = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getInventory = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateInventory = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteInventory = (id) => {
    return request.delete(api + "/" + id);
  };

  return {
    getInventories,
    getInventory,
    addInventory,
    updateInventory,
    deleteInventory,
  }
}
