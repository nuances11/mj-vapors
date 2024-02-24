import request from "../helpers/request";

export function useProductRequest() {
  const api = "api/products";

  const addProduct = (data) => {
    return request.post(api, data);
  }

  const getProducts = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getProduct = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateProduct = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteProduct = (id) => {
    return request.delete(api + "/" + id);
  };

  return {
    getProducts,
    getProduct,
    addProduct,
    updateProduct,
    deleteProduct,
  }
}
