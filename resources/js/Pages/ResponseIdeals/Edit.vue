<template>
    <AuthenticatedLayout>

        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>
        <v-container>
        <v-row>
            <v-col cols="6">

                <h3> Definir ideales de respuesta para el escalafón de docente: {{teachingLadder == 'Ninguno' ? 'Sin Escalafón' : teachingLadder}} </h3>

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
                                        @change="updateGraphOnChange(competences)"
                                    >
                                    </v-text-field>
                                </template>
                            </v-tooltip>
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>







            <v-col cols="6" class="px-10 mt-15 align-baseline" >
                <v-card >
                    <v-card-text style="position: relative; height:40vh; width:80vw">
                    <canvas id="MiGrafica"></canvas>
                    </v-card-text>
                </v-card>
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
import Chart from "chart.js/auto";


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
            dataToGraph: [],
            competencesValuesArray: [],
            chart:'',

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },

            //Overlays
            isLoading: true,

            numberRules: [
                v => !!v || 'El valor introducido debe ser un número',
            ]
        }


    },
    async created() {

        await this.getCompetences();
        this.fillArrayWithCompetencesValues(this.competences);
        this.getGraph();
        this.isLoading = false;

    },



    methods: {

        getPossibleInitialCompetences() {

            return new Question().getPossibleCompetencesAsArrayOfObjects();

        },

        async getCompetences(){

            let url = route('responseIdeals.get');

            let request = await axios.post(url, {teachingLadder: this.teachingLadder})

            if(request.data.length === 0){

                this.competences = this.getPossibleInitialCompetences();
            }

            else{

                this.competences = request.data;

            }

        },

        getGraph(){

            let miCanvas = document.getElementById("MiGrafica").getContext("2d");

            let hex = this.randomHexColor();

            this.chart = new Chart(miCanvas, {
                type:"line",
                data:{
                    labels: ["C1", "C2", "C3", "C4", "C5", "C6"],
                    datasets:[
                        {
                            label: `Ideales de respuesta para el escalafon:  ${this.teachingLadder == 'Ninguno' ? 'Sin Escalafón' : this.teachingLadder}`,
                            data: this.competencesValuesArray,
                            backgroundColor: hex,
                            borderColor: hex,
                            borderWidth: 3
                        }],

                },
                options: {
                    legend: {
                        display: false
                    },
                    responsive: true,
                    tooltips: {
                        mode: "index",
                        intersect: false
                    },
                    hover: {
                        mode: "nearest",
                        intersect: true
                    },
                    scales: {
                        x:
                            {
                                title: {
                                    display: true,
                                    text: 'Competencias'
                                },
                                position:"top",
                                ticks: {
                                    padding: 8,
                                }
                            }
                        ,
                        y:
                            {
                                max:5,
                                title: {
                                    display: true,
                                    text: 'Ideal de respuesta'
                                },

                                ticks: {
                                    beginAtZero: true,
                                    padding: 8,
                                    stepSize: 0.5,
                                }
                            }
                    }
                }
            })
        },

        async updateResponseIdeals(competences) {

            try{

                let url = route('responseIdeals.update');

                let request = await axios.post(url, {competences: competences,
                    teachingLadder: this.teachingLadder});

                this.competencesValuesArray.length = 0;

                this.fillArrayWithCompetencesValues(competences)

                showSnackbar(this.snackbar, request.data.message, 'success');

                /*this.updateGraph(this.competencesValuesArray);*/

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },


        updateGraph(competencesValues){

                this.chart.data.datasets[0].data=competencesValues
                this.chart.update();
        },


        updateGraphOnChange(competences){

            this.competencesValuesArray.length = 0;

            this.fillArrayWithCompetencesValues(competences);

            this.updateGraph(this.competencesValuesArray);

        },


        fillArrayWithCompetencesValues(competences){

            competences.forEach(competence =>{

                this.competencesValuesArray.push(competence.value);

            })

        },

        randomInteger(max) {
            return Math.floor(Math.random() * (max + 1));
        },

        randomRgbColor() {
            let r = this.randomInteger(255);
            let g = this.randomInteger(255);
            let b = this.randomInteger(255);
            return [r, g, b];
        },

        randomHexColor() {
            let [r, g, b] = this.randomRgbColor();

            let hr = r.toString(16).padStart(2, '0');
            let hg = g.toString(16).padStart(2, '0');
            let hb = b.toString(16).padStart(2, '0');

            return "#" + hr + hg + hb;
        }

    },

}
</script>
