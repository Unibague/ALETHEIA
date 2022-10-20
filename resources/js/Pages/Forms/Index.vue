<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end">
                <h2 class="align-self-start">Gestionar formularios</h2>
                <div>
                    <v-btn
                        class="mr-3"
                        @click="createOthersFormDialog=true"
                    >
                        Crear formulario para otros
                    </v-btn>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="createStudentFormDialog=true"
                    >
                        Crear formulario para estudiantes
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <h3 class="mb-5">Formularios para estudiantes</h3>
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="studentTableHeaders"
                :items="studentsForms"
                :items-per-page="15"
                class="elevation-1"
            >
                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="setFormDialogToCreateOrEdit('edit',item)"
                    >
                        mdi-pencil
                    </v-icon>
                    <v-icon
                        class="primario--text"
                        @click="confirmDeleteForm(item)"
                    >
                        mdi-content-copy
                    </v-icon>
                    <v-icon
                        class="primario--text"
                        @click="confirmDeleteForm(item)"
                    >
                        mdi-delete
                    </v-icon>

                </template>
            </v-data-table>
            <h3 class="mt-10 mb-5">Formularios para otros roles (par, jefe, autoevaluación)</h3>
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="othersTableHeaders"
                :items="othersForms"
                :items-per-page="15"
                class="elevation-1"
            >
                <template v-slot:item="{ item }">
                    <tr>
                        <td>
                            {{ item.name }}
                        </td>
                        <td>
                            {{item.assessment_period ? item.assessment_period.name : 'No diligenciado'}}
                        </td>
                        <td>
                            {{ item.unit_role != null ? item.unit_role : 'No diligenciado' }}
                        </td>
                        <td>
                            {{ item.teaching_ladder != null ? item.teaching_ladder : 'No diligenciado' }}
                        </td>
                        <td>
                            {{ item.unit != null ? item.unit.name : 'No diligenciado' }}
                        </td>

                        <td>
                            <v-icon
                                class="mr-2 primario--text"
                                @click="setFormDialogToCreateOrEdit('edit',item)"
                            >
                                mdi-pencil
                            </v-icon>
                            <v-icon
                                class="primario--text"
                                @click="confirmDeleteForm(item)"
                            >
                                mdi-content-copy
                            </v-icon>
                            <v-icon
                                class="primario--text"
                                @click="confirmDeleteForm(item)"
                            >
                                mdi-delete
                            </v-icon>
                        </td>

                    </tr>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar form -->
            <v-dialog
                v-model="createStudentFormDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Crear un nuevo formulario para estudiantes</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Nombre del formulario *"
                                        required
                                        v-model="newStudentForm.name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newStudentForm.degree"
                                        :items="newStudentForm.getPossibleDegrees()"
                                        label="Nivel de formación"
                                        item-value="name"
                                        :item-text="(degree)=> degree.name.charAt(0).toUpperCase() + degree.name.slice(1)"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newStudentForm.academicPeriodId"
                                        :items="academicPeriods"
                                        label="Periodo académico"
                                        :item-text="(academicPeriod)=>academicPeriod.name"
                                        :item-value="(academicPeriod)=>academicPeriod.id"

                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newStudentForm.serviceAreaId"
                                        :items="serviceAreas"
                                        label="Área de servicio"
                                        :item-text="(academicPeriod)=>academicPeriod.name"
                                        :item-value="(academicPeriod)=>academicPeriod.id"
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
                            @click="createStudentFormDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="createForm('newStudentForm')"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog
                v-model="createOthersFormDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Crear un nuevo formulario para otros</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Nombre del formulario *"
                                        required
                                        v-model="newOthersForm.name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newOthersForm.assessmentPeriodId"
                                        :items="assessmentPeriods"
                                        label="Periodo de evaluación"
                                        :item-text="(assessmentPeriod)=>assessmentPeriod.name"
                                        :item-value="(assessmentPeriod)=>assessmentPeriod.id"

                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newOthersForm.unitRole"
                                        :items="newOthersForm.getPossibleRoles()"
                                        label="Rol"
                                        item-value="name"
                                        :item-text="(role)=> role.name.charAt(0).toUpperCase() + role.name.slice(1)"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newOthersForm.teachingLadder"
                                        :items="newOthersForm.getPossibleTeachingLadders()"
                                        label="Escalafón"
                                        item-value="name"
                                        :item-text="(teachingLadder)=> teachingLadder.name.charAt(0).toUpperCase() + teachingLadder.name.slice(1)"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="newOthersForm.unitId"
                                        :items="units"
                                        label="Unidad"
                                        :item-text="(unit)=>unit.name"
                                        :item-value="(unit)=>unit.id"
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
                            @click="createOthersFormDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="createForm('newOthersForm')"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>


            <!--Confirmar borrar rol-->
            <confirm-dialog
                :show="deleteFormDialog"
                @canceled-dialog="deleteFormDialog = false"
                @confirmed-dialog="deleteForm(deletedFormId)"
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
import Form from "@/models/Form";
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
            studentTableHeaders: [
                {text: 'Nombre', value: 'name'},
                {text: 'Nivel de formación', value: 'degree'},
                {text: 'Periodo académico', value: 'academic_period.name'},
                {text: 'Área de servicio', value: 'service_area.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            othersTableHeaders: [
                {text: 'Nombre', value: 'name'},
                {text: 'Periodo de evaluación', value: 'assessment_period.name'},
                {text: 'Rol', value: 'unit_role'},
                {text: 'Escalafón', value: 'teaching_ladder'},
                {text: 'Unidad', value: 'unit.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            forms: [],
            studentsForms: [],
            othersForms: [],
            //data for modals
            academicPeriods: [],
            assessmentPeriods: [],
            serviceAreas: [],
            units: [],

            //Forms models
            newStudentForm: new Form(),
            editedStudentForm: new Form(),
            newOthersForm: new Form(),
            editedOthersForm: new Form(),
            deletedFormId: 0,

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteFormDialog: false,
            createStudentFormDialog: false,
            editStudentFormDialog: false,
            createOthersFormDialog: false,
            editOthersFormDialog: false,

            isLoading: true,
        }
    },
    async created() {
        await this.getAllForms();
        await this.getCurrentAssessmentPeriodAcademicPeriods();
        this.getServiceAreas();
        this.getAssessmentPeriods();
        this.getUnits();

        this.isLoading = false;
    },

    methods: {
        setFormAsActive: async function (formId) {
            try {
                let request = await axios.post(route('api.forms.setActive', {'form': formId}));
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllForms();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },
        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },
        editForm: async function () {
            //Verify request
            if (this.editedForm.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            //Recollect information
            let data = this.editedForm.toObjectRequest();

            try {
                let request = await axios.patch(route('api.forms.update', {'form': this.editedForm.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllForms();

                //Clear form information
                this.editedForm = new Form();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        confirmDeleteForm: function (form) {
            this.deletedFormId = form.id;
            this.deleteFormDialog = true;
        },
        deleteForm: async function (formId) {
            try {
                let request = await axios.delete(route('api.forms.destroy', {form: formId}));
                this.deleteFormDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllForms();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        getAllForms: async function () {
            let request = await axios.get(route('api.forms.index'));
            this.forms = request.data;
            this.formatForms();
        },
        getServiceAreas: async function () {
            let request = await axios.get(route('api.serviceAreas.index'));
            this.serviceAreas = request.data;
        },
        getUnits: async function () {
            let request = await axios.get(route('api.units.index'));
            this.units = request.data;
        },

        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
        },
        getCurrentAssessmentPeriodAcademicPeriods: async function () {
            let request = await axios.get(route('api.academicPeriods.index'), {
                params: {active: true}
            });
            this.academicPeriods = request.data;
        },
        formatForms: function () {
            const forms = this.forms;
            this.studentsForms = [];
            this.othersForms = [];
            forms.forEach((form) => {
                if (form.type === 'estudiantes') {
                    this.studentsForms.push(form);
                } else {
                    this.othersForms.push(form);
                }
            });
        },
        createForm: async function (formModel) {
            if (this[formModel].hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            if (formModel === 'newStudentForm') {
                this[formModel].type = 'estudiantes';
            }
            if (formModel === 'newOthersForm') {
                this[formModel].type = 'otros';
            }
            let data = this[formModel].toObjectRequest();


            //Clear form information
            this[formModel] = new Form();

            try {
                let request = await axios.post(route('api.forms.store'), data);
                if (formModel === 'newStudentForm') {
                    this.createStudentFormDialog = false;
                }
                if (formModel === 'newOthersForm') {
                    this.createOthersFormDialog = false;
                }
                console.log(request);

                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllForms();
            } catch (e) {
                console.log(e);
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert', 3000);
            }
        }
    },


}
</script>
