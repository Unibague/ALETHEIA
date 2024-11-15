<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-2" id="malparido">
                <h2 class="align-self-start">Reportes por docencia general</h2>
            </div>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="4" >
                        <v-autocomplete
                            v-model="assessmentPeriod"
                            flat
                            solo-inverted
                            hide-details
                            :items="assessmentPeriods"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            :item-value="(assessmentPeriod)=> (assessmentPeriod.id)"
                            prepend-inner-icon="mdi-home-search"
                            label="Periodo de evaluación"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="3">
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

                    <v-col cols="2">
                        <v-autocomplete
                            v-model="hourType"
                            flat
                            solo-inverted
                            hide-details
                            :items="hourTypes"
                            prepend-inner-icon="mdi-account-search"
                            label="Tipo de hora"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="2">
                        <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 secundario--text"
                                    @click="downloadResults"
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
                    :no-data-text="noDataText"
                    :loading="isLoading"
                    :headers="dynamicHeaders"
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

                    <template v-slot:item.open_ended_answers="{ item }">
                        <v-tooltip top
                        >
                            <template v-slot:activator="{on,attrs}">
                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="setDialogToShowOpenEndedAnswers(item)"
                                >
                                    mdi-text-box
                                </v-icon>
                            </template>
                            <span>Visualizar comentarios</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>

            <!--Seccion de dialogos-->
            <v-dialog v-model="showChartDialog" max-width="710">
                <v-card>
                    <v-card-title class="custom-card-title" style="text-align: center">Resultado consolidado de docencia para el docente "{{this.selectedAssessment.teacher_name}}"
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="6">
                                <pie-chart
                                    ref="satisfactionPieChartComponent"
                                    :value="selectedAssessment['Satisfacción']"
                                    title="¿Qué tan satisfecho estoy con el desempeño del profesor(a)?."
                                    :max="5"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <pie-chart
                                ref="overallAverageChartComponent"
                                :value="selectedAssessment.overall_average"
                                title="Promedio general del periodo de evaluación"
                                :max="5"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            class="white--text"
                            @click="downloadPDF()"
                            v-if="serviceAreaResults"
                        >
                            Descargar reporte en PDF
                        </v-btn>
                        <v-btn color="primario" class="white--text" @click="showChartDialog = false">Cerrar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog v-model="showOpenEndedAnswersDialog">
                <v-card v-if="selectedAssessment && selectedAssessment.open_ended_answers" class="mt-4">
                    <v-card-title>Respuestas abiertas</v-card-title>
                    <v-card-text>
                        <div v-for="(serviceArea, serviceAreaIndex) in selectedAssessment.open_ended_answers" :key="serviceAreaIndex">
                            <h3 class="group-name mb-3" style="color: black"> <span style="color: black"> Área: {{ serviceArea.service_area_name }} </span></h3>
                            <div v-for="(group, groupIndex) in serviceArea.groups" :key="groupIndex" style="margin-left: 10px">
                            <h3 class="group-name mb-3" style="color: black">{{ group.group_name }}</h3>
                            <div v-for="(question, questionIndex) in group.questions" :key="questionIndex" class="mb-4">
                                <h4 class="question-text mb-2">{{ question.question }}</h4>
                                <v-expansion-panels multiple>
                                    <v-expansion-panel v-for="(commentType, typeIndex) in question.commentType" :key="typeIndex">
                                        <v-expansion-panel-header>
                                            {{ commentType.type }} ({{ commentType.answers.length }})
                                        </v-expansion-panel-header>
                                        <v-expansion-panel-content>
                                            <ul>
                                                <li v-for="(answer, answerIndex) in commentType.answers" :key="answerIndex">
                                                    {{ answer }}
                                                </li>
                                            </ul>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </div>
                        </div>
                    </div>
                    </v-card-text>
                </v-card>

            </v-dialog>
        </v-container>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-card-title {
    white-space: normal;
    word-break: keep-all;
    overflow-wrap: break-word;
    text-align: center;
}

