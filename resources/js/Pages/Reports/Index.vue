<template>
    <AuthenticatedLayout>


        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Reportes a generar</h2>
            </div>
            <!------------Seccion de dialogos ---------->
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
                {text: 'ID del rol', value: 'customId'},
                {text: 'Nombre del rol', value: 'name'},
                {text: 'Fecha de creación', value: 'created_at'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            roles: [],
            //Roles models
            newRole: {
                name: '',
                customId: '',
            },
            editedRole: {
                id: '',
                name: '',
                customId: '',
            },
            deletedRoleId: 0,
            //Snackbars
            snackbar: {
            text: "",
            color: 'red',
            status: false,
            timeout: 2000,
        },
            //Dialogs
            createRoleDialog: false,
            deleteRoleDialog: false,
            editRoleDialog: false,

            //Overlays
            isLoading: true,
        }
    },
    async created() {
        await this.getAllRoles();
        this.isLoading = false;
    },
    methods: {
        openEditRoleModal: function (role) {
            this.editedRole = {...role};
            this.editRoleDialog = true;
        },
        editRole: async function () {
            //Verify request
            if (this.editedRole.name === '' || this.editedRole.id === '') {
                this.snackbar.text = 'Debes proporcionar un nombre y Id para el nuevo rol';
                this.snackbar.status = true;
                return;
            }
            //Recollect information
            let data = {
                id: this.editedRole.id,
                name: this.editedRole.name,
                customId: this.editedRole.customId
            }

            try {
                let request = await axios.patch(route('api.roles.update', {'role': this.editedRole.id}), data);
                this.editRoleDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllRoles();

                //Clear role information
                this.editedRole = {
                    id: '',
                    name: '',
                    customId: '',
                };
            } catch (e) {
                this.snackbar.text = prepareErrorText(e);
                this.snackbar.status = true;
            }
        },

        confirmDeleteRole: function (role) {
            this.deletedRoleId = role.id;
            this.deleteRoleDialog = true;
        },

        deleteRole: async function (roleId) {
            try {
                let request = await axios.delete(route('api.roles.destroy', {role: roleId}));
                this.deleteRoleDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllRoles();

            } catch (e) {
                this.snackbar.text = e.response.data.message;
                this.snackbar.status = true;
            }

        },
        getAllRoles: async function () {
            let request = await axios.get(route('api.roles.index'));
            this.roles = request.data;
        },
        createRole: async function () {
            if (this.newRole.name === '' || this.newRole.id === '') {
                this.snackbar.text = 'Debes proporcionar un nombre y Id para el nuevo rol';
                this.snackbar.status = true;
                return;
            }

            let data = {
                name: this.newRole.name,
                customId: this.newRole.id
            }
            //Clear role information
            this.newRole = {
                name: '',
                id: ''
            }
            try {
                let request = await axios.post(route('api.roles.index'), data);
                this.createRoleDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.snackbar.color='success';
                this.getAllRoles();
            } catch (e) {
                this.snackbar.text = e.response.data.message;
                this.snackbar.status = true;
            }

        }
    },


}
</script>
