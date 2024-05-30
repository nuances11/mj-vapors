import {useUserStore} from "stores/user-store";
import {computed} from "vue";

export function useInventoryHelper() {
  const userStore = useUserStore()
  const isAdmin = computed(() => ['admin', 'super_admin'].includes(userStore.user.user_type))
  const getColumns = () => {
    let columns = [
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
      }
    ];

    if (isAdmin.value) {
      columns.push({
        name: "actions",
        align: "center",
        label: "Actions",
        field: "actions",
        sortable: false,
      })
    }

    return columns
  };

  return {
    getColumns,
  };
}
