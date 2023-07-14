<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start" > Área de servicio: {{this.capitalize(this.currentServiceArea.name)}} </h2>

                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="setDialogToAddAdmin"
                    >
                        Añadir Administrador de Área de Servicio
                    </v-btn>

                </div>
            </div>

            <h3 class="mt-9"> Administradores del Área de Servicio </h3>

            <!--Tabla profesores de la unidad-->
            <v-card class="mt-4" max-width="750px" >
                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headers"
                    :items="admins"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1 mx-auto"
                >

                    <template v-slot:item.actions="{ item }">
                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                    <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        class="mr-2 primario--text"
                                        @click="confirmDeleteAdmin(item.teacherId)"
                                    >
                                        mdi-delete
                                    </v-icon>
                            </template>
                            <span>Gestionar Usuarios</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>


            <!--Seccion de dialogos-->


            <!--Asignar Admin de unidad-->

            <v-dialog
                v-model="adminDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border"> Añadir adminsitrador a la unidad </span>
                    </v-card-title>
                    <v-card-text>
                        <span>Ingrese el nombre del funcionario</span>

                            <v-autocomplete
                                label="Por favor selecciona un usuario"
                                :items="listOfStaffMembers"
                                v-model="unitAdmin"
                                item-text="name"
                                item-value="id"
                                return-object
                            ></v-autocomplete>


                        <small>Los campos con * son obligatorios </small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primario"
                            text
                            @click="cancelAdminDialog()"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="assignAdmin(unitAdmin.id)"
                        >
                            Guardar cambios
                        </v-btn>

                    </v-card-actions>
                </v-card>
            </v-dialog>

            <confirm-dialog
                :show="deleteAdminDialog"
                @canceled-dialog="deleteAdminDialog = false"
                @confirmed-dialog="deleteAdmin()"
                max-width="600px"
            >
                <template v-slot:title>
                    Estás a punto de eliminar al administrador seleccionado
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>
        </v-container>


    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import Unit from "@/models/Unit";
import Snackbar from "@/Components/Snackbar";
import {showSnackbar} from "@/HelperFunctions";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },


    data: () => {
        return {
            //Table info
            adminDialog: false,
            deleteAdminDialog: false,
            currentServiceArea: {},
            admins: [],
            listOfStaffMembers: [],
            unitAdmin: '',
            headers: [
                {text: 'Nombre', value: 'name'},
                {text: 'Eliminar administrador', value: 'actions', width: '10%', sortable: false},
            ],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            isLoading: true,

        }


    },

    props: {
        serviceAreaCode: Object
    },

    async created() {

        this.currentServiceArea= this.serviceAreaCode;

        console.log(this.serviceAreaCode, "serviceArea")
    },

    async mounted(){

    await this.getAdmins();

    },


    methods:{

        async getAdmins() {

            let url = route('serviceArea.admins', {serviceAreaCode:this.currentServiceArea.code})

            let request = await axios.get(url);

            this.admins = request.data;

            console.log(this.admins, 'admins');

            this.admins.forEach(admin =>{

                admin.name = this.capitalize(admin.name);

            })

        },

        async getStaffMembersAndSortAlphabetically(){

            let request = await axios.get(route('staffMembers.index'));

            this.listOfStaffMembers = request.data;

            this.sortArrayAlphabetically(this.listOfStaffMembers);

                this.listOfStaffMembers.forEach(staffMember => {

                    staffMember.name = this.capitalize(staffMember.name)

                })

            console.log(this.listOfStaffMembers);

        },

        capitalize($field){

           return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },


        async setDialogToAddAdmin(){

            this.adminDialog = true

            await this.getStaffMembersAndSortAlphabetically();

        },

        sortArrayAlphabetically(array){

            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);

        },

        assignAdmin: async function (staffMemberId){

            try {

               let data = {
                    serviceAreaCode: this.currentServiceArea.code,
                    userId: staffMemberId,
                }

                let request = await axios.post(route('serviceArea.assignAdmin'), data);

                this.unitAdmin= {name: '', id:''};
                this.adminDialog= false;
                await this.getAdmins();
                showSnackbar(this.snackbar, request.data.message, 'success');

            }

            catch (e){

                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }

        },


        cancelAdminDialog(){
            this.adminDialog = false;
        },

        confirmDeleteAdmin: function (userId) {
            this.deletedAdminId = userId;
            this.deleteAdminDialog = true;
        },


        deleteAdmin: async function(){

            let data = {
                serviceAreaCode: this.currentServiceArea.code,
                userId : this.deletedAdminId
            }

            let url = route('serviceArea.deleteAdmin');

            let request = await axios.post(url, data);

            this.deleteAdminDialog = false;

            await this.getAdmins();

            showSnackbar(this.snackbar, request.data.message, 'success');
        },

},

}
</script>

