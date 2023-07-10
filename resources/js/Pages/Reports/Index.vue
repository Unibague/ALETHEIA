<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-5">
                <h2 class="align-self-start">Gestionar respuestas de evaluación por área de servicio</h2>
            </div>

            <v-container class="d-flex flex-column align-end mr-5">

<!--
                <v-btn
                    color="primario"
                    class="grey&#45;&#45;text text&#45;&#45;lighten-4"
                    @click=""
                >
                    Actualizar resultados
                </v-btn>
-->

            </v-container>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="6" >
                        <v-select
                            v-model="serviceArea"
                            flat
                            solo-inverted
                            hide-details
                            :items="serviceAreas"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="code"
                            prepend-inner-icon="mdi-home-search"
                            label="Áreas de Servicio"
                        ></v-select>
                    </v-col>

                    <v-col cols="5">
                        <v-select
                            v-model="teacher"
                            flat
                            solo-inverted
                            hide-details
                            :items="filteredTeachers"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="id"
                            prepend-inner-icon="mdi-account-search"
                            label="Docente"
                        ></v-select>
                    </v-col>


                    <v-col cols="1">
                        <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">

                                <v-btn
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 secundario--text"
                                    @click="downloadResults()"
                                >
                                    <v-icon>
                                        mdi-file-excel
                                    </v-icon>
                                </v-btn>
                            </template>
                            <span>Exportar resultados actuales a Excel</span>
                        </v-tooltip>
                    </v-col>


                </v-row>


            </v-toolbar>



            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="filteredItems"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.graph="{ item }">

                        <v-tooltip top
                        >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="setDialogToShowChart(item)"
                                >
                                    mdi-chart-line
                                </v-icon>

                            </template>
                            <span>Graficar resultados</span>
                        </v-tooltip>

                    </template>



                    <template v-slot:item.op_answers="{ item }">

                        <v-tooltip top
                        >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="setDialogToShowOpenAnswers(item)"
                                >
                                    mdi-text-box
                                </v-icon>

                            </template>
                            <span>Visualizar Comentarios</span>
                        </v-tooltip>

                    </template>

                </v-data-table>
            </v-card>


            <!--Seccion de dialogos-->

            <!--Transferir docente entre unidades-->

            <v-dialog
                v-model="showChartDialog"
                persistent
            >

                <v-card>

                    <v-card-text>

                        <h2 class="black--text ma-2" style="text-align: center"> Visualizando al docente: {{this.capitalize(this.selectedTeacherToGraph)}}</h2>

                    </v-card-text>


                    <v-container style="position: relative; height:60vh; width:90vw; background: #FAF9F6">
                        <canvas id="MiGrafica"></canvas>
                    </v-container>

                    <v-card-actions>

                        <v-btn
                            color="primario"
                            class="grey--text text--lighten-4"
                            @click="setDialogToCancelChart()"
                        >
                            Salir
                        </v-btn>


                    </v-card-actions>
                </v-card>


            </v-dialog>


            <v-dialog
                v-model="showOpenAnswersDialog"
                persistent
            >

                <v-card>

                    <v-card-text>
                        <h2 class="black--text pt-5" style="text-align: center"> Visualizando comentarios hacia el docente: {{ this.capitalize(this.selectedTeacherOpenAnswers) }}</h2>

                        <h3 class="black--text pt-5"> PREGUNTA:  {{openAnswersStudents[0] == null ? '' : openAnswersStudents[0].question}}</h3>

                        <h3 class="black--text pt-5 mt-4"> Comentarios por parte de estudiantes:</h3>

                        <div v-for="studentAnswer in openAnswersStudents" class="mt-3">

                            <h4> {{ studentAnswer.answer }}</h4>

                        </div>



                    </v-card-text>


                    <v-card-actions>

                        <v-btn
                            color="primario"
                            class="grey--text text--lighten-4"
                            @click="setDialogToCancelOpenAnswers()"
                        >
                            Salir
                        </v-btn>


                    </v-card-actions>
                </v-card>

            </v-dialog>


        </v-container>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Form from "@/models/Form";
import Snackbar from "@/Components/Snackbar";
import Chart from "chart.js/auto";
import Question from "@/models/Question";
import Papa from 'papaparse';


