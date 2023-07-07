<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-5">
                <h2 class="align-self-start">Gestionar respuestas de evaluación 360</h2>
            </div>

            <v-container class="d-flex flex-column align-end mr-5">

<!--                <v-btn
                    color="primario"
                    class="grey&#45;&#45;text text&#45;&#45;lighten-4"
                    @click="savePDFFile()"
                >
                    Actualizar resultados
                </v-btn>-->

            </v-container>

            <v-toolbar
                    dark
                    color="primario"
                    class="mb-1"
                    height="auto"
                >
                    <v-row class="py-3">
                        <v-col cols="4" >
                            <v-select
                                v-model="unit"
                                flat
                                solo-inverted
                                hide-details
                                :items="units"
                                :item-text="(pStatus)=> capitalize(pStatus.name)"
                                item-value="identifier"
                                prepend-inner-icon="mdi-home-search"
                                label="Unidades"
                            ></v-select>
                        </v-col>

                        <v-col cols="4">
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


                        <v-col cols="3">
                            <v-select
                                v-model="role"
                                flat
                                solo-inverted
                                hide-details
                                :items="roles"
                                :item-text="(pStatus)=> capitalize(pStatus.id)"
                                item-value="name"
                                prepend-inner-icon="mdi-eye-settings"
                                label="Rol"
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

                    <template v-slot:item.actions="{ item }">

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

