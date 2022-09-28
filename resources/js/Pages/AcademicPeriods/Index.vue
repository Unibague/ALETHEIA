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
                :items="roles"
                :items-per-page="5"
                class="elevation-1"
                :item-class="getRowColor"
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
                :show="deleteAcademicPeriodDialog"
                @canceled-dialog="deleteAcademicPeriodDialog = false"
                @confirmed-dialog="deleteAcademicPeriod(deletedAcademicPeriodId)"
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
                {text: 'Nombre del periodo académico', value: 'name'},
                {text: 'Periodo de evaluación', value: 'assessment_period.name'},
                {text: 'Fecha de inicio autoevaluación', value: 'class_start_date'},
                {text: 'Fecha de fin autoevaluación', value: 'class_end_date'},
                {text: 'Fecha de inicio autoevaluación', value: 'students_start_date'},
                {text: 'Fecha de fin autoevaluación', value: 'students_end_date'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            roles: [],
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
        await this.getAllAcademicPeriods();
        this.isLoading = false;
    },

    methods: {
        syncPeriods: async function () {
            try {
                let request = await axios.post(route('api.academicPeriods.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAcademicPeriods();

                //Clear role information
                this.editedAcademicPeriod = new AcademicPeriod();
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

        confirmDeleteAcademicPeriod: function (role) {
            this.deletedAcademicPeriodId = role.id;
            this.deleteAcademicPeriodDialog = true;
        },
        deleteAcademicPeriod: async function (academicPeriodId) {
            try {
                let request = await axios.delete(route('api.academicPeriods.destroy', {academicPeriod: academicPeriodId}));
                this.deleteAcademicPeriodDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAcademicPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        getAllAcademicPeriods: async function () {
            let request = await axios.get(route('api.academicPeriods.index'));
            this.roles = request.data;
        },
        setAcademicPeriodDialogToCreateOrEdit(which, item = null) {
            if (which === 'create') {
                this.createOrEditDialog.method = 'createAcademicPeriod';
                this.createOrEditDialog.model = 'newAcademicPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

            if (which === 'edit') {
                this.editedAcademicPeriod = AcademicPeriod.fromModel(item);
                this.createOrEditDialog.method = 'editAcademicPeriod';
                this.createOrEditDialog.model = 'editedAcademicPeriod';
                this.createOrEditDialog.dialogStatus = true;
            }

        },
        createAcademicPeriod: async function () {
            if (this.newAcademicPeriod.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            let data = this.newAcademicPeriod.toObjectRequest();

            //Clear role information
            // this.newAcademicPeriod = new AcademicPeriod();

            try {
                let request = await axios.post(route('api.academicPeriods.store'), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllAcademicPeriods();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 3000);
            }
        }
    },


}
</script>
