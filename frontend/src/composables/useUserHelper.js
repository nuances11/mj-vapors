export function useUserHelper() {
  const getColumns = () => {
    return [
      {
        name: "name",
        label: "Name",
        field: "full_name",
        align: "left",
        sortable: true,
      },
      {
        name: "user_type",
        label: "Type",
        field: "user_type",
        align: "left",
        sortable: true,
      },
      {
        name: "status",
        label: "Status",
        field: "status",
        align: "left",
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
