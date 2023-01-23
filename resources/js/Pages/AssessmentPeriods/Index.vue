<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar periodos de evaluación</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="setAssessmentPeriodDialogToCreateOrEdit('create')"
                    >
                        Crear nuevo periodo
                    </v-btn>


                </div>

            </div>

            <!--Inicia tabla-->

            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar por nombre o fecha"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="assessmentPeriods"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                    :item-class="getRowColor"
                >
                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="mr-2 primario--text"
                            @click="setAssessmentPeriodDialogToCreateOrEdit('edit',item)"
                        >
                            mdi-pencil
                        </v-icon>
                        <v-icon
                            class="primario--text"
                            @click="confirmDeleteAssessmentPeriod(item)"
                        >
                            mdi-delete
                        </v-icon>
                        <v-icon v-if="!(item.active)"
                                class="mr-2 primario--text"
                                @click="setAssessmentPeriodAsActive(item.id)"
                        >
                            mdi-cursor-default-click
                        </v-icon>
                    </template>
                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar assessmentPeriod -->
            <v-dialog
                v-model="createOrEditDialog.dialogStatus"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Crear un nuevo periodo académico</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Nombre del periodo de evaluación *"
                                        required
                                        v-model="$data[createOrEditDialog.model].name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de inicio autoevaluación
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].selfStartDate" full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de finalización autoevaluación
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].selfEndDate" full-width>
                                    </v-date-picker>
                                </v-col>

                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de inicio jefe
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].bossStartDate" full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de finalización jefe
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].bossEndDate" full-width>
                                    </v-date-picker>
                                </v-col>

                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de inicio par
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].colleagueStartDate"
                                                   full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de finalización par
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].colleagueEndDate"
                                                   full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12">
                                    <span class="subtitle-1">
                                       Por favor seleccione los escalafones que realizan 360 este periodo académico
                                    </span>
                                    <v-checkbox
                                        v-model="$data[createOrEditDialog.model].doneByNone"
                                        label="Sin escalafón"
                                    ></v-checkbox>
                                    <v-checkbox
                                        v-model="$data[createOrEditDialog.model].doneByAuxiliary"
                                        label="Auxiliar"
                                    ></v-checkbox>
                                    <v-checkbox
                                        v-model="$data[createOrEditDialog.model].doneByAssistant"
                                        label="Asistente"
                                    ></v-checkbox>
                                    <v-checkbox
                                        v-model="$data[createOrEditDialog.model].doneByAssociated"
                                        label="Asociado"
                                    ></v-checkbox>
                                    <v-checkbox
                                        v-model="$data[createOrEditDialog.model].doneByHeadTeacher"
                                        label="Titular"
                                    ></v-checkbox>
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

            <!--Confirmar borrar rol-->
            <confirm-dialog
                :show="deleteAssessmentPeriodDialog"
                @canceled-dialog="deleteAssessmentPeriodDialog = false"
                @confirmed-dialog="deleteAssessmentPeriod(deletedAssessmentPeriodId)"
            >
                <template v-slot:title>
                    Estas a punto de eliminar el rol seleccionado
                </template>

                ¡Cuidado! esta acción es irreversible

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>

        </v-container>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import AssessmentPeriod from "@/models/AssessmentPeriod";
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
            search: '',
            headers: [
                {text: 'Nombre del periodo', value: 'name'},
                {text: 'Fecha de inicio autoevaluación', value: 'self_start_date'},
                {text: 'Fecha de fin autoevaluación', value: 'self_end_date'},
                {text: 'Fecha de inicio par', value: 'colleague_start_date'},
                {text: 'Fecha de fin par', value: 'colleague_end_date'},
                {text: 'Fecha de inicio jefe', value: 'boss_start_date'},
                {text: 'Fecha de fin jefe', value: 'boss_start_date'},
                {text: 'sin escalafón', value: 'done_by_none', sortable: false},
                {text: 'Auxiliar', value: 'done_by_auxiliary', sortable: false},
                {text: 'Asistente', value: 'done_by_assistant', sortable: false},
                {text: 'Asociado', value: 'done_by_associated', sortable: false},
                {text: 'Titular', value: 'done_by_head_teacher', sortable: false},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            assessmentPeriods: [],
            //AssessmentPeriods models
            newAssessmentPeriod: new AssessmentPeriod(),
            editedAssessmentPeriod: new AssessmentPeriod(),
            deletedAssessmentPeriodId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteAssessmentPeriodDialog: false,
            createOrEditDialog: {
                model: 'newAssessmentPeriod',
                method: 'createAssessmentPeriod',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllAssessmentPeriods();
        this.isLoading = false;
    },

    methods: {
        setAssessmentPeriodAsActive: async function (assessmentPeriodId) {
            try {
                let request = await axios.post(route('api.assessmentPeriods.setActive', {'assessmentPeriod': assessmentPeriodId}));
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAssessmentPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },
        getRowColor: function (item) {
            return item.active ? 'green lighten-5' : '';
        },
        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },
        editAssessmentPeriod: async function () {
            //Verify request
            if (this.editedAssessmentPeriod.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            //Recollect information
            let data = this.editedAssessmentPeriod.toObjectRequest();

            try {
                let request = await axios.patch(route('api.assessmentPeriods.update', {'assessmentPeriod': this.editedAssessmentPeriod.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAssessmentPeriods();

                //Clear role information
                this.editedAssessmentPeriod = new AssessmentPeriod();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        confirmDeleteAssessmentPeriod: function (role) {
            this.deletedAssessmentPeriodId = role.id;
            this.deleteAssessmentPeriodDialog = true;
        },
        deleteAssessmentPeriod: async function (assessmentPeriodId) {
            try {
                let request = await axios.delete(route('api.assessmentPeriods.destroy', {assessmentPeriod: assessmentPeriodId}));
                this.deleteAssessmentPeriodDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAssessmentPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        getAllAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
        },
        setAssessmentPeriodDialogToCreateOrEdit(which, item = null) {
            if (which === 'create') {
                this.createOrEditDialog.method = 'createAssessmentPeriod';
                this.createOrEditDialog.model = 'newAssessmentPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

            if (which === 'edit') {
                this.editedAssessmentPeriod = AssessmentPeriod.fromModel(item);
                this.createOrEditDialog.method = 'editAssessmentPeriod';
                this.createOrEditDialog.model = 'editedAssessmentPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

        },
        createAssessmentPeriod: async function () {
            if (this.newAssessmentPeriod.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            let data = this.newAssessmentPeriod.toObjectRequest();

            //Clear role information
            this.newAssessmentPeriod = new AssessmentPeriod();

            try {
                let request = await axios.post(route('api.assessmentPeriods.store'), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllAssessmentPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 3000);
            }
        }
    },


}
</script>
