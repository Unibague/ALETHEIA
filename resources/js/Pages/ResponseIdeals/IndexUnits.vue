<template>
    <AuthenticatedLayout>

        <v-container class="mt-5" style="max-width: 60%">
            <v-card>
                <v-card-title>
                    <span class="text-h5 text-center">Ideales de respuesta por facultad </span>
                </v-card-title>
                <v-col cols="12">
                        <h5 class="subtitle-1 mb-5">
                            Por favor, selecciona los ideales de respuesta que deseas visualizar:
                        </h5>

                    <InertiaLink v-for="faculty in faculties"
                                 :key="faculty.name" :href="route('unit.responseIdeals.index', {unitId: faculty.unit_identifier})">
                        <v-btn
                            color="primario"
                            class="white--text mb-4 mr-5" >
                            {{ capitalize(faculty.name) }}
                        </v-btn>

                    </InertiaLink>


                </v-col>
            </v-card>
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

            faculties: [],
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
     /*   await this.getSuitableTeachingLadders()*/
        await this.getFaculties();
        /*await this.getAllResponseIdeals()*/
        /* this.getGraph()*/
        this.isLoading = false;

    },
    methods: {


        getSuitableTeachingLadders: async function (){

            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));

            let teachingLadders = request.data

            teachingLadders.forEach(teachingLadder =>{

/*                if(teachingLadder == 'NIN'){

                    this.finalTeachingLadders.unshift({name : 'Ninguno',
                    identifier:teachingLadder})
                }*/

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


        async getFaculties (){

            let url = route('unit.getFaculties');

            let request = await axios.get(url);

            this.faculties = request.data

            console.log(this.faculties, 'faculties')


        },


        async getAllResponseIdeals() {

            let url = route('responseIdeals.get');

            let request = await axios.get(url);

            let responseIdeals = request.data;

            console.log(responseIdeals, 'responseIdealsFromEndPoint');

            this.faculties.forEach(faculty =>{

                faculty.responseIdeals = [];

                let filteredIdeals = responseIdeals.filter(ideal =>{

                    return ideal.unit_identifier == faculty.unit_identifier

                })

                filteredIdeals.forEach(ideal => {

                    faculty.responseIdeals.push({
                        teaching_ladder: ideal.teaching_ladder,
                        values: ideal.response
                    })

                })

                console.log(filteredIdeals, 'filteredddddd')

            })

            console.log(this.faculties, 'faculties after assignment');



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
                                    text: 'Nivel Esperado'
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
