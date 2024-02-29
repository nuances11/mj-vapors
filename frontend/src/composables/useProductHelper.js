export function useProductHelper() {
  const getColumns = () => {
    return [
      {
        name: "sku",
        label: "SKU",
        field: "code",
        align: "left",
      },
      {
        name: "name",
        label: "Name",
        field: row => row.product.name,
        align: "left",
        sortable: true,
      },
      // {
      //   name: "description",
      //   label: "Description",
      //   field: row => row.product.description,
      //   align: "left",
      // },
      {
        name: "variants",
        label: "Variant",
        align: "left",
        field: "variants",
      },
      {
        name: "price",
        label: "Price",
        align: "left",
        field: "price",
      },
      {
        name: "actions",
        align: "left",
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
