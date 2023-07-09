<template>
    <AuthenticatedLayout>


        <v-container fluid>

            <v-row class="mr-5">
                <v-col cols="6">

                    <div class="d-flex flex-column align-end mb-8">
                        <h3 class="align-self-start">Definir ideales de respuesta para cada escalafón de cada facultaad</h3>
                    </div>

                    <!--Inicia tabla-->
                    <v-card max-width="850%">
                        <v-data-table
                            loading-text="Cargando, por favor espere..."
                            :loading="isLoading"
                            :headers="headers"
                            :items="finalTeachingLadders"
                            :hide-default-footer="true"


                        >
                            <template v-slot:item.name="{ item }"  >

                                {{ item.name == 'Ninguno' ? 'Sin Escalafón' : item.name}}

                            </template>

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

                </v-col>



                <v-col cols="6" class="mt-6">

                        <v-container style="position: relative; height:50vh; width:90vw; background: #FAF9F6">
                            <canvas id="MiGrafica"></canvas>
                        </v-container>

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
import Chart from "chart.js/auto";

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
                {text: 'Escalafon', value: 'name', width:'15%'},
                {text: 'C1', value: 'C1'},
                {text: 'C2', value: 'C2'},
                {text: 'C3', value: 'C3', sortable: false},
                {text: 'C4', value: 'C4', sortable: false},
                {text: 'C5', value: 'C5', sortable: false},
                {text: 'C6', value: 'C6', sortable: false},
                {text: 'Editar', value: 'actions', sortable: false},
            ],

            finalTeachingLadders: [],
            responseIdeals:[],
            dataToGraph: [],
            chart:'',
            datasets:[],
            competencesValuesAsArray:[],
            //Snackbars
            snackbar: {
            text: "",
            color: 'red',
            status: false,
            timeout: 2000,
        },


            //Overlays
            isLoading: true,
        }
    },
    async created() {
        await this.getSuitableTeachingLadders()
        await this.getAllResponseIdeals()
        this.getGraph()
        this.isLoading = false;

    },
    methods: {


        getSuitableTeachingLadders: async function (){

            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));

            console.log(request.data)

            let teachingLadders = request.data

            teachingLadders.forEach(teachingLadder =>{

                if(teachingLadder == 'NIN'){

                    this.finalTeachingLadders.unshift({name : 'Ninguno',
                    identifier:teachingLadder})
                }

                if(teachingLadder == 'AUX'){

                    this.finalTeachingLadders.unshift({name : 'Auxiliar',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASI'){
                    this.finalTeachingLadders.unshift({name : 'Asistente',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASO'){
                    this.finalTeachingLadders.unshift({name : 'Asociado',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'TIT'){
                    this.finalTeachingLadders.unshift({name : 'Titular',
                        identifier:teachingLadder})
                }

            })

            console.log(this.finalTeachingLadders);

        },

        async getAllResponseIdeals() {

            let url = route('responseIdeals.get');

            let request = await axios.get(url);

            this.responseIdeals = request.data;

            console.log(this.responseIdeals, 'responseIdealsFromEndPoint');

            this.responseIdeals.forEach(responseIdeal =>{

                responseIdeal.teaching_ladder = this.capitalize(responseIdeal.teaching_ladder);

            })


            this.responseIdeals.forEach( responseIdeal =>{

                this.finalTeachingLadders.forEach( teachingLadder =>{

                    if(teachingLadder.name == responseIdeal.teaching_ladder){

                        teachingLadder.C1 = responseIdeal.response[0].value
                        teachingLadder.C2 = responseIdeal.response[1].value
                        teachingLadder.C3 = responseIdeal.response[2].value
                        teachingLadder.C4 = responseIdeal.response[3].value
                        teachingLadder.C5 = responseIdeal.response[4].value
                        teachingLadder.C6 = responseIdeal.response[5].value
                    }
                })
            })

            console.log(this.finalTeachingLadders);

        },


        setDatasets (){


            console.log(this.responseIdeals, 'jfjwfejfejwefj');

            this.responseIdeals.forEach(responseIdeal =>{

            let hex = this.randomHexColor()


             /*   const randomBetween = (min, max) => min + Math.floor(Math.random() * (max - min + 1));
                const r = randomBetween(0, 255);
                const g = randomBetween(0, 255);
                const b = randomBetween(0, 255);
                let rgb = `rgb(${r},${g},${b})`
*/

                if(responseIdeal.teaching_ladder === 'Ninguno'){

                    responseIdeal.teaching_ladder = 'Sin Escalafón'

                }

                this.datasets.push({

                    label: responseIdeal.teaching_ladder,
                    data: this.fillCompetencesArray(responseIdeal.response),
                    backgroundColor: hex,
                    borderColor: hex,
                    borderWidth: 2,
                    borderDash: [5, 5]
                })

            })

            console.log(this.datasets)

            return this.datasets


        },


        fillCompetencesArray(competencesValuesAsArrayOfObjects){

            let array = []

            competencesValuesAsArrayOfObjects.forEach(competenceValue =>{

                array.push(competenceValue.value)

            })

            return array

        },

        getGraph(){

            let miCanvas = document.getElementById("MiGrafica").getContext("2d");
            this.chart = new Chart(miCanvas, {
                type:"line",
                data:{
                    labels: ["C1", "C2", "C3", "C4", "C5","C6"],
                    datasets: this.setDatasets(),
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

        capitalize($field){

            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },

 /*       random_rgba() {
            let o = Math.round, r = Math.random, s = 255;
            return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + r().toFixed(1) + ')';
},*/
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
