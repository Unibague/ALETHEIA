<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>


        <!--Inicia tabla resultados por áreas de servicio-->
        <v-container fluid v-if="!resultsPerGroup">
            <div class="d-flex flex-column align-end mb-5">
                <h2 class="align-self-start">Gestionar respuestas de evaluación por área de servicio</h2>
            </div>

            <v-container class="d-flex flex-column align-end mr-5">

                    <v-btn
                        color="primario"
                        class="white--text"
                        @click="activateResultsPerGroup"
                    >
                        Visualizar resultados por grupo
                    </v-btn>


            </v-container>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="6" >
                        <v-autocomplete
                            v-model="serviceArea"
                            flat
                            solo-inverted
                            hide-details
                            :items="serviceAreas"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="code"
                            prepend-inner-icon="mdi-home-search"
                            label="Áreas de Servicio"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="5">
                        <v-autocomplete
                            v-model="teacher"
                            flat
                            solo-inverted
                            hide-details
                            :items="filteredTeachers"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="id"
                            prepend-inner-icon="mdi-account-search"
                            label="Docente"
                        ></v-autocomplete>
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
                    :headers="serviceAreasViewHeaders"
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

            <!--Mostrar gráfica con resultados del docente-->

            <v-dialog
                v-model="showChartDialog"
                persistent
            >
                <v-card>
                    <v-card-text class="py-3">

                        <h2 class="black--text ma-2" style="text-align: center"> Visualizando al docente: {{this.capitalize(this.selectedTeacherToGraph)}}</h2>

                    </v-card-text>

                    <v-container style="position: relative; height:60vh; width:90vw; background: #FAF9F6">
                        <canvas id="MiGrafica"></canvas>
                    </v-container>

                    <v-card-actions>

                        <v-btn
                            color="primario"
                            class="white--text"
                            @click="confirmSavePDF = true"
                            :disabled="!teacher"
                        >
                            Descargar reporte en PDF
                        </v-btn>

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

        <!--Inicia tabla resultados individuales por grupo-->
        <v-container fluid v-if="resultsPerGroup">
            <div class="d-flex flex-column align-end mb-5">
                <h2 class="align-self-start">Gestionar respuestas de evaluación por grupo</h2>
            </div>

            <v-container class="d-flex flex-column align-end mr-5">

                    <v-btn
                        color="primario"
                        class="white--text"
                        @click="activateResultsPerServiceAreas"

                    >
                        Visualizar resultados por Área de servicio
                    </v-btn>

            </v-container>
            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="6" >
                        <v-autocomplete
                            v-model="serviceArea"
                            flat
                            solo-inverted
                            hide-details
                            :items="serviceAreas"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="code"
                            prepend-inner-icon="mdi-home-search"
                            label="Áreas de Servicio"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="5">
                        <v-autocomplete
                            v-model="teacher"
                            flat
                            solo-inverted
                            hide-details
                            :items="filteredTeachers"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="id"
                            prepend-inner-icon="mdi-account-search"
                            label="Docente"
                        ></v-autocomplete>
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
                    :headers="serviceAreaGroupsViewHeaders"
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

                        <v-tooltip top>
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

            <v-dialog
                v-model="showChartDialog"
                persistent
            >

                <v-card class="py-2">

                    <v-card-text>

                        <h2 class="black--text ma-2" style="text-align: center"> Visualizando al docente: {{this.capitalize(this.selectedTeacherToGraph)}}</h2>

                    </v-card-text>


                    <v-container style="position: relative; height:60vh; width:90vw; background: #FAF9F6">
                        <canvas id="MiGrafica"></canvas>
                    </v-container>

                    <h5 class="gray--text" style="text-align: left; margin-left: 60px; margin-bottom: 5px"> Puedes dar click izquierdo sobre la leyenda de la línea de tendencia que desees ocultar </h5>

                    <v-card-actions>


                        <v-btn
                            color="primario"
                            class="white--text"
                            @click="confirmSavePDF = true"
                            :disabled="!teacher && !serviceArea"
                        >
                            Descargar reporte en PDF
                        </v-btn>


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
                    <v-card-text v-if="openAnswersStudents.length >0">
                        <h2 class="black--text pt-5" style="text-align: center"> Visualizando comentarios hacia el docente:
                            {{ this.capitalize(this.selectedTeacherOpenAnswers)}}</h2>

                        <h2 class="black--text pt-3 pb-4" style="text-align: center">{{this.selectedGroupNameOpenAnswers}} - Grupo: {{this.selectedGroupNumberOpenAnswers}}</h2>

                        <h3 class="black--text pt-5"> PREGUNTA:  {{openAnswersStudents[0] == null ? '' : openAnswersStudents[0].question}}</h3>

                        <h3 class="black--text pt-5 mt-4"> Comentarios por parte de estudiantes:</h3>

                        <div v-for="studentAnswer in openAnswersStudents" class="mt-3">

                            <h4> {{ studentAnswer.answer }}</h4>

                        </div>

                    </v-card-text>


                    <v-card-text v-else>

                        <h2 class="black--text pt-5" style="text-align: center"> No hay comentarios disponibles para el grupo de este docente</h2>

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


        <confirm-dialog
            :show="confirmSavePDF"
            @canceled-dialog="confirmSavePDF = false"
            @confirmed-dialog="savePDF()"

        >
            <template v-slot:title>
                Ahora serás redirigido a la pantalla para guardar el PDF
            </template>

            Una vez allí, lo único que debes hacer es darle click al botón de <strong class="black--text"> Guardar </strong> en la parte inferior derecha de tu pantalla y tendrás el archivo

            <template v-slot:confirm-button-text>
                Descargar PDF
            </template>
        </confirm-dialog>

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
            serviceAreasViewHeaders: [
                {text: 'Profesor', value: 'name'},
                {text: 'Área de Servicio', value: 'service_area_name'},
                {text: 'Orientación a la calidad educativa', value: 'first_competence_average'},
                {text: 'Trabajo Colaborativo', value: 'second_competence_average'},
                {text: 'Empatía Universitaria', value: 'third_competence_average'},
                {text: 'Comunicación', value: 'fourth_competence_average'},
                {text: 'Innovación del conocimiento', value: 'fifth_competence_average'},
                {text: 'Productividad académica', value: 'sixth_competence_average'},
                {text: 'Estudiantes que evaluaron', value: 'aggregate_students_amount_reviewers'},
                {text: 'Estudiantes totales', value: 'aggregate_students_amount_on_service_area'},
                {text: 'Fecha de envío', value: 'submitted_at'},
                {text: 'Graficar Resultados', value: 'graph', sortable: false},
                {text: 'Visualizar Comentarios', value: 'op_answers', sortable: false},
            ],


            serviceAreaGroupsViewHeaders: [
                {text: 'Profesor', value: 'name'},
                {text: 'Asignatura', value: 'group_name'},
                {text: 'Grupo', value: 'group_number'},
                {text: 'Área de Servicio', value: 'service_area_name'},
                {text: 'Orientación a la calidad educativa', value: 'first_competence_average'},
                {text: 'Trabajo Colaborativo', value: 'second_competence_average'},
                {text: 'Empatía Universitaria', value: 'third_competence_average'},
                {text: 'Comunicación', value: 'fourth_competence_average'},
                {text: 'Innovación del conocimiento', value: 'fifth_competence_average'},
                {text: 'Productividad académica', value: 'sixth_competence_average'},
                {text: 'Estudiantes que evaluaron', value: 'students_amount_reviewers'},
                {text: 'Estudiantes totales', value: 'students_amount_on_group'},
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
            selectedGroupNumberOpenAnswers: '',
            selectedGroupNameOpenAnswers: '',
            individualView: false,
            resultsPerGroup: false,
            confirmSavePDF: false,
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

    props: {
        propsServiceAreas: Array,
        token: String
    },


    async created() {

        await this.checkUser();
        await this.getServiceAreas();
        await this.getTeachers();
        await this.getServiceAreasTeacherResults();
        this.isLoading = false;

    },

    methods: {

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
        },

        checkUser: function (){

            if (this.propsServiceAreas !== undefined){

                this.individualView = true;
            }

        },

        activateResultsPerGroup(){

            this.resultsPerGroup = true
            this.isLoading = true
            this.getServiceAreaGroupsTeacherResults();

        },

        activateResultsPerServiceAreas(){

            this.resultsPerGroup = false
            this.isLoading = true
            this.getServiceAreasTeacherResults();

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

            if (this.individualView){

                this.serviceAreas = this.serviceAreas.filter (serviceArea => {

                    return this.propsServiceAreas.includes(serviceArea.code)

                })
                return this.serviceAreas;
            }

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

            this.isLoading = false;

            console.log(request.data, 'resultsss');

        },


        getServiceAreaGroupsTeacherResults: async function (){

            let url = route('serviceAreas.getResultsPerGroup');

            let request = await axios.get(url);

            this.assessments = request.data

            this.isLoading = false

            console.log(request.data, 'resultsss');

        },


        getOpenAnswersFromStudents: async function (teacherId, serviceArea){

            let url = route('formAnswers.teachers.openAnswersStudents');

            let request = await axios.post(url, {teacherId: teacherId, serviceArea:serviceArea});

            this.openAnswersStudents = request.data;

        },

        getOpenAnswersFromStudentsFromGroup: async function (teacherId, serviceArea, groupId){

            let url = route('formAnswers.teachers.openAnswersStudentsFromGroup');

            let request = await axios.post(url, {teacherId: teacherId, serviceArea:serviceArea, groupId: groupId});

            console.log(request.data);

            this.openAnswersStudents = request.data;


        },


        async setDialogToShowOpenAnswers(teacher){

            this.selectedTeacherOpenAnswers = teacher.name;

            this.showOpenAnswersDialog = true

            console.log(teacher, "teacherrr")

            if(this.resultsPerGroup == false){

                await this.getOpenAnswersFromStudents(teacher.teacherId, teacher.service_area_code);
            }

            else{

                this.selectedTeacherOpenAnswers = teacher.name;

                this.showOpenAnswersDialog = true

                this.selectedGroupNumberOpenAnswers = teacher.group_number;

                this.selectedGroupNameOpenAnswers = teacher.group_name

                await this.getOpenAnswersFromStudentsFromGroup(teacher.teacherId, teacher.service_area_code, teacher.group_id)

            }


        },


        async setDialogToShowOpenAnswersFromGroups(teacher){

            this.selectedTeacherOpenAnswers = teacher.name;

            this.showOpenAnswersDialog = true

            this.selectedGroupNumberOpenAnswers = teacher.group_number;

            this.selectedGroupNameOpenAnswers = teacher.group_name

            console.log(teacher, "teacherrr")

            await this.getOpenAnswersFromStudentsFromGroup(teacher.teacherId, teacher.service_area_code, teacher.group_id);

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

            this.getServiceAreasDatasets(teacher);

            this.getGraph();


        },

        fillCompetencesArray(roleArray) {

            let array = [roleArray.first_competence_average, roleArray.second_competence_average, roleArray.third_competence_average,
                roleArray.fourth_competence_average, roleArray.fifth_competence_average, roleArray.sixth_competence_average]

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

            let url = route('teacher.responseIdeals.get');
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


        getServiceAreasDatasets(teacher){

            let teacherServiceAreaArray = this.filteredItems.filter((item) => {
                return item.name == teacher.name
            })

            let colorsArray = ['blue', 'red', 'green', 'purple', 'black', 'orange']

            console.log(teacherServiceAreaArray, 'array of serviceareas');

            if (this.resultsPerGroup == false){

                teacherServiceAreaArray.forEach((serviceArea, index) => {

                    this.datasets.push({
                        label: this.capitalize(serviceArea.service_area_name),
                        data: this.fillCompetencesArray(serviceArea),
                        backgroundColor: colorsArray[index],
                        borderColor: colorsArray[index],
                        borderWidth: 3
                    })

                })

            }

            else{

                teacherServiceAreaArray.forEach((serviceArea, index) => {

                    this.datasets.push({
                        label: `P.A: ${serviceArea.academic_period_name} - ${this.capitalize(serviceArea.group_name)} - Gr. ${serviceArea.group_number}`,
                        data: this.fillCompetencesArray(serviceArea),
                        backgroundColor: colorsArray[index],
                        borderColor: colorsArray[index],
                        borderWidth: 3
                    })

                })

            }



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

            let excelInfo = [];

            if(this.resultsPerGroup == false){

                excelInfo = this.filteredItems.map(item =>{

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
            }


            else{

                excelInfo = this.filteredItems.map(item =>{

                    return {
                        Nombre :item.name,
                        Asignatura: item.group_name,
                        Grupo: item.group_number,
                        AreaDeServicio: item.service_area_name,
                        PromedioC1: item.first_competence_average,
                        PromedioC2: item.second_competence_average,
                        PromedioC3: item.third_competence_average,
                        PromedioC4: item.fourth_competence_average,
                        PromedioC5: item.fifth_competence_average,
                        PromedioC6: item.sixth_competence_average
                    }

                })

            }

            let csv = Papa.unparse(excelInfo, {delimiter:';'});

            var csvData = new Blob(["\uFEFF"+csv], {type: 'text/csv;charset=utf-8;'});
            var csvURL =  null;
            if (navigator.msSaveBlob)
            {
                csvURL = navigator.msSaveBlob(csvData, 'ResultadosEvaluaciónDocente.csv');
            }
            else
            {
                csvURL = window.URL.createObjectURL(csvData);
            }

            var tempLink = document.createElement('a');
            tempLink.href = csvURL;
            tempLink.setAttribute('download', 'ResultadosEvaluaciónDocente.csv');
            tempLink.click();

        },


        async savePDF(){


            this.confirmSavePDF = false;

            this.datasets.forEach(dataset =>{

                dataset.fill = {target: 'origin',
                    above: 'rgb(255, 255, 255)',
                    below: 'rgb(255, 255, 255)'}
            })

            var winName='MyWindow';
            var winURL= route('reports.serviceAreas.savePDF');
            var windowOption='resizable=yes,height=600,width=800,location=0,menubar=0,scrollbars=1';
            var params = { _token: this.token,
                chart: JSON.stringify(this.getChartAsObject()),
                teacherResults: JSON.stringify(this.filteredItems)
            };

            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", winURL);
            form.setAttribute("target",winName);
            for (var i in params) {
                if (params.hasOwnProperty(i)) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = i;
                    input.value = params[i];
                    form.appendChild(input);
                }
            }
            document.body.appendChild(form);
            window.open('', winName, windowOption);
            form.target = winName;
            form.submit();
            document.body.removeChild(form);


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
                        display: true,
                        position: 'bottom'
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
                                    text: 'Competencias',
                                    color: 'black',
                                    font: {
                                        size: 15,
                                        weight: 'bold',
                                        lineHeight: 1.2,
                                    },
                                },
                                position:"top",

                            }
                        ,
                        y:
                            {
                                min: 0,
                                max:5.4,
                                title: {
                                    display: true,
                                    text: 'Valores obtenidos',
                                    color: 'black',
                                    font: {
                                        size: 15,
                                        weight: 'bold',
                                        lineHeight: 1.2,
                                    },
                                },

                                ticks:{

                                    callback: (value, index, values) => (index == (values.length-1)) ? undefined : value,

                                },
                            }
                    }
                }
            })

        },


        getChartAsObject(){

            return this.chart.config._config;
        },

    },


    computed: {

        filteredItems() {

            let finalAssessments = this.assessments;

            if (this.individualView){
                finalAssessments = this.getFilteredAssessmentsByServiceArea(finalAssessments);
            }

            else {

                if (this.serviceArea !== '') {
                    finalAssessments = this.getFilteredAssessmentsByServiceArea(finalAssessments);
                }

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