<!--                        <v-btn
                            color="purple"
                            class="white&#45;&#45;text"
                            @click="savePDFFile()"
                        >
                            Descargar reporte en PDF
                        </v-btn>-->

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
                {text: 'Rol', value: 'unit_role'},
                {text: 'Promedio C1', value: 'first_competence_average'},
                {text: 'Promedio C2', value: 'second_competence_average'},
                {text: 'Promedio C3', value: 'third_competence_average'},
                {text: 'Promedio C4', value: 'fourth_competence_average'},
                {text: 'Promedio C5', value: 'fifth_competence_average'},
                {text: 'Promedio C6', value: 'sixth_competence_average'},
                {text: 'Actores involucrados', value: 'aggregate_students_amount_reviewers'},
                {text: 'Actores totales', value: 'aggregate_students_amount_on_360_groups'},
                {text: 'Fecha de envío', value: 'submitted_at'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],

            //Display data

            deletedFormId: 0,
            assessments: [],
            unit: '',
            units:[],
            teacher: '',
            teachers:[],
            selectedTeacherToGraph: '',
            role:'',
            roles: [],
            dataToGraph: [],
            chart:'',
            datasets:[],
            competencesValuesAsArray:[],
            finalTeachingLadders:[],
            responseIdeals: [],
            teachingLadder:[],
            responseIdealsCompetences: [],
            responseIdealsCompetencesArray: [],
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

        await this.getRoles();
        await this.getUnits();
        await this.getTeachers();
        await this.getAnswersFromTeachers();
        await this.getAnswersFromStudents();
        this.isLoading = false;

    },

    methods: {

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
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


        getUnits: async function (){

            let request = await axios.get(route('api.units.index'));
            this.units = this.sortArrayAlphabetically(request.data);
            this.units = this.units.filter(unit => {

                return unit.teachers_from_unit.length>0 || unit.is_custom == 1;

            })
            this.units.unshift({name: 'Todas las unidades', identifier:''})

        },


        getTeachers: async function (){

            let request = await axios.get(route('unit.getTeachersThatBelongToAnUnit'));

            this.teachers = request.data

            this.teachers.forEach(teacher =>{

                teacher.name = this.capitalize(teacher.name)

            })

            this.teachers = this.sortArrayAlphabetically(this.teachers);

            console.log(this.teachers, 'teacherssss');

        },


        getRoles (){

            this.roles = [{id:  'Todos los roles', name: ''},{id: 'jefe', name: 'jefe'},
                {id: 'par', name: 'par'}, {id: 'autoevaluación', name: 'autoevaluación'}, {id: 'estudiante', name: 'estudiante'}]

            console.log(this.roles);


        },

        getAnswersFromTeachers: async function (){

            let url = route('formAnswers.teachers.show');

            let request = await axios.get(url);

            this.assessments = request.data

            console.log(request.data);

            this.assessments.forEach(assessment =>{
                assessment.first_competence_average = assessment.first_competence_average.toFixed(1);
                assessment.aggregate_students_amount_reviewers = 1;
                assessment.aggregate_students_amount_on_360_groups = 1;
            })


            console.log(this.assessments, 'assessments totales')

        },


        getAnswersFromStudents: async function () {

            let url = route('formAnswers.teachers.studentPerspective');

            let request = await axios.get(url);

            let answersFromStudents = request.data;

            console.log(answersFromStudents, 'answers from students');

           answersFromStudents.forEach(answer =>{

                answer.unit_role = 'estudiante'
                this.assessments.push(answer)

            });

           this.assessments.sort(this.orderData);



        },


        capitalize($field){

            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },


        sortArrayAlphabetically(array){

            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);

        },

        getFilteredAssessmentsByUnit(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }

            return assessments.filter((assessment) => {

                let doesAssessmentHaveUnit = false;
                if (assessment.unit_identifier === this.unit) {
                    doesAssessmentHaveUnit = true;
                }
                return doesAssessmentHaveUnit;
            });

        },

        getFilteredAssessmentsByTeacher(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }

            return this.matchProperty(assessments, 'teacherId', this.teacher)

        },

        getFilteredAssessmentsByRole(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }

            return this.matchProperty(assessments, 'unit_role', this.role)

        },


        async setDialogToShowChart(teacher){


            this.showChartDialog = true

            console.log(teacher, 'jefjewfjefj')

            await this.getResponseIdealsDataset(teacher);

            this.getRolesDatasets(teacher);

            this.getGraph();

            console.log(this.chart);


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

            let teacherId = teacher.teacherId;

            let info = {userId : teacherId}

            let request = await axios.post(route('teachers.getTeachingLadder'), info)

            let teachingLadder= await this.getTeachingLadderNameByParameter(request.data)

            console.log(teachingLadder, 'teachingLadder');

            let responseIdealsCompetences = await this.getResponseIdealsCompetences(teachingLadder);

            responseIdealsCompetences.forEach(competence =>{

                this.responseIdealsCompetencesArray.push(competence.value);

            })

            let hex = this.randomHexColor()

            this.datasets.unshift({

                label: `Ideales de respuesta (${teachingLadder == 'Ninguno' ? 'Sin Escalafón' : teachingLadder})`,
                data: this.responseIdealsCompetencesArray,
                backgroundColor: hex,
                borderColor: hex,
                borderWidth: 2,
                borderDash: [5, 5],

            })

        },


        getRolesDatasets(teacher){

            let teacherRolesArrays = this.filteredItems.filter((item) => {
                return item.name == teacher.name
            })

            teacherRolesArrays.forEach(roleArray => {

                let hex = this.randomHexColor()

                this.datasets.push({

                    label: this.capitalize(roleArray.unit_role),
                    data: this.fillCompetencesArray(roleArray),
                    backgroundColor: hex,
                    borderColor: hex,
                    borderWidth: 2
                })

            })

        },

        orderData(a,b){

            if ( a.name < b.name ){
                return -1;
            }
            if ( a.name > b.name ){
                return 1;
            }
            return 0;

        },


        setDialogToCancelChart (){

            this.showChartDialog = false
            this.chart.destroy();
            this.responseIdealsCompetencesArray.length = 0;
            this.finalTeachingLadders.length= 0;
            this.datasets = [];


        },


        downloadResults (){


                console.log(this.filteredItems);

                let excelInfo = this.filteredItems.map(item =>{

                    return {

                        Nombre :item.name,
                        rol: item.unit_role,
                        NombreUnidad: item.unitName,
                        PromedioC1: item.first_competence_average,
                        PromedioC2: item.second_competence_average,
                        PromedioC3: item.third_competence_average,
                        PromedioC4: item.fourth_competence_average,
                        PromedioC5: item.fifth_competence_average,
                        PromedioC6: item.sixth_competence_average,
                        ActoresInvolucrados: item.aggregate_students_amount_reviewers,
                        ActoresTotales: item.aggregate_students_amount_on_360_groups
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


        async savePDFFile(){

            let parameterChart = this.getChartAsObject().config._config;

            await axios.post(route('reports.index.downloadPdf'),
                {myChart: parameterChart});

    /*        await axios.post(route('reports.index.downloadPdf'),
                { myChart: parameterChart },
                { responseType: 'blob'})
                .then(res => {
                    let blob = new Blob([res.data], { type: res.headers['content-type'] });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    link.download = item.slice(item.lastIndexOf('/')+1);
                    link.click()
                }).catch(err => {})
*/


/*            await axios.get(route('reports.index.downloadPdf', {chartInfo:parameterChart}));*/



        },

        getGraph(){

                let miCanvas = document.getElementById("MiGrafica").getContext("2d");
                this.chart = new Chart(miCanvas, {
                    type:"line",
                    data:{
                        labels: ["C1", "C2", "C3", "C4", "C5","C6"],
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

        getChartAsObject(){

            return this.chart

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

            if (this.unit !== '') {
                finalAssessments = this.getFilteredAssessmentsByUnit(finalAssessments);
            }
            if (this.teacher !== '') {
                finalAssessments = this.getFilteredAssessmentsByTeacher(finalAssessments);
            }
            if (this.role !== '') {
                finalAssessments = this.getFilteredAssessmentsByRole(finalAssessments);
            }

            return finalAssessments;
        },


        filteredTeachers(){

            let finalTeachers = this.teachers;

            let finalAssessments = this.assessments;

           if (this.unit !== '') {
                finalAssessments = this.getFilteredAssessmentsByUnit();

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
