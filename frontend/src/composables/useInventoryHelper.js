export function useInventoryHelper() {
  const getColumns = () => {
    return [
      {
        name: "product_sku",
        label: "SKU",
        field: "product_sku",
        align: "left",
        sortable: true,
      },
      {
        name: "branch_name",
        label: "Branch",
        field: "branch_name",
        align: "left",
        sortable: true,
      },
      {
        name: "stock_quantity",
        label: "Quantity",
        field: "stock_quantity",
        sortable: true,
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
