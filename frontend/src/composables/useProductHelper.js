export function useProductHelper() {
  const getColumns = () => {
    return [
      {
        name: "name",
        label: "Name",
        field: "name",
        align: "left",
        sortable: true,
        style: "max-width: 200px",
      },
      {
        name: "description",
        label: "Description",
        field: "description",
        align: "left",
      },
      {
        name: "product_variant",
        label: "Variant",
        align: "left",
        field: row => row.name,
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