export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },
    data: () => {
        return {
            sheet: false,
            //Table info
            search: '',
            headers: [
                {text: 'Profesor', value: 'name'},
                {text: 'Área de Servicio', value: 'service_area_name'},
                {text: 'Promedio C1', value: 'first_competence_average'},
                {text: 'Promedio C2', value: 'second_competence_average'},
                {text: 'Promedio C3', value: 'third_competence_average'},
                {text: 'Promedio C4', value: 'fourth_competence_average'},
                {text: 'Promedio C5', value: 'fifth_competence_average'},
                {text: 'Promedio C6', value: 'sixth_competence_average'},
                {text: 'Estudiantes que evaluaron', value: 'aggregate_students_amount_reviewers'},
                {text: 'Estudiantes totales', value: 'aggregate_students_amount_on_service_area'},
                {text: 'Fecha de envío', value: 'submitted_at'},
                {text: 'Graficar Resultados', value: 'graph', sortable: false},
                {text: 'Visualizar Comentarios', value: 'op_answers', sortable: false},
            ],

            //Display data

            assessments: [],
            serviceArea: '',
            serviceAreas:[],
            teacher: '',
            teachers:[],
            selectedTeacherToGraph: '',
            dataToGraph: [],
            chart:'',
            datasets:[],
            competencesValuesAsArray:[],
            finalTeachingLadders:[],
            responseIdeals: [],
            teachingLadder:[],
            responseIdealsCompetences: [],
            responseIdealsCompetencesArray: [],
            openAnswersStudents: [],
            showOpenAnswersDialog: false,
            selectedTeacherOpenAnswers: '',

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            showChartDialog: false,
            isLoading: true,
        }
    },
    async created() {

        await this.getServiceAreas();
        await this.getTeachers();
        await this.getServiceAreasTeacherResults();
        this.isLoading = false;

    },

    methods: {

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
        },



        updateResults: function (){




        },

        matchProperty: function (array, propertyPath, reference) {

            return array.filter((item) => {

                const propertyArr = propertyPath.split(".");

                const propertyValue = propertyArr.reduce((obj, key) => {

                    return obj && obj[key];

                }, item);

                return propertyValue === reference;

            });
        },


        getServiceAreas: async function (){

            let request = await axios.get(route('api.serviceAreas.index'));

            this.serviceAreas = this.sortArrayAlphabetically(request.data);

            console.log(this.serviceAreas, 'service areas');

            this.serviceAreas.unshift({name: 'Todas las áreas de servicio', code:''})

        },


        getTeachers: async function (){

            let request = await axios.get(route('serviceAreas.teachersWithResults'));

            this.teachers = request.data

            console.log(this.teachers, 'teacherssss');

            this.teachers.forEach(teacher =>{

                teacher.name = this.capitalize(teacher.name)

            })

            this.teachers = this.sortArrayAlphabetically(this.teachers);



        },


        getServiceAreasTeacherResults: async function (){

            let url = route('serviceAreas.getResults');

            let request = await axios.get(url);

            this.assessments = request.data

            console.log(request.data, 'resultsss');

        },


        getOpenAnswersFromStudents: async function (teacherId, serviceArea){

            let url = route('formAnswers.teachers.openAnswersStudents');

            let request = await axios.post(url, {teacherId: teacherId, serviceArea:serviceArea});

            this.openAnswersStudents = request.data;


        },


        async setDialogToShowOpenAnswers(teacher){

            this.selectedTeacherOpenAnswers = teacher.name;

            this.showOpenAnswersDialog = true

            console.log(teacher, "teacherrr")

            await this.getOpenAnswersFromStudents(teacher.teacherId, teacher.service_area_code);

        },

        capitalize($field){

            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },

        sortArrayAlphabetically(array){

            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);

        },

        getFilteredAssessmentsByServiceArea(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }

            return assessments.filter((assessment) => {

                let doesAssessmentHaveServiceArea = false;
                if (assessment.service_area_code === this.serviceArea) {
                    doesAssessmentHaveServiceArea = true;
                }
                return doesAssessmentHaveServiceArea;
            });

        },

        getFilteredAssessmentsByTeacher(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }

            return this.matchProperty(assessments, 'teacherId', this.teacher)

        },


        async setDialogToShowChart(teacher){


            this.showChartDialog = true

            console.log(teacher, 'jefjewfjefj')

            await this.getResponseIdealsDataset(teacher);

            this.getRolesDatasets(teacher);

            this.getGraph();


        },

        fillCompetencesArray(roleArray) {

            let array = [roleArray.first_competence_average, roleArray.second_competence_average, roleArray.third_competence_average, roleArray.fourth_competence_average,

                roleArray.fifth_competence_average, roleArray.sixth_competence_average]


            return array;

        },

        getTeachingLadderNameByParameter: async function (teachingLadderCode){

            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));

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

            let teachingLadder = this.finalTeachingLadders.find(teachingLadder =>{

                return teachingLadder.identifier == teachingLadderCode

            })

            if (teachingLadder === undefined){

                return 'Ninguno'

            }

            return teachingLadder.name

        },

        getPossibleInitialCompetences() {
            return new Question().getPossibleCompetencesAsArrayOfObjects();
        },

        async getResponseIdealsCompetences(teachingLadder){

            let url = route('responseIdeals.get');
            let request = await axios.post(url, {teachingLadder: teachingLadder})
            if(request.data.length === 0){
                return this.getPossibleInitialCompetences();
            }
            return request.data;
        },

        async getResponseIdealsDataset(teacher){

            this.selectedTeacherToGraph = teacher.name

            let info = {userId : teacher.teacherId}

/*            let request = await axios.post(route('teachers.getTeachingLadder'), info)

            let teachingLadder= await this.getTeachingLadderNameByParameter(request.data)*/
/*

            console.log(teachingLadder, 'teachingLadder');

            let responseIdealsCompetences = await this.getResponseIdealsCompetences(teachingLadder);

            responseIdealsCompetences.forEach(competence =>{

                this.responseIdealsCompetencesArray.push(competence.value);

            })
*/
/*

            let hex = this.randomHexColor()

            this.datasets.unshift({

                label: `Ideales de respuesta (${teachingLadder == 'Ninguno' ? 'Sin Escalafón' : teachingLadder})`,
                data: this.responseIdealsCompetencesArray,
                backgroundColor: hex,
                borderColor: hex,
                borderWidth: 2,
                borderDash: [5, 5],

            })*/

        },


        getRolesDatasets(teacher){

            let teacherServiceAreaArray = this.filteredItems.find((item) => {
                return item.name == teacher.name && item.service_area_code ==teacher.service_area_code
            })


            console.log(teacherServiceAreaArray);

            let hex = this.randomHexColor()

            this.datasets.push({

                label: 'Perspectiva del estudiante',
                data: this.fillCompetencesArray(teacherServiceAreaArray),
                backgroundColor: hex,
                borderColor: hex,
                borderWidth: 2
            })

        },


        setDialogToCancelChart (){

            this.showChartDialog = false
            this.chart.destroy();
            this.responseIdealsCompetencesArray.length = 0;
            this.finalTeachingLadders.length= 0;
            this.datasets = [];

        },



        setDialogToCancelOpenAnswers (){

            this.showOpenAnswersDialog = false;
            this.openAnswersStudents= [];

        },

        downloadResults (){


            let excelInfo = this.filteredItems.map(item =>{

                return {

                    Nombre :item.name,
                    AreaDeServicio: item.service_area_name,
                    PromedioC1: item.first_competence_average,
                    PromedioC2: item.second_competence_average,
                    PromedioC3: item.third_competence_average,
                    PromedioC4: item.fourth_competence_average,
                    PromedioC5: item.fifth_competence_average,
                    PromedioC6: item.sixth_competence_average,
                    ActoresInvolucrados: item.aggregate_students_amount_reviewers,
                    ActoresTotales: item.aggregate_students_amount_on_service_area
                }

            })



            let csv = Papa.unparse(excelInfo, {delimiter:';'});

            var csvData = new Blob(["\uFEFF"+csv], {type: 'text/csv;charset=utf-8;'});
            var csvURL =  null;
            if (navigator.msSaveBlob)
            {
                csvURL = navigator.msSaveBlob(csvData, 'ResultadosEvaluaciónDocente360.csv');
            }
            else
            {
                csvURL = window.URL.createObjectURL(csvData);
            }

            var tempLink = document.createElement('a');
            tempLink.href = csvURL;
            tempLink.setAttribute('download', 'ResultadosEvaluaciónDocente360.csv');
            tempLink.click();

        },

        getGraph(){

            let miCanvas = document.getElementById("MiGrafica").getContext("2d");
            this.chart = new Chart(miCanvas, {
                type:"line",
                data:{
                    labels: ["Orientación a la calidad educativa", "Trabajo Colaborativo",
                        "Empatía Universitaria", "Comunicación", "Innovación del conocimiento","Productividad académica"],
                    datasets: this.datasets,
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
                                max:5.5,
                                title: {
                                    display: true,
                                    text: 'Valores obtenidos'
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


    computed: {

        filteredItems() {

            let finalAssessments = this.assessments;

            if (this.serviceArea !== '') {
                finalAssessments = this.getFilteredAssessmentsByServiceArea(finalAssessments);
            }
            if (this.teacher !== '') {
                finalAssessments = this.getFilteredAssessmentsByTeacher(finalAssessments);
            }

            return finalAssessments;
        },


        filteredTeachers(){

            let finalTeachers = this.teachers;

            let finalAssessments = this.assessments;

            if (this.serviceArea !== '') {

                finalAssessments = this.getFilteredAssessmentsByServiceArea();

                finalTeachers = finalTeachers.filter((teacher) => {
                    return finalAssessments.some((assessment) => assessment.teacherId == teacher.id)
                });
            }

            this.addAllElementSelectionItem(finalTeachers, 'Todos los docentes');

            return finalTeachers;




        }
    }
}
</script>
