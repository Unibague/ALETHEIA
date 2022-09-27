<template>
    <AuthenticatedLayout>
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
            :color="snackbar.color + ' accent-2'"
            top
            right
        >
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn
                    text
                    v-bind="attrs"
                    @click="snackbar.status = false"
                >
                    Cerrar
                </v-btn>
            </template>
        </v-snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar periodos de evaluación</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="setAssessmentPeriodDialogToCreateOrUpdate('create')"
                    >
                        Crear nuevo periodo
                    </v-btn>
                </div>

            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="roles"
                :items-per-page="5"
                class="elevation-1"
            >
                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="openEditAssessmentPeriodModal(item)"
                    >
                        mdi-pencil
                    </v-icon>
                    <v-icon
                        class="primario--text"
                        @click="confirmDeleteAssessmentPeriod(item)"
                    >
                        mdi-delete
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->
            <!--Crear rol -->

            <v-dialog
                v-model="createOrEditDialog.dialogStatus"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
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
                                    <v-date-picker v-model="$data[createOrEditDialog.model].colleagueStartDate" full-width>
                                    </v-date-picker>
                                </v-col>
                                <v-col cols="12" :md="6" class="d-flex flex-column">
                                    <span class="subtitle-1">
                                        Fecha de finalización par
                                    </span>
                                    <v-date-picker v-model="$data[createOrEditDialog.model].colleagueEndDate" full-width>
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
                            @click="createAssessmentPeriod"
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
import {prepareErrorText} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import AssessmentPeriod from "@/models/AssessmentPeriod";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
    },
    data: () => {
        return {
            //Table info
            headers: [
                {text: 'Nombre del periodo', value: 'name'},
                {text: 'Fecha de inicio autoevaluación', value: 'self_start_date'},
                {text: 'Fecha de fin autoevaluación', value: 'self_end_date'},
                {text: 'Fecha de inicio par', value: 'colleague_start_date'},
                {text: 'Fecha de fin par', value: 'colleague_end_date'},
                {text: 'Fecha de inicio jefe', value: 'boss_start_date'},
                {text: 'Fecha de fin jefe', value: 'boss_start_date'},
                {text: 'sin escalafón', value: 'boss_start_date',sortable: false},
                {text: 'Auxiliar', value: 'boss_start_date',sortable: false},
                {text: 'Asistente', value: 'boss_start_date',sortable: false},
                {text: 'Asociado', value: 'boss_start_date',sortable: false},
                {text: 'Titular', value: 'boss_start_date',sortable: false},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            roles: [],
            //AssessmentPeriods models
            newAssessmentPeriod: new AssessmentPeriod('Hola', '2021-02-04'),
            editedAssessmentPeriod: null,
            deletedAssessmentPeriodId: 0,
            //Snackbars
            snackbar: {
                text: "",
                color: 'red',
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
        openEditAssessmentPeriodModal: function (role) {
            this.editedAssessmentPeriod = {...role};
            this.editAssessmentPeriodDialog = true;
        },
        editAssessmentPeriod: async function () {
            //Verify request
            if (this.editedAssessmentPeriod.name === '' || this.editedAssessmentPeriod.id === '') {
                this.snackbar.text = 'Debes proporcionar un nombre y Id para el nuevo rol';
                this.snackbar.status = true;
                return;
            }
            //Recollect information
            let data = {
                id: this.editedAssessmentPeriod.id,
                name: this.editedAssessmentPeriod.name,
                customId: this.editedAssessmentPeriod.customId
            }

            try {
                let request = await axios.patch(route('api.roles.update', {'role': this.editedAssessmentPeriod.id}), data);
                this.editAssessmentPeriodDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllAssessmentPeriods();

                //Clear role information
                this.editedAssessmentPeriod = {
                    id: '',
                    name: '',
                    customId: '',
                };
            } catch (e) {
                this.snackbar.text = prepareErrorText(e);
                this.snackbar.status = true;
            }
        },

        confirmDeleteAssessmentPeriod: function (role) {
            this.deletedAssessmentPeriodId = role.id;
            this.deleteAssessmentPeriodDialog = true;
        },

        deleteAssessmentPeriod: async function (roleId) {
            try {
                let request = await axios.delete(route('api.roles.destroy', {role: roleId}));
                this.deleteAssessmentPeriodDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllAssessmentPeriods();

            } catch (e) {
                this.snackbar.text = e.response.data.message;
                this.snackbar.status = true;
            }

        },
        getAllAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.roles = request.data;
        },
        setAssessmentPeriodDialogToCreateOrUpdate(which) {

            if (which === 'create') {
                this.createOrEditDialog.method = 'createAssessmentPeriod';
                this.createOrEditDialog.model = 'newAssessmentPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

            if (which === 'edit') {
                this.createOrEditDialog.method = 'editAssessmentPeriod';
                this.createOrEditDialog.model = 'editedAssessmentPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

        },
        createAssessmentPeriod: async function () {
            if (this.newAssessmentPeriod.name === '' || this.newAssessmentPeriod.id === '') {
                this.snackbar.text = 'Debes proporcionar un nombre y Id para el nuevo rol';
                this.snackbar.status = true;
                return;
            }

            let data = {
                name: this.newAssessmentPeriod.name,
                customId: this.newAssessmentPeriod.id
            }
            //Clear role information
            this.newAssessmentPeriod = {
                name: '',
                id: ''
            }
            try {
                let request = await axios.post(route('api.roles.index'), data);
                this.createAssessmentPeriodDialog = false;
                this.snackbar.text = request.data.message;
                this.snackbar.status = true;
                this.getAllAssessmentPeriods();
            } catch (e) {
                this.snackbar.text = e.response.data.message;
                this.snackbar.status = true;
            }

        }
    },


}
</script>
