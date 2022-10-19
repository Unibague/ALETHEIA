<template>
    <AuthenticatedLayout>
        <!--Snackbars-->
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="dd-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar usuarios</h2>
            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="users"
                :items-per-page="15"
                class="elevation-1"
            >
                <template v-slot:item.roles="{ item }">
                   <span v-for="(role,key) in item.roles" :key="item.id">
                       {{ key !== (item.roles.length - 1) ? `${role.name},` : role.name }}
                   </span>
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="openEditRoleModal(item)"
                    >
                        mdi-pencil
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->
            <!--Editar role-->
            <v-dialog
                v-model="editUserDialog"
                persistent
                max-width="600px"
            >
                <v-card>
                    <v-card-title>
                        <span class="text-h5 text-center">Cambiar el rol de {{ editedUser.name }}</span>
                    </v-card-title>
                    <v-col cols="12">
                        <span class="subtitle-1">
                            Por favor seleccione los roles que desee asignar al usuario
                        </span>

                        <v-checkbox v-for="role in roles" :key="role.name"
                                    :label="role.name"
                                    :value="role.id"
                                    v-model="editedUser.customRoles"
                        >
                        </v-checkbox>
                    </v-col>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            text
                            @click="editUserDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="changeUserRoles"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";


export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar
    },
    data: () => {
        return {
            //Table info
            headers: [
                {text: 'ID', value: 'id'},
                {text: 'Nombre', value: 'name'},
                {text: 'Correo electrónico', value: 'email'},
                {text: 'Roles', value: 'roles'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            users: [],
            roles: [],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            editUserDialog: false,
            //User models
            editedUser: {
                name: '',
                roles: [],
                customRoles: []
            },

            //overlays
            isLoading: true
        }
    },
    async created() {
        await this.getAllUsers();
        await this.getAllRoles();
        this.isLoading = false;
    },
    methods: {
        formatRoles: function () {
            const users = this.users;
            users.forEach((user) => {
                user.customRoles = [];
                user.roles.forEach((role) => {
                    user.customRoles.push(role.id)
                })

            })
        },

        openEditRoleModal: function (user) {
            this.editedUser = {...user};
            this.editUserDialog = true;
        },
        changeUserRoles: async function () {
            //Recollect information
            console.log(this.editedUser.customRoles);
            let data = {
                roles: this.editedUser.customRoles
            }

            let url = route('api.users.roles.update', {'user': this.editedUser.id});
            try {
                let request = await axios.patch(url, data);

                showSnackbar(this.snackbar, request.data.message);
                this.editUserDialog = false;
                this.getAllUsers();

                //Clear role information
                this.editedUser = {
                    name: ''
                };
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e),'alert');

                this.snackbar.text = prepareErrorText(e);
                this.snackbar.status = true;
            }
        },

        getAllUsers: async function () {
            let request = await axios.get(route('api.users.index'));
            this.users = request.data;
            this.formatRoles();
        },
        getAllRoles: async function () {
            let request = await axios.get(route('api.roles.index'));
            this.roles = request.data;
        },

    },


}
</script>
