<template>
    <AuthenticatedLayout>
        <!--Snackbars-->
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
            color="red accent-2"
            top
            right
        >
            {{ snackbar.text }}

        </v-snackbar>

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
                :items-per-page="5"
                class="elevation-1"
            >
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
                    <v-card-text>
                        <v-container>
                            <v-row>
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
                            </v-row>
                        </v-container>
                    </v-card-text>
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
                            @click="editUserRoleRequest"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <!------------Seccion de dialogos ---------->

            <!------------Seccion de Overlays ---------->

            <!--            <v-overlay :value="isLoading"
                                   absolute
                                   opacity="1">
                            <v-progress-circular
                                indeterminate
                                size="64"
                            ></v-progress-circular>
                        </v-overlay>-->
            <!------------Seccion de Overlays ---------->

        </v-container>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
    },
    data: () => {
        return {
            //Table info
            headers: [
                {text: 'ID', value: 'id'},
                {text: 'Nombre', value: 'name'},
                {text: 'Correo electrónico', value: 'email'},
                {text: 'Rol', value: 'role.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            users: [],
            roles: [],
            //Snackbars
            snackbar: {
                text: '...',
                status: false,
                timeout: 3000
            },
            //Dialogs
            editUserDialog: false,
            //User models
            editedUser: {
                name: ''
            },
            selectedRoleId: 0,

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
        openEditRoleModal: function (user) {
            this.editedUser = {...user};
            this.editUserDialog = true;
        },
        editUserRoleRequest: async function () {
            //Verify request
            if (this.selectedRoleId === 0) {
                this.snackbar.text = 'Por favor, selecciona un rol para el usuario';
                this.snackbar.status = true;
                return;
            }
            //Recollect information
            let data = {
                roleId: this.selectedRoleId,
            }

            console.log('entre aca');
            let url = route('api.users.roles.update', {'user': this.editedUser.id});
            try {
                let request = await axios.patch(url, data);
                this.editUserDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllUsers();

                //Clear role information
                this.editedUser = {
                    name: ''
                };
            } catch (e) {
                this.snackbar.text = prepareErrorText(e);
                this.snackbar.status = true;
            }
        },

        getAllUsers: async function () {
            let request = await axios.get(route('api.users.index'));
            this.users = request.data;
        },
        getAllRoles: async function () {
            let request = await axios.get(route('api.roles.index'));
            this.roles = request.data;
        },

    },


}
</script>
