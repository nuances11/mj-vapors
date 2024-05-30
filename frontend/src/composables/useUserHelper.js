import {useCommonHelper} from "src/composables/useCommonHelper";

export function useUserHelper() {
  const commonHelper = useCommonHelper()
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
        name: "email",
        label: "Email",
        field: "email",
        align: "left",
        sortable: true,
      },
      // {
      //   name: "branch",
      //   label: "Branch",
      //   field: row => row.branch?.name,
      //   align: "left",
      //   sortable: true,
      // },
      {
        name: "user_type",
        label: "Type",
        field: row => commonHelper.titleCase(row.user_type),
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
