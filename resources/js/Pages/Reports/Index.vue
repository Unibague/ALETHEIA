<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-2" id="malparido">
                <h2 class="align-self-start">Respuestas por grupo</h2>
            </div>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="3" >
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

                    <v-col cols="3" >
                        <v-autocomplete
                            v-model="unit"
                            flat
                            solo-inverted
                            hide-details
                            :items="units"
                            :item-text="(pStatus)=> capitalize(pStatus.name)"
                            item-value="identifier"
                            prepend-inner-icon="mdi-home-search"
                            label="Unidad"
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
                </v-data-table>
            </v-card>

            <!--Seccion de dialogos-->
            <v-dialog
                v-model="showOpenAnswersDialog"
                persistent
            >
                <v-card>
                    <v-card-text v-if="openAnswersColleagues.length > 0 || openAnswersStudents.length > 0">
                        <h2 class="black--text pt-5" style="text-align: center"> Visualizando comentarios hacia el docente: {{ this.capitalize(this.selectedTeacherOpenAnswers) }}</h2>

                        <div v-if="openAnswersStudents.length > 0">
                            <div v-for="studentAnswer in openAnswersStudents" class="mt-3">
                                <h3 class="black--text pt-5"> PREGUNTA:  {{studentAnswer.question_name}}</h3>
                                <div v-for="group in studentAnswer.groups" class="mt-3">
                                    <h3 class="black--text pt-5"> {{group.group_name}} - Grupo {{group.group_number}}</h3>
                                    <div v-for="studentGroupAnswer in group.answers" class="mt-3">
                                        <h4>- {{studentGroupAnswer}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </v-card-text>

                    <v-card-text v-else>
                        <h2 class="black--text pt-5" style="text-align: center"> No hay comentarios disponibles para este docente</h2>
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
            sheet: false,
            //Table info
            search: '',
            //Display data
            deletedFormId: 0,
            assessments: [],
            assessmentPeriod: '',
            assessmentPeriods: [],
            unit: '',
            units:[],
            teacher: '',
            selectedTeacher: '',
            teachers:[],
            selectedTeacherOpenAnswers: '',
            role:'',
            roles: [],
            dataToGraph: [],
            datasets:[],
            competencesValuesAsArray:[],
            finalTeachingLadders:[],
            responseIdeals: [],
            teachingLadder:[],
            openAnswersStudents: [],
            openAnswersColleagues: [],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            groupsResults: [],
            dynamicHeaders: [],
            //Dialogs
            showChartDialog: false,
            showOpenAnswersDialog: false,
            isLoading: true,
            individualView: true,
            noDataText: 'Para comenzar, selecciona un periodo de evaluación y la unidad que deseas visualizar'
        }
    },

    props: {
        propsUnits: Array,
        assessmentPeriodsArray: Array,
        token: String
    },

    async created() {
        await this.getAssessmentPeriods();
        await this.getRoles();
        await this.getUnits();
        await this.getTeachers();
        await this.getAssessments()
        this.isLoading = false;
    },

    watch:{
        async assessmentPeriod(){
            await this.getUnits();
            await this.getTeachers();
        }
    },

    computed: {

        filteredItems() {
            let finalAssessments = this.assessments;
            if (this.unit !== '') {
                finalAssessments = this.getFilteredAssessmentsByUnit(finalAssessments);
            }
            if (this.teacher !== '') {

                console.log(this.teacher, 'Teacher sleeccionado');

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
                    return finalAssessments.some((assessment) => assessment.teacher_id == teacher.id)
                });
            }
            this.addAllElementSelectionItem(finalTeachers, 'Todos los docentes');
            return finalTeachers;
        }
    },

    methods: {

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
        },

        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data.filter(assessmentPeriod => {
                return assessmentPeriod.active === 1;
            });
        },

        getRoles (){
            this.roles = [{id:  'Todos los roles', name: ''},{id: 'jefe', name: 'jefe'},
                {id: 'par', name: 'par'}, {id: 'autoevaluación', name: 'autoevaluación'}, {id: 'estudiante', name: 'estudiante'}, {id: 'promedio final', name:'promedio final'}]
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

        getUnits: async function () {
            let request = await axios.get(route('api.units.index', {assessmentPeriodId: this.assessmentPeriod}));
            console.log(request.data)
            this.units = request.data.filter(unit => {
                return unit.teachers_from_unit.length > 0 || unit.is_custom == 1;
            });
            this.units.unshift({name: 'Todas las unidades', identifier: ''})
        },

        getTeachers: async function () {
            let request = await axios.get(route('units.teachers.assigned', {assessmentPeriodId: this.assessmentPeriod}));
            this.teachers = request.data
            this.teachers.forEach(teacher => {
                teacher.name = this.capitalize(teacher.name)
            })
        },

        async getAssessments() {
            let request = await axios.get(route('reports.group.results'));
            this.dynamicHeaders = request.data.headers
            this.assessments = request.data.items;
            console.log(this.assessments, 'assessments');
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
            return this.matchProperty(assessments, 'teacher_id', this.teacher)
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        sortArrayAlphabetically(array){
            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        setDialogToCancelOpenAnswers (){
            this.showOpenAnswersDialog = false;
            this.openAnswersColleagues = [];
            this.openAnswersStudents= [];
        },

    },
}

</script>
