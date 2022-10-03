<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar unidades</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncPeriods"
                    >
                        Crear nueva unidad
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="unities"
                :items-per-page="5"
                class="elevation-1"
            >
                <template v-slot:item.type="{ item }">
                    {{item.is_custom ? 'Personalizada': 'Integración'}}
                </template>

                <template v-slot:item.users="{ item }">
                    {{item.is_custom ? 'Personalizada': 'Integración'}}
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="setUnityDialogToCreateOrEdit('edit',item)"
                    >
                        mdi-account-group
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar unity -->
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
                                    <v-date-picker v-model="$data[createOrEditDialog.model].studentsStartDate" full-width>
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
import Unity from "@/models/Unity";
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
                {text: 'Nombre', value: 'name'},
                {text: 'Tipo de unidad', value: 'type'},
                {text: 'Cantidad de docentes', value: 'users'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            assessmentPeriods: [],
            unities: [],
            //Unities models
            newUnity: new Unity(),
            editedUnity: new Unity(),
            deletedUnityId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteUnityDialog: false,
            createOrEditDialog: {
                model: 'newUnity',
                method: 'createUnity',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllAssessmentPeriods();
        await this.getAllUnitys();
        this.isLoading = false;
    },

    methods: {
        getAllAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
        },
        syncPeriods: async function () {
            try {
                let request = await axios.post(route('api.unities.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllUnitys();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },
        editUnity: async function () {
            console.log(this.editedUnity);
            //Verify request
            if (this.editedUnity.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'alert', 2000);
                return;
            }
            //Recollect information
            let data = this.editedUnity.toObjectRequest();
            console.log(data);
            try {
                let request = await axios.patch(route('api.unities.update', {'unity': this.editedUnity.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllUnitys();

                //Clear role information
                this.editedUnity = new Unity();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        getAllUnitys: async function () {
            let request = await axios.get(route('api.unities.index'));
            this.unities = request.data;
        },
        setUnityDialogToCreateOrEdit(which, item = null) {
            if (which === 'edit') {
                this.editedUnity = Unity.fromModel(item);
                this.createOrEditDialog.method = 'editUnity';
                this.createOrEditDialog.model = 'editedUnity';
                this.createOrEditDialog.dialogStatus = true;
            }
        },
    },


}
</script>
