<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-2" id="malparido">
                <h2 class="align-self-start">Reportes por facultad</h2>
            </div>


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
                            v-model="faculty"
                            flat
                            solo-inverted
                            hide-details
                            :items="faculties"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="id"
                            prepend-inner-icon="mdi-home-search"
                            label="Facultades"
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

<!--                    <template v-slot:item.graph="{ item }">-->
<!--                        <v-tooltip top-->
<!--                        >-->
<!--                            <template v-slot:activator="{on,attrs}">-->
<!--                                <v-icon-->
<!--                                    v-bind="attrs"-->
<!--                                    v-on="on"-->
<!--                                    class="mr-2 primario&#45;&#45;text"-->
<!--                                    @click="setDialogToShowChart(item)"-->
<!--                                >-->
<!--                                    mdi-chart-line-->
<!--                                </v-icon>-->
<!--                            </template>-->
<!--                            <span>Graficar resultados</span>-->
<!--                        </v-tooltip>-->
<!--                    </template>-->

                    <!--                    <template v-slot:item.open_ended_answers="{ item }">-->
                    <!--                        <v-tooltip top-->
                    <!--                        >-->
                    <!--                            <template v-slot:activator="{on,attrs}">-->
                    <!--                                <v-icon-->
                    <!--                                    v-bind="attrs"-->
                    <!--                                    v-on="on"-->
                    <!--                                    class="mr-2 primario&#45;&#45;text"-->
                    <!--                                    @click="setDialogToShowOpenEndedAnswers(item)"-->
                    <!--                                >-->
                    <!--                                    mdi-text-box-->
                    <!--                                </v-icon>-->
                    <!--                            </template>-->
                    <!--                            <span>Visualizar comentarios</span>-->
                    <!--                        </v-tooltip>-->
                    <!--                    </template>-->
                </v-data-table>
            </v-card>

            <!--Seccion de dialogos-->
            <v-dialog v-model="showChartDialog" max-width="710">
                <v-card>
                    <v-card-title class="custom-card-title" style="text-align: center">Resultados para:
                        {{ this.selectedFaculty.faculty_name }}
                    </v-card-title>
                    <v-card-text>
                        <v-row style="text-align: center">
                            <v-col :cols="selectedFaculty['Satisfacción'] !== undefined ? 6 : 12">
                                <pie-chart
                                    ref="overallAverageChartComponent"
                                    :value="selectedFaculty.overall_average"
                                    title="Promedio general del periodo de evaluación"
                                    :max="5"
                                />
                            </v-col>
                            <v-col cols="12" sm="6" v-if="selectedFaculty['Satisfacción'] !== undefined">
                                <pie-chart
                                    ref="satisfactionPieChartComponent"
                                    :value="selectedFaculty['Satisfacción']"
                                    title="Satisfacción percibida"
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
            faculty: '',
            faculties: [],
            serviceArea: '',
            serviceAreas: [],
            teacher: '',
            selectedFaculty: '',
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

    // props: {
    //   serviceAreasFromProps: Array,
    //   token: String
    // },

    async created() {
        await this.getAssessmentPeriods();
        await this.getFaculties();
        await this.getAssessments()
        this.isLoading = false;
    },

    watch: {
        async assessmentPeriod() {
            await this.getFaculties();
            await this.getAssessments();
        }
    },

    computed: {

        filteredItems() {
            let finalAssessments = this.assessments;

            if (this.faculty !== '') {
                finalAssessments = this.getFilteredAssessmentsByFaculty(finalAssessments);
            }

            return finalAssessments;
        },
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

                const response = await axios.post(route('reports.faculty.download'), {
                    faculty: this.selectedFaculty,
                    headers: this.dynamicHeaders,
                    overallAverageChart: overallAverageImage,
                    satisfactionChart: satisfactionChartImage,
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

        downloadResults() {
            const excelInfo = this.filteredItems.map(item => {
                const result = {};

                const notDesiredHeaders = ['Gráfico', 'Comentarios']

                this.dynamicHeaders.forEach(header => {
                    if (!notDesiredHeaders.includes(header.text)) {
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

        getFaculties: async function () {
            let request = await axios.get(route('api.faculties.index'))
            this.faculties = request.data;
            this.faculties.unshift({name: 'Todas las facultades', id: ''})
            console.log(this.faculties);
        },

        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.notLegacy'));
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

        async getAssessments() {
            if (this.assessmentPeriod !== '') {
                let data = {assessmentPeriodId: this.assessmentPeriod};
                let request = await axios.post(route('reports.faculty.results'), data);
                this.dynamicHeaders = request.data.headers
                this.assessments = request.data.items;
            }

            console.log(this.assessments);

        },

        getFilteredAssessmentsByFaculty(assessments = null) {
            if (assessments === null) {
                assessments = this.assessments;
            }
            return this.matchProperty(assessments, 'faculty_id', this.faculty)
        },

        capitalize($field) {
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        sortArrayAlphabetically(array) {
            return array.sort((p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        setDialogToShowChart(faculty) {
            this.showChartDialog = true
            this.selectedFaculty = faculty;
        },


    },
}
</script>
