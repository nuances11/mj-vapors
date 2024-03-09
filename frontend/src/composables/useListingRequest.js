import request from "../helpers/request";

export function useListingRequest() {
  const api = "api/listings";

  const addListing = (data) => {
    return request.post(api, data);
  }

  const getListings = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getListing = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const updateListing = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteListing = (id) => {
    return request.delete(api + "/" + id);
  };

  return {
    getListings,
    getListing,
    addListing,
    updateListing,
    deleteListing,
  }
}
