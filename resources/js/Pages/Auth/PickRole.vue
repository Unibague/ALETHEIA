<template>
    <AuthenticatedLayout>
        <div class="" style="width:100%">
            <p class="text-h4 text-center">
                Por favor, selecciona el rol con el que deseas autentificarte
            </p>

            <v-select
                color="primario"
                v-model="selectedRoleId"
                :items="roles"
                label="Selecciona un rol"
                :item-value="(role)=>role.id"
                :item-text="(role)=>role.name"
            ></v-select>


        </div>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";

export default {
    components: {
        AuthenticatedLayout,
        InertiaLink,
        ConfirmDialog
    },
    data: () => ({
        showDialog: false,
        roles: []
    }),
    methods: {

        getUserRoles() {

        }
    }
}
</script>
