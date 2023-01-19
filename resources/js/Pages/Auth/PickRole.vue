<template>
    <AuthenticatedLayout>
        <!--Snackbars-->
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
            :color="snackbar.color + ' accent-2'"
            top
            right
        >
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn
                    text
                    v-bind="attrs"
                    @click="snackbar.status = false"
                >
                    Cerrar
                </v-btn>
            </template>
        </v-snackbar>

        <v-container class="d-flex flex-column justify-center align-center fill-height">
            <div>
                <v-row>
                    <v-col cols="12">
                        <p class="text-h5 text-center">
                            Por favor, selecciona el rol con el que deseas autenticarte
                        </p>
                    </v-col>
                    <v-col cols="12">
                        <v-select
                            color="primario"
                            v-model="selectedRoleId"
                            :items="roles"
                            label="Selecciona un rol"
                            :item-value="(role)=>role.id"
                            :item-text="(role)=>role.name"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-center">
                        <v-btn
                            color="primario"
                            class="grey--text text--lighten-4"
                            @click="selectRole"
                        >
                            Seleccionar rol
                        </v-btn>
                    </v-col>
                </v-row>
            </div>


        </v-container>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import {showSnackbar} from "@/HelperFunctions";

export default {
    components: {
        AuthenticatedLayout,
        InertiaLink,
        ConfirmDialog
    },
    data: () => ({
        snackbar: {
            text: "",
            color: 'red',
            status: false,
            timeout: 2000,
        },
        showDialog: false,
        roles: [],
        selectedRoleId: 0,
    }),
    created() {

        this.getUserRoles()
    },
    methods: {

        async getUserRoles() {
            let url = route('api.users.roles.show', {user: this.$page.props.user.id});
            try {
                let request = await axios.get(url);
                this.roles = request.data;
            } catch (e) {
                console.log(e);
            }
        },

        async selectRole() {
            if (this.selectedRoleId === 0) {
                showSnackbar(this.snackbar, 'Por favor, selecciona un rol de la lista', 'red');
                return;
            }
            const url = route('api.roles.selectRole');
            const redirect = route('redirect');
            try {
                await axios.post(url, {
                    role: this.selectedRoleId
                })
                window.location.href = redirect;
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red');
            }
        }
    }
}
</script>
