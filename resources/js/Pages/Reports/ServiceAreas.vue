<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-2" id="malparido">
                <h2 class="align-self-start" v-if="serviceAreaResults">Reportes por área de servicio</h2>
                <h2 class="align-self-start" v-else> Reportes por grupo </h2>
            </div>

            <v-container class="d-flex flex-column align-end mr-5">
                <v-btn
                    color="primario"
                    class="white--text"
                    @click="switchResults()"
                >
                    <span v-if="serviceAreaResults">Visualizar resultados por grupo</span>
                    <span v-else> Visualizar resultados por área de servicio</span>
                </v-btn>
            </v-container>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="3">
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

                    <v-col cols="4">
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
                    <v-card-title class="custom-card-title" style="text-align: center">Resultados para el docente
                        "{{ this.selectedAssessment.teacher_name }}"
                        <span> Área de servicio: "{{ this.selectedAssessment.service_area_name }}"</span>
                        <template v-if="groupResults">
                            <span class="optional-text">Asignatura: "{{ this.selectedAssessment.group_name }} | Grupo: {{ this.selectedAssessment.group_number }}"</span>
                        </template>
                    </v-card-title>
                    <v-card-text>
                        <v-row style="text-align: center">
                            <v-col :cols="selectedAssessment['Satisfacción'] !== undefined ? 6 : 12">
                                <pie-chart
                                    ref="overallAverageChartComponent"
                                    :value="selectedAssessment.overall_average"
                                    title="Promedio general del periodo de evaluación"
                                    :max="5"
                                />
                            </v-col>
                            <v-col cols="12" sm="6" v-if="selectedAssessment['Satisfacción'] !== undefined">
                                <pie-chart
                                    ref="satisfactionPieChartComponent"
                                    :value="selectedAssessment['Satisfacción']"
                                    title="¿Qué tan satisfecho estoy con el desempeño del profesor(a)?."
                                    :max="5"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            class="white--text mr-3"
                            @click="downloadPDF()"
                            :disabled="this.reportDownloading"
                        >
                            {{ reportDownloading ? 'Descargando reporte...' : 'Descargar reporte en PDF' }}
                        </v-btn>
                        <v-btn color="primario" class="white--text" @click="showChartDialog = false">Cerrar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog v-model="showOpenEndedAnswersDialog">
                <v-card v-if="selectedAssessment && selectedAssessment.open_ended_answers && serviceAreaResults"
                        class="mt-4">
                    <v-card-title>Respuestas abiertas</v-card-title>
                    <v-card-text>
                        <div v-for="(group, groupIndex) in selectedAssessment.open_ended_answers" :key="groupIndex">
                            <h3 class="group-name mb-3" style="color: black">{{ group.group_name }}</h3>
                            <div v-for="(question, questionIndex) in group.questions" :key="questionIndex" class="mb-4">
                                <h4 class="question-text mb-2"> {{ question.question }}</h4>
                                <v-expansion-panels multiple>
                                    <v-expansion-panel v-for="(commentType, typeIndex) in question.commentType"
                                                       :key="typeIndex">
                                        <v-expansion-panel-header>
                                            {{ commentType.type }} ({{ commentType.answers.length }})
                                        </v-expansion-panel-header>
                                        <v-expansion-panel-content>
                                            <ul>
                                                <li v-for="(answer, answerIndex) in commentType.answers"
                                                    :key="answerIndex">
                                                    {{ answer }}
                                                </li>
                                            </ul>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>

                <v-card v-if="selectedAssessment && selectedAssessment.open_ended_answers && groupResults" class="mt-4">
                    <v-card-title>Respuestas abiertas</v-card-title>
                    <v-card-text>
                        <h3 class="group-name mb-3" v-if="groupResults"> {{ selectedAssessment.group_name }} | Grupo:
                            {{ selectedAssessment.group_number }}</h3>


                        <div v-if="selectedAssessment.open_ended_answers.length === 0" style="color: black"> Este grupo no posee preguntas abiertas </div>
                        <div v-else v-for="(question, questionIndex) in selectedAssessment.open_ended_answers"
                             :key="questionIndex" class="mb-4">
                            <h3 class="group-name mb-3">{{ question.question }}</h3>
                            <v-expansion-panels multiple>
                                <v-expansion-panel v-for="(commentType, typeIndex) in question.commentType"
                                                   :key="typeIndex">
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