</style>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Form from "@/models/Form";
import Snackbar from "@/Components/Snackbar";
import Papa from 'papaparse';
import PieChart from '../../Components/PieChart.vue'

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
        PieChart
    },
    data: () => {
        return {
            //Table info
            satisfactionPieChartComponent: null,
            overallAverageChartComponent: null,
            search: '',
            //Display data
            assessments: [],
            selectedAssessment:'',
            assessmentPeriod: '',
            assessmentPeriods: [],
            serviceArea: '',
            serviceAreas:[],
            teacher: '',
            selectedTeacher: '',
            hourType: '',
            hourTypes: ['normal', 'cátedra'],
            teachers:[],
            selectedTeacherOpenAnswers: '',
            finalTeachingLadders:[],
            openAnswersStudents: [],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            dynamicHeaders: [],
            //Dialogs
            showChartDialog: false,
            showOpenEndedAnswersDialog: false,
            isLoading: true,
            noDataText: 'Para comenzar, selecciona un periodo de evaluación',
            //Booleans
            groupResults: false,
            serviceAreaResults: true,
        }
    },

    async created() {
        await this.getAssessmentPeriods();
        await this.getTeachers();
        await this.getAssessments()
        this.isLoading = false;
    },

    watch:{
        async assessmentPeriod(){
            await this.getTeachers();
            await this.getAssessments()
        }
    },

    computed: {


        filteredItems() {
            let finalAssessments = this.assessments;
            if (this.teacher !== '') {
                finalAssessments = this.getFilteredAssessmentsByTeacher(finalAssessments);
            }

            if(this.hourType !== ''){
                finalAssessments = this.getFilteredAssessmentsByHourType(finalAssessments);
            }

            return finalAssessments;
        },

        filteredTeachers(){
            let finalTeachers = this.teachers;
            this.addAllElementSelectionItem(finalTeachers, 'Todos los docentes');
            return finalTeachers;
        }
    },

    methods: {

        async downloadPDF() {
            try {
                // Get references to the pie-chart components
                this.satisfactionPieChartComponent= this.$refs.satisfactionPieChartComponent;
                this.overallAverageChartComponent = this.$refs.overallAverageChartComponent;
                // Generate chart images one by one
                const chartImage1 = await this.satisfactionPieChartComponent.generateChartImage('image/jpeg', 0.9, 3);
                const chartImage2 = await this.overallAverageChartComponent.generateChartImage('image/jpeg', 0.9, 3);
                const response = await axios.post(route('reports.teaching.download'), {
                    assessment: this.selectedAssessment,
                    headers:this.dynamicHeaders,
                    overallAverageChart: chartImage1,
                    satisfactionChart: chartImage2,
                    reportType: "overallTeaching"

                }, {
                    responseType: 'blob' // This tells Axios to expect a binary response
                });
                // Create a download link and trigger the download
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'report.pdf');
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Error downloading the PDF', error);
            }
        },

        downloadResults() {
            const excelInfo = this.filteredItems.map(item => {
                const result = {};

                const notDesiredHeaders = ['Gráfico', 'Comentarios']

                this.dynamicHeaders.forEach(header => {
                    if(!notDesiredHeaders.includes(header.text))
                    {
                        result[header.text] = item[header.value]
                    }
                })
                return result;
            })

            let csv = Papa.unparse(excelInfo, {delimiter: ';'});
            var csvData = new Blob(["\uFEFF" + csv], {type: 'text/csv;charset=utf-8;'});
            var csvURL = null;
            if (navigator.msSaveBlob) {
                csvURL = navigator.msSaveBlob(csvData, 'ResultadosEvaluaciónDocente.csv');
            } else {
                csvURL = window.URL.createObjectURL(csvData);
            }
            var tempLink = document.createElement('a');
            tempLink.href = csvURL;
            tempLink.setAttribute('download', 'ResultadosEvaluaciónDocente.csv');
            tempLink.click();
        },

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
        },

        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.notLegacy'));
            this.assessmentPeriods = request.data
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

        getTeachers: async function () {
            let request = await axios.get(route('serviceAreas.teachersWithResults', {assessmentPeriodId: this.assessmentPeriod}));
            this.teachers = request.data
            this.teachers.forEach(teacher => {
                teacher.name = this.capitalize(teacher.name)
            })
        },

        async getAssessments() {
            if(this.assessmentPeriod !== ''){
                let request = await axios.post(route('reports.finalTeaching.results'), {assessmentPeriodId: this.assessmentPeriod});
                this.dynamicHeaders = request.data.headers
                this.assessments = request.data.items;
                console.log(this.assessments, 'assessments');
            }
        },

        getFilteredAssessmentsByTeacher(assessments = null) {
            if (assessments === null) {
                assessments = this.assessments;
            }
            return this.matchProperty(assessments, 'teacher_id', this.teacher)
        },

        getFilteredAssessmentsByHourType(assessments = null) {
            if (assessments === null) {
                assessments = this.assessments;
            }
            return this.matchProperty(assessments, 'hour_type', this.hourType)
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        sortArrayAlphabetically(array){
            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        setDialogToShowChart(assessment){
            this.showChartDialog = true
            console.log(assessment, 'selectedAssessment')
            this.selectedAssessment = assessment;
        },

        setDialogToShowOpenEndedAnswers(assessment){
            console.log("it is")
            this.showOpenEndedAnswersDialog= true
            this.selectedAssessment = assessment;
        },

        setDialogToCancelOpenAnswers (){
            this.showOpenAnswersDialog = false;
            this.openAnswersColleagues = [];
            this.openAnswersStudents= [];
        },

    },
}

</script>
