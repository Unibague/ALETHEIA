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
                        @click="openFormDialog('create','othersForm')"
                    >
                        Crear formulario para otros
                    </v-btn>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="openFormDialog('create','studentForm')"
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
                <template v-slot:item="{ item }">
                    <tr>
                        <td>
                            {{ item.name }}
                        </td>
                        <td>
                            {{ item.degree ? item.degree : 'Todos' }}
                        </td>
                        <td>
                            {{ item.academic_period != null ? item.academic_period.name : 'Todos' }}
                        </td>
                        <td>
                            {{ getTableServiceAreas(item.service_areas_id) }}
                        </td>

                        <td>
                            <v-icon
                                class="mr-2 primario--text"
                                @click="openFormDialog('edit','studentForm',item)"
                            >
                                mdi-pencil
                            </v-icon>
                            <v-icon
                                class="primario--text"
                                @click="copy(item.id)"
                            >
                                mdi-content-copy
                            </v-icon>
                            <InertiaLink as="v-icon" class="primario--text"
                                         :href="route('forms.show.view',{form:item.id})">
                                mdi-format-list-bulleted
                            </InertiaLink>

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
                            {{ item.assessment_period ? item.assessment_period.name : 'Todos' }}
                        </td>
                        <td>
                            {{ item.unit_role != null ? item.unit_role : 'Todos' }}
                        </td>
                        <td>
                            {{ item.teaching_ladder != null ? item.teaching_ladder : 'Todos' }}
                        </td>
                        <td>
                            {{ getTableUnits(item.units_id) }}
                        </td>

                        <td>
                            <v-icon
                                class="mr-2 primario--text"
                                @click="openFormDialog('edit','othersForm',item)"
                            >
                                mdi-pencil
                            </v-icon>
                            <v-icon
                                class="primario--text"
                                @click="copy(item.id)"
                            >
                                mdi-content-copy
                            </v-icon>
                            <InertiaLink as="v-icon" class="primario--text"
                                         :href="route('forms.show.view',{form:item.id})">
                                mdi-format-list-bulleted
                            </InertiaLink>

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
                        <span
                            class="text-h5">Crear un nuevo formulario para estudiantes</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Nombre del formulario *"
                                        required
                                        v-model="studentForm.name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="studentForm.degree"
                                        :items="degrees"
                                        label="Nivel de formación"
                                        item-value="value"
                                        :item-text="(degree)=> degree.name.charAt(0).toUpperCase() + degree.name.slice(1)"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="studentForm.academicPeriodId"
                                        :items="academicPeriods"
                                        label="Periodo académico"
                                        :item-text="(academicPeriod)=>this.studentForm.degree === null ? 'Todos':academicPeriod.name"
                                        :item-value="(academicPeriod)=>this.studentForm.degree === null ? null:academicPeriod.id"
                                        :disabled="studentForm.degree === null"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        multiple
                                        v-model="studentForm.serviceAreasId"
                                        :items="serviceAreas"
                                        label="Área de servicio"
                                        :item-text="(serviceArea)=>serviceArea.name"
                                        :item-value="(serviceArea)=>serviceArea"
                                        :disabled="studentForm.academicPeriodId === null"
                                    >
                                        <template v-slot:selection="{ item, index }">
                                            <v-chip v-if="index === 0">
                                                <span>{{ item.name }}</span>
                                            </v-chip>
                                            <span
                                                v-if="index === 1"
                                                class="grey--text text-caption"
                                            >
                                              (+{{ studentForm.serviceAreasId.length - 1 }} otros)
                                            </span>
                                        </template>

                                    </v-select>
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
                            @click="createForm('studentForm')"
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
                                        v-model="othersForm.name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="othersForm.assessmentPeriodId"
                                        :items="assessmentPeriods"
                                        label="Periodo de evaluación"
                                        :item-text="(assessmentPeriod)=>assessmentPeriod.name"
                                        :item-value="(assessmentPeriod)=>assessmentPeriod.id"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="othersForm.unitRole"
                                        :items="roles"
                                        label="Rol"
                                        :item-text="(role)=>this.othersForm.assessmentPeriodId === null ? 'Todas':role.name"
                                        :item-value="(role)=>this.othersForm.assessmentPeriodId === null ? null:role.value"
                                        :disabled="othersForm.assessmentPeriodId === null"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="othersForm.teachingLadder"
                                        :items="teachingLadders"
                                        label="Escalafón"
                                        :item-text="(teachingLadder)=> this.othersForm.unitRole === null ? 'Todos' :teachingLadder.name"
                                        :item-value="(teachingLadder)=> this.othersForm.unitRole === null ? null: teachingLadder.value"
                                        :disabled="othersForm.unitRole === null"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        color="primario"
                                        v-model="othersForm.unitsId"
                                        :items="units"
                                        multiple
                                        label="Unidades"
                                        :item-text="(unit)=>this.othersForm.teachingLadder === null ? 'Todas':unit.name"
                                        :item-value="(unit)=>this.othersForm.teachingLadder === null ? null:unit"
                                        :disabled="othersForm.teachingLadder === null"
                                    >
                                        <template v-slot:selection="{ item, index }">
                                            <v-chip v-if="index === 0">
                                                <span>{{ item.name }}</span>
                                            </v-chip>
                                            <span
                                                v-if="index === 1"
                                                class="grey--text text-caption"
                                            >
                                              (+{{ othersForm.unitsId.length - 1 }} otros)
                                            </span>
                                        </template>
                                    </v-select>
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
                            @click="createForm('othersForm')"
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
                {text: 'Unidades', value: 'unit.name'},
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
            teachingLadders: [],
            roles: [],
            degrees: [],

            //Forms models
            studentForm: new Form(),
            isServiceAreaDisabled: false,
            isAcademicPeriodDisabled: false,
            othersForm: new Form(),
            isRoleDisabled: false,
            isTeacherLadderDisabled: false,
            isUnitDisabled: false,
            formMethod: 'create',
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
        this.getCurrentAssessmentPeriodAcademicPeriods();
        this.getServiceAreas();
        this.getAssessmentPeriods();
        this.getUnits();
        this.getTeachingLadders();
        this.getRoles();
        this.getDegrees();

        this.isLoading = false;
    },

    methods: {

        getTableServiceAreas: function (item) {
            if (!(Array.isArray(item))) {
                return 'Ninguna';
            }
            let isNull = false;
            let names = [];
            item.forEach(function (serviceArea) {
                console.log('for', serviceArea);
                if (serviceArea.id === null) {
                    isNull = true;
                }
                names.push(serviceArea.name);
            })

            return isNull ? 'Todas' : names.join(', ');
        },
        getTableUnits: function (item) {
            if (!(Array.isArray(item))) {
                return 'Ninguna';
            }
            let isNull = false;
            let names = [];
            item.forEach(function (unit) {
                console.log('for', unit);
                if (unit.id === null) {
                    isNull = true;
                }
                names.push(unit.name);
            })

            return isNull ? 'Todas' : names.join(', ');
        },

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
        openFormDialog(method, model, form = null) {
            this.formMethod = method;
            if (method === 'edit') {
                this[model] = Form.fromModel(form);
            } else{
                this[model] = new Form();
            }

            if (model === 'studentForm') {
                this.createStudentFormDialog = true;
            }
            if (model === 'othersForm') {
                this.createOthersFormDialog = true;
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
            this.serviceAreas.unshift({
                id: null,
                name: "Todas"
            });
        },
        copy: async function (formId) {
            try {
                await axios.post(route('api.forms.copy', {form: formId}));
                showSnackbar(this.snackbar, 'Formulario copiado exitosamente');
                await this.getAllForms();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        getUnits: async function () {
            let request = await axios.get(route('api.units.index'));
            this.units = request.data;
            this.units.unshift({
                id: null,
                name: "Todas"
            });
        },
        getRoles() {
            this.roles = Form.getPossibleRoles();
        },
        getTeachingLadders() {
            this.teachingLadders = Form.getPossibleTeachingLadders();
        },
        getDegrees() {
            this.degrees = Form.getPossibleDegrees();
        },


        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
            this.assessmentPeriods.unshift({
                id: null,
                name: "Todos"
            });
        },
        getCurrentAssessmentPeriodAcademicPeriods: async function () {
            let request = await axios.get(route('api.academicPeriods.index'), {
                params: {active: true}
            });
            this.academicPeriods = request.data;
            this.academicPeriods.unshift({
                id: null,
                name: "Todos"
            });
        },
        formatForms: function () {
            const forms = this.forms;
            console.log(forms);
            this.studentsForms = [];
            this.othersForms = [];
            forms.forEach((form) => {
                if (form.type === 'estudiantes') {
                    //Decode the json string
                    form.service_areas_id = JSON.parse(form.service_areas_id);
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
            if (formModel === 'studentForm') {
                this[formModel].type = 'estudiantes';
            }
            if (formModel === 'othersForm') {
                this[formModel].type = 'otros';
            }
            const endpoint = route('api.forms.store', {form: this[formModel].id});
            const axiosMethod = 'post';
            let data = this[formModel].toObjectRequest();

            try {
                let request = await axios[axiosMethod](endpoint, data);
                if (formModel === 'studentForm') {
                    this.createStudentFormDialog = false;
                }
                if (formModel === 'othersForm') {
                    this.createOthersFormDialog = false;
                }
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllForms();
                //Clear form information
                this[formModel] = new Form();

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert', 3000);
            }
        }
    },

    watch: {

        'studentForm.degree'(newDegree, oldAcademicPeriod) {
            if (newDegree === null) {
                this.studentForm.academicPeriodId = null;
                this.isAcademicPeriodDisabled = true;

                this.studentForm.serviceAreasId = [{id:null,name:'Todas'}];
                this.isServiceAreaDisabled = true;

            } else {
                this.isAcademicPeriodDisabled = false;
                this.isServiceAreaDisabled = false;
            }
        },
        'studentForm.academicPeriodId'(newAcademicPeriod, oldAcademicPeriod) {
            if (newAcademicPeriod === null) {
                this.studentForm.serviceAreasId = [{id:null,name:'Todas'}];
                this.isServiceAreaDisabled = true;
            } else {
                this.isServiceAreaDisabled = false;
            }
        },
        'othersForm.assessmentPeriodId'(newAssessmentPeriodId, oldAcademicPeriod) {
            if (newAssessmentPeriodId === null) {
                this.othersForm.unitRole = null;
                this.isRoleDisabled = true;

                this.othersForm.teachingLadder = null;
                this.isTeacherLadderDisabled = true;

                this.othersForm.unitsId = [{id:null,name:'Todas'}]
                this.isUnitDisabled = true;

            } else {
                this.isRoleDisabled = false;
                this.isTeacherLadderDisabled = false;
                this.isUnitDisabled = false;
            }
        },

        'othersForm.unitRole'(newUnitRole, oldAcademicPeriod) {
            if (newUnitRole === null) {
                this.othersForm.teachingLadder = null;
                this.isTeacherLadderDisabled = true;

                this.othersForm.unitsId = [{id:null,name:'Todas'}]
                this.isUnitDisabled = true;

            } else {
                this.isTeacherLadderDisabled = false;
                this.isUnitDisabled = false;
            }
        },
        'othersForm.teachingLadder'(newTeachingLadder, oldAcademicPeriod) {
            if (newTeachingLadder === null) {
                this.othersForm.unitsId = [{id:null,name:'Todas'}]
                this.isUnitDisabled = true;

            } else {
                this.isUnitDisabled = false;
            }
        },

    },


}
</script>
