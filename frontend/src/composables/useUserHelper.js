export function useUserHelper() {
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
        name: "attribute_options",
        label: "Options",
        field: "attribute_options",
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
