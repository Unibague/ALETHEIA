<template>
    <AuthenticatedLayout>

        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>
        <v-container fluid>

            <v-row class="mr-5">
                <v-col cols="9">

                    <div class="d-flex flex-column align-end mb-4">
                        <h3 class="align-self-start">{{capitalize(unit.name)}}</h3>
                    </div>

                    <!--Inicia tabla-->
                    <v-card>
                        <v-data-table
                            loading-text="Cargando, por favor espere..."
                            :loading="isLoading"
                            :headers="headers"
                            :items="teachingLadders"
                            :hide-default-footer="true"
                        >


                            <template v-slot:item.teaching_ladder="{ item }">

                                {{capitalize(item.teaching_ladder)}}

                            </template>


                            <template v-slot:item.actions="{item}">

                                <v-tooltip top>
                                    <template v-slot:activator="{on,attrs}">

                                            <v-icon
                                                v-bind="attrs"
                                                v-on="on"
                                                class="mr-2 primario--text"
                                                @click="editResponseIdeals(item)"
                                            >
                                                mdi-pencil
                                            </v-icon>

                                    </template>
                                    <span>Editar ideales de respuesta</span>
                                </v-tooltip>

                            </template>

                        </v-data-table>
                    </v-card>

                </v-col>

            </v-row>

        </v-container>

                <v-dialog
                    v-model="editDialog"
                    persistent
                >

                    <v-card class="py-4 px-4">

                        <v-card-title >

                           <h3 style="margin: auto"> Editando ideales de respuesta </h3>

                        </v-card-title>


                        <v-data-table
                            loading-text="Cargando, por favor espere..."
                            :headers="headersEdit"
                            :items="competences"
                            :hide-default-footer="true"
                        >

                            <template v-slot:item.value="{ item }">
                                <v-tooltip top>
                                    <template v-slot:activator="{on,attrs}">
                                        <v-text-field
                                            label="Valor de la competencia"
                                            v-model="competences[item.id].value"
                                            single-line
                                            style="text-align: center"
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

                        <v-card-actions>

                            <v-btn
                                color="primario"
                                class="white--text"
                                @click="editDialog = false"
                            >
                               Salir
                            </v-btn>

                            <v-btn
                                color="primario"
                                class="grey--text text--lighten-4"
                                @click="updateResponseIdeals(competences)"
                            >
                                Guardar Cambios
                            </v-btn>


                        </v-card-actions>
                    </v-card>

                </v-dialog>






                <!--
                               <v-col cols="6" class="mt-6">

                                        <v-container style="position: relative; height:50vh; width:90vw; background: #FAF9F6">
                                            <canvas id="MiGrafica"></canvas>
                                        </v-container>

                                </v-col>-->

<!--                <v-col cols="2" style="align-self: center; margin-left: 10px">

                    <InertiaLink :href="route('answers.index.view')">

                        <v-btn
                            class="mr-2 white&#45;&#45;text"
                            color="primario"
                        >
                            Editar ideales de respuesta
                        </v-btn>

                    </InertiaLink>

                </v-col>-->







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

    data: () => {
        return {
            //Table info
            headers: [
                {text: 'Escalafón', value: 'teaching_ladder'},
                {text: 'Orientación a la calidad educativa', value: 'response[0].value'},
                {text: 'Trabajo Colaborativo', value: 'response[1].value'},
                {text: 'Empatía Universitaria', value: 'response[2].value'},
                {text: 'Comunicación', value: 'response[3].value'},
                {text: 'Innovación del conocimiento', value: 'response[4].value'},
                {text: 'Productividad académica', value: 'response[5].value'},
                {text: 'Editar', value: 'actions'},
            ],


            headersEdit: [
                {text: 'Competencia', value: 'name'},
                {text: 'Valor', value: 'value', width:'20%', margin:'auto'},
        ],
            competences:[],
            dataToGraph: [],
            competencesValuesArray: [],
            chart:'',
            teachingLadders: [],
            teachingLadderEdit: [],
            editDialog: false,
            updateTeachingLadder: '',
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

    props: {
        unit: Object
    },


    async created() {

/*
        this.fillArrayWithCompetencesValues(this.competences);
*/
        await this.getUnitResponseIdeals();
        this.isLoading = false;

    },



    methods: {

        getPossibleInitialCompetences() {

            return new Question().getPossibleCompetencesAsArrayOfObjects();

        },

        async getUnitResponseIdeals(){

            let request = await axios.get(route('unit.responseIdeals.get', {unitId: this.unit.identifier}))

            this.teachingLadders = request.data;

            console.log(request.data);

        },

/*        getGraph(){

            let miCanvas = document.getElementById("MiGrafica").getContext("2d");

            let hex = this.randomHexColor();

            this.chart = new Chart(miCanvas, {
                type:"line",
                data:{
                    labels: ["C1", "C2", "C3", "C4", "C5", "C6"],
                    datasets:[
                        {
                            label: `Nivel esperado para el escalafon:  ${this.teachingLadder == 'Ninguno' ? 'Sin Escalafón' : this.teachingLadder}`,
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
        },*/


        async editResponseIdeals(item){

            console.log(item, "jejrejrjejerjrej")

            let request = await axios.get(route('unit.teachingLadder.responseIdeals.get', {unitId: this.unit.identifier, teachingLadder: item.teaching_ladder}));

            this.competences = request.data

            this.updateTeachingLadder = item.teaching_ladder;

            console.log(this.competences, 'competences to editttttt')

            this.editDialog = true

        },

        async updateResponseIdeals(competences) {

            try{

                let url = route('responseIdeals.update');

                let request = await axios.post(url, {competences: competences,
                    teachingLadder: this.updateTeachingLadder, unit: this.unit.identifier});

                this.competencesValuesArray.length = 0;

                this.fillArrayWithCompetencesValues(competences)

                showSnackbar(this.snackbar, request.data.message, 'success');

                /*this.updateGraph(this.competencesValuesArray);*/

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },


        fillArrayWithCompetencesValues(competences){

            competences.forEach(competence =>{

                this.competencesValuesArray.push(competence.value);

            })

        },


        capitalize($field){

            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },

    },

}
</script>
