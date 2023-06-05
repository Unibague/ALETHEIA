<template>
    <AuthenticatedLayout>


        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h3 class="align-self-start">Definir ideales de respuesta para cada escalafón</h3>
            </div>

            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="finalTeachingLadders"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="mr-2 primario--text"
                            @click=""
                        >
                            mdi-pencil
                        </v-icon>
                    </template>
                </v-data-table>
            </v-card>



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
                {text: 'Escalafon', value: 'name'},
                {text: 'C1', value: ''},
                {text: 'C2', value: 'created_at'},
                {text: 'C3', value: '', sortable: false},
                {text: 'C4', value: '', sortable: false},
                {text: 'C5', value: '', sortable: false},
                {text: 'Editar', value: 'actions', sortable: false},
            ],

            finalTeachingLadders: [],
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
        await this.getSuitableTeachingLadders()
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

        getSuitableTeachingLadders: async function (){


            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));

            console.log(request.data)

            let teachingLadders = request.data

            teachingLadders.forEach(teachingLadder =>{

                if(teachingLadder == 'NIN'){

                    this.finalTeachingLadders.push({name : 'Ninguno',
                    identifier:teachingLadder})
                }

                if(teachingLadder == 'AUX'){

                    this.finalTeachingLadders.push({name : 'Auxiliar',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASI'){
                    this.finalTeachingLadders.push({name : 'Asistente',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASO'){
                    this.finalTeachingLadders.push({name : 'Asociado',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'TIT'){
                    this.finalTeachingLadders.push({name : 'Titular',
                        identifier:teachingLadder})
                }

            })

            console.log(this.finalTeachingLadders);

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
            console.log(this.roles);

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
