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

  const updateTransaction = (id, data) => {
    return request.patch(api + "/" + id, data);
  };

  const deleteTransaction = (id) => {
    return request.delete(api + "/" + id);
  };

  return {
    getTransactions,
    getTransaction,
    addTransaction,
    updateTransaction,
    deleteTransaction,
  }
}