.optional-text {
    display: inline;
}
</style>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
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
            selectedAssessment: '',
            assessmentPeriod: '',
            assessmentPeriods: [],
            serviceArea: '',
            serviceAreas: [],
            teacher: '',
            selectedTeacher: '',
            teachers: [],
            selectedTeacherOpenAnswers: '',
            finalTeachingLadders: [],
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
            groupResults: true,
            serviceAreaResults: false,
            reportDownloading: false,
        }
    },

    props: {
      serviceAreasFromProps: Array,
      token: String
    },

    async created() {
        console.log(this.serviceAreasFromProps, "These are the service areas")
        await this.getAssessmentPeriods();
        await this.getServiceAreas();
        await this.getTeachers();
        await this.getAssessments()
        this.isLoading = false;
    },

    watch: {
        async assessmentPeriod() {
            await this.getServiceAreas();
            await this.getTeachers();
            await this.getAssessments();
        }
    },

    computed: {

        filteredItems() {
            let finalAssessments = this.assessments;
            if (this.serviceArea !== '') {
                finalAssessments = this.getFilteredAssessmentsByServiceArea(finalAssessments);
            }
            if (this.teacher !== '') {
                console.log(this.teacher, 'Teacher seleccionado');
                finalAssessments = this.getFilteredAssessmentsByTeacher(finalAssessments);
            }
            return finalAssessments;
        },

        filteredTeachers() {
            let finalTeachers = this.teachers;
            let finalAssessments = this.assessments;
            if (this.serviceArea !== '') {
                finalAssessments = this.getFilteredAssessmentsByServiceArea();
                finalTeachers = finalTeachers.filter((teacher) => {
                    return finalAssessments.some((assessment) => assessment.teacher_id == teacher.id)
                });
            }
            this.addAllElementSelectionItem(finalTeachers, 'Todos los docentes');
            return finalTeachers;
        }
    },

    methods: {

        async downloadPDF() {
            try {
                this.reportDownloading = true;
                // Get references to the pie-chart components
                this.overallAverageChartComponent = this.$refs.overallAverageChartComponent;
                const overallAverageImage = await this.overallAverageChartComponent.generateChartImage('image/jpeg', 0.9, 3);


                let satisfactionChartImage = null

                if(this.$refs.satisfactionPieChartComponent !== undefined){
                    this.satisfactionPieChartComponent = this.$refs.satisfactionPieChartComponent;
                    satisfactionChartImage = await this.satisfactionPieChartComponent.generateChartImage('image/jpeg', 0.9, 3);
                }

                let reportType = null
                if (this.groupResults) {
                    reportType = 'group'
                } else {
                    reportType = 'serviceArea'
                }
                const response = await axios.post(route('reports.teaching.download'), {
                    assessment: this.selectedAssessment,
                    headers: this.dynamicHeaders,
                    overallAverageChart: overallAverageImage,
                    satisfactionChart: satisfactionChartImage,
                    reportType: reportType
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

            this.reportDownloading = false;
        },

        switchResults() {
            if (this.serviceAreaResults) {
                this.serviceAreaResults = false
                this.groupResults = true;
                this.getAssessments();
            } else {
                this.serviceAreaResults = true
                this.groupResults = false;
                this.getAssessments();
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
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
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

        getServiceAreas: async function () {

            if (this.serviceAreasFromProps === undefined){
                let request = await axios.get(route('api.serviceAreas.index', {assessmentPeriodId: this.assessmentPeriod}));
                this.serviceAreas = this.sortArrayAlphabetically(request.data);
                console.log(this.serviceAreas, 'service areas');
                this.serviceAreas.unshift({name: 'Todas las áreas de servicio', code: ''})
                return;
            }

            this.serviceAreas = this.serviceAreasFromProps;
        },

        getTeachers: async function () {
            let request = await axios.get(route('serviceAreas.teachersWithResults', {assessmentPeriodId: this.assessmentPeriod}));
            this.teachers = request.data
            console.log(this.teachers, 'Teachers to be selected')
            this.teachers.forEach(teacher => {
                teacher.name = this.capitalize(teacher.name)
            })
        },

        async getAssessments() {
            if(this.assessmentPeriod !== ''){
                let data = {serviceAreas: this.serviceAreas, assessmentPeriodId: this.assessmentPeriod};
                if (this.serviceAreaResults) {
                    let request = await axios.post(route('reports.serviceArea.results'), data);
                    this.dynamicHeaders = request.data.headers
                    this.assessments = request.data.items;
                } else {
                    let request = await axios.post(route('reports.group.results'), data);
                    this.dynamicHeaders = request.data.headers
                    this.assessments = request.data.items;
                }
            }
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
            return this.matchProperty(assessments, 'teacher_id', this.teacher)
        },

        capitalize($field) {
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        sortArrayAlphabetically(array) {
            return array.sort((p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        setDialogToShowChart(assessment) {
            this.showChartDialog = true
            console.log(assessment, 'selectedAssessment')
            this.selectedAssessment = assessment;
        },

        setDialogToShowOpenEndedAnswers(assessment) {
            this.showOpenEndedAnswersDialog = true
            this.selectedAssessment = assessment;
        },

        setDialogToCancelOpenAnswers() {
            this.showOpenAnswersDialog = false;
            this.openAnswersColleagues = [];
            this.openAnswersStudents = [];
        },

    },
}
</script>
