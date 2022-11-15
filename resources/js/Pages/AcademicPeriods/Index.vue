<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Sincronizar periodos académicos</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncPeriods"
                    >
                        Sincronizar periodos
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="academicPeriods"
                :items-per-page="20"
                class="elevation-1"
            >
                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="setAcademicPeriodDialogToCreateOrEdit('edit',item)"
                    >
                        mdi-pencil
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar academicPeriod -->
            <v-dialog
                v-model="createOrEditDialog.dialogStatus"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Editar periodo académico</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de inicio de evaluación de estudiantes *
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].studentsStartDate"
                                                   full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de finalización de evaluación de estudiantes *
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].studentsEndDate" full-width>
                                    </v-date-picker>
                                </v-col>

                                <v-col cols="12" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Periodo de evaluación al que pertenece
                                    </span>
                                    <v-select
                                        color="primario"
                                        v-model="$data[createOrEditDialog.model].assessmentPeriodId"
                                        :items="assessmentPeriods"
                                        label="Selecciona un periodo de evaluación"
                                        :item-value="(assessmentPeriod)=>assessmentPeriod.id"
                                        :item-text="(assessmentPeriod)=>assessmentPeriod.name"
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                        <small>Los campos con * son obligatorios</small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            text
                            @click="createOrEditDialog.dialogStatus = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="handleSelectedMethod"
                        >
                            Guardar cambios
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
import AcademicPeriod from "@/models/AcademicPeriod";
import Snackbar from "@/Components/Snackbar";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },
    data: () => {
        return {
            //Table info
            headers: [
                {text: 'Periodo de evaluación', value: 'assessment_period.name'},
                {text: 'Nombre del periodo académico', value: 'name'},
                {text: 'Descripción', value: 'description'},
                {text: 'Fecha de inicio clases', value: 'class_start_date'},
                {text: 'Fecha de fin clases', value: 'class_end_date'},
                {text: 'Fecha de inicio evaluación (estudiantes)', value: 'students_start_date'},
                {text: 'Fecha de fin evaluación (estudiantes)', value: 'students_end_date'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            assessmentPeriods: [],
            academicPeriods: [],
            //AcademicPeriods models
            newAcademicPeriod: new AcademicPeriod(),
            editedAcademicPeriod: new AcademicPeriod(),
            deletedAcademicPeriodId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteAcademicPeriodDialog: false,
            createOrEditDialog: {
                model: 'newAcademicPeriod',
                method: 'createAcademicPeriod',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllAssessmentPeriods();
        await this.getAllAcademicPeriods();
        this.isLoading = false;
    },

    methods: {
        getAllAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
            this.assessmentPeriods.unshift({id: null, name: 'Ninguno'})
        },
        syncPeriods: async function () {
            try {
                let request = await axios.post(route('api.academicPeriods.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAcademicPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },
        editAcademicPeriod: async function () {
            //Verify request
            if (this.editedAcademicPeriod.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'alert', 2000);
                return;
            }
            //Recollect information
            let data = this.editedAcademicPeriod.toObjectRequest();
            try {
                let request = await axios.patch(route('api.academicPeriods.update', {'academicPeriod': this.editedAcademicPeriod.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAcademicPeriods();

                //Clear role information
                this.editedAcademicPeriod = new AcademicPeriod();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        getAllAcademicPeriods: async function () {
            let request = await axios.get(route('api.academicPeriods.index'));
            this.academicPeriods = request.data;
        },
        setAcademicPeriodDialogToCreateOrEdit(which, item = null) {
            if (which === 'edit') {
                this.editedAcademicPeriod = AcademicPeriod.fromModel(item);
                this.createOrEditDialog.method = 'editAcademicPeriod';
                this.createOrEditDialog.model = 'editedAcademicPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }
        },
    },


}
</script>
