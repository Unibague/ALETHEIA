<template>
    <AuthenticatedLayout>


        <v-container>
        <v-row>
            <v-col cols="6">

                <h3> Definir ideales de respuesta para el escalafón XXXXX </h3>

                <v-card>

                    <v-data-table

                        loading-text="Cargando, por favor espere..."
                        :headers="headers"
                        :items="competences"
                        :hide-default-footer="true"
                        class="elevation-1 mt-5"

                    >
                        <template v-slot:item.name="{ item }">

                            <v-tooltip top>
                                <template v-slot:activator="{on,attrs}">
                                    <v-text-field

                                        label="Nombre de la competencia"
                                        v-model="competences[item.id].name"

                                    >


                                    </v-text-field>


                                </template>

                            </v-tooltip>

                        </template>


                        <template v-slot:item.value="{ item }">

                            <v-tooltip top>
                                <template v-slot:activator="{on,attrs}">

                                    <v-text-field
                                        label="Valor de la competencia"
                                        v-model="competences[item.id].value"
                                        single-line
                                        type="number"
                                        min=1
                                        max="5"
                                    >


                                    </v-text-field>


                                </template>



                            </v-tooltip>

                        </template>

                    </v-data-table>



                </v-card>
            </v-col>


            <v-col cols="6" class="px-10">

                <h2> Aqui va una gráfica XD</h2>

            </v-col>




            <v-col cols="12">



                <v-row justify="center">
                    <v-col cols="12" class="d-flex justify-center mt-5">
                        <v-btn

                            color="primario"
                            class="grey--text text--lighten-4"
                            @click="updateResponseIdeals(competences)"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-col>

                </v-row>

            </v-col>


            </v-row>
        </v-container>









    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Question from "@/models/Question";

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
                {text: 'Competencia', value: 'competence'},
                {text: 'Nombre', value: 'name'},
                {text: 'Valor', value: 'value'},

            ],
            competences:[],

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

        this.getPossibleCompetences();
        this.isLoading = false;
    },
    methods: {


        getPossibleCompetences() {

            this.competences = new Question().getPossibleCompetencesAsArrayOfObjects();

            console.log(this.competences);

        },


        async updateResponseIdeals(competences) {

            let url = route('responseIdeals.update');

            let request = await axios.post(url, {competences: competences});

            console.log(request.data);

        },



        async getResponseIdeals(){


            let url = route('responseIdeals.get');


        }


    },


}
</script>
