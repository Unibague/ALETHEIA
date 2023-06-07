<template>
    <AuthenticatedLayout>


        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h3 class="align-self-start">Definir ideales de respuesta para cada escalafón</h3>
            </div>

            <!--Inicia tabla-->
            <v-card max-width="45%">
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

                    <template v-slot:item.actions="{item}">


                        <v-tooltip top>
                        <template v-slot:activator="{on,attrs}">

                            <InertiaLink :href="route('responseIdeals.edit.view', {teachingLadder:item.name})">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                >
                                    mdi-pencil
                                </v-icon>

                            </InertiaLink>

                        </template>
                        <span>Editar ideales de respuesta</span>
                    </v-tooltip>


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
                {text: 'C6', value: '', sortable: false},
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



    },


}
</script>
