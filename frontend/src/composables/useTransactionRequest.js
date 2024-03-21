import request from "../helpers/request";

export function useTransactionRequest() {
  const api = "api/transactions";

  const addTransaction = (data) => {
    return request.post(api, data);
  }

  const getTransactions = (data) => {
    return request.get(api, {
      params: data,
    });
  };

  const getTransaction = (id, data) => {
    return request.get(api + "/" + id, {
      params: data,
    });
  };

  const getTransactionItems = (id, data) => {
    return request.get(api + "/" + id + "/items", {
      params: data,
    });
  };

  const updateTransaction = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteTransaction = (id) => {
    return request.delete(api + "/" + id);
  };

  const updateTransactionStatus = (id, data) => {
    return request.patch(api + "/" + id + "/update-status", data);
  };

  return {
    getTransactions,
    getTransaction,
    addTransaction,
    updateTransaction,
    deleteTransaction,
    getTransactionItems,
    updateTransactionStatus,
  }
}
