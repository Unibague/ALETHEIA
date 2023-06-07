<template>
    <AuthenticatedLayout>

        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>
        <v-container>
        <v-row>
            <v-col cols="6">

                <h3> Definir ideales de respuesta para el escalafón de docente: {{teachingLadder}} </h3>

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
                                        :rules="numberRules"
                                        step="0.1"

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
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Question from "@/models/Question";
import Snackbar from "@/Components/Snackbar";

export default {
    components: {

        Snackbar,
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
    },
    props: ['teachingLadder'],

    data: () => {
        return {
            //Table info
            headers: [
                {text: 'Competencia', value: 'competence'},
                {text: 'Nombre', value: 'name', width:'60%'},
                {text: 'Valor', value: 'value'},

            ],
            competences:[],

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            createRoleDialog: false,
            deleteRoleDialog: false,
            editRoleDialog: false,

            //Overlays
            isLoading: true,

            numberRules: [
                v => !!v || 'El valor introducido debe ser un número',
            ]
        }


    },
    async created() {

        this.isLoading = false;
        await this.getResponseIdeals();
    },
    methods: {



        getPossibleInitialCompetences() {

            return new Question().getPossibleCompetencesAsArrayOfObjects();

        },


        async updateResponseIdeals(competences) {

            try{

                let url = route('responseIdeals.update');

                let request = await axios.post(url, {competences: competences,
                    teachingLadder: this.teachingLadder});

                showSnackbar(this.snackbar, request.data.message, 'success');

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }


        },



        async getResponseIdeals(){


            let url = route('responseIdeals.get');


            console.log(this.teachingLadder);

            let request = await axios.post(url, {teachingLadder: this.teachingLadder})

   /*         console.log(request.data);*/

            if(request.data.length == 0){

                this.competences = this.getPossibleInitialCompetences();

            }

            else{

                this.competences = request.data;

            }


        }


    },


}
</script>
