export function useProductHelper() {
  const getColumns = () => {
    return [
      {
        name: "name",
        label: "Name",
        field: row => row.name,
        align: "left",
        sortable: true,
      },
      {
        name: "description",
        label: "Description",
        field: row => row.description,
        align: "left",
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
