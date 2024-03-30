import {capitalize} from "vue";
import {useCommonHelper} from "src/composables/useCommonHelper";

export function useTransactionHelper() {
  const commonHelper = useCommonHelper()
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
        name: "branch",
        label: "Branch",
        field: row => row.branch.name,
        align: "left",
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
        field: row => commonHelper.numberFormat(row.total_amount),
        align: "left",
      },
      {
        name: "transaction_status",
        label: "Status",
        field: row => capitalize(row.status),
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
