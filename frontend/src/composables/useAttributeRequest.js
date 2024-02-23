import request from "../helpers/request";

export function useAttributeRequest() {
  const api = "api/attributes";

  const addAttribute = (data) => {
    return request.post(api, data);
  }

  const getAttributes = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getAttribute = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateAttribute = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteAttribute = (id) => {
    return request.delete(api + "/" + id);
  };

  const deleteAttributeOption = (id) => {
    return request.delete(api + "/option/" + id);
  };

  return {
    getAttributes,
    getAttribute,
    updateAttribute,
    deleteAttribute,
    addAttribute,
    deleteAttributeOption
  };
}
