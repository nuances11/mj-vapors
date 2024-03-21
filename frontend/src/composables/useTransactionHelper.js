export function useTransactionHelper() {
  const getColumns = () => {
    return [
      {
        name: "id",
        label: "Transaction ID",
        field: "id",
        align: "left",
        sortable: true,
      },
      {
        name: "transaction_type",
        label: "Transaction Type",
        field: "transaction_type",
        align: "left",
      },
      {
        name: "total_amount",
        label: "Total",
        field: "total_amount",
        align: "left",
      },
      {
        name: "transaction_date",
        label: "Transaction Date",
        field: "transaction_date",
        align: "left",
      },
      {
        name: "actions",
        align: "center",
        label: "Actions",
        field: "actions",
        sortable: false,
      }
    ];
  };

  return {
    getColumns,
  };
}
