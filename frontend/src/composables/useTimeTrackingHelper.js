export function useTimeTrackingHelper() {

  const getVisibleColumns = () => {
    return [
      'name',
      'branch',
      'clock_in',
      'clock_in_remarks',
      'clock_out',
      'clock_out_remarks',
    ]
  }
  const getColumns = () => {
    return [
      {
        name: "name",
        label: "Name",
        field: row => row.user?.full_name,
        align: "left",
        sortable: true,
      },
      {
        name: "branch",
        label: "Branch",
        field: row => row.branch?.name,
        align: "left",
      },
      {
        name: "clock_in",
        align: "left",
        label: "Clock In",
        field: "clock_in",
        sortable: false,
      },
      {
        name: "clock_in_remarks",
        align: "left",
        label: "Clock In Remarks",
        field: "clock_in_remarks",
        sortable: false,
      },
      {
        name: "clock_out",
        align: "left",
        label: "Clock Out",
        field: "clock_out",
        sortable: false,
      },
      {
        name: "clock_out_remarks",
        align: "left",
        label: "Clock Out Remarks",
        field: "clock_out_remarks",
        sortable: false,
      },

    ];
  };

  return {
    getColumns,
    getVisibleColumns
  };
}
