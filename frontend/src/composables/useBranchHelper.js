export function useBranchHelper() {
  const getColumns = () => {
    return [
      {
        name: "name",
        label: "Name",
        field: "name",
        align: "left",
        sortable: true,
      },
      {
        name: "status",
        label: "Status",
        field: "status",
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
