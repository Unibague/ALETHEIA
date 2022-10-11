<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar formularios</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="setFormDialogToCreateOrEdit('create')"
                    >
                        Crear nuevo formulario
                    </v-btn>


                </div>

            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="forms"
                :items-per-page="15"
                class="elevation-1"
                :item-class="getRowColor"
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
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar form -->
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
            headers: [
                {text: 'Nombre', value: 'name'},
                {text: 'Tipo', value: 'type'},
                {text: 'Periodo académico', value: 'academicPeriod.name'},
                {text: 'Unidad de servicio', value: 'unit.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            forms: [],
            //Forms models
            newForm: new Form(),
            editedForm: new Form(),
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
            createOrEditDialog: {
                model: 'newForm',
                method: 'createForm',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllForms();
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
        getRowColor: function (item) {
            return item.active ? 'green lighten-5' : '';
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
        },
        setFormDialogToCreateOrEdit(which, item = null) {
            if (which === 'create') {
                this.createOrEditDialog.method = 'createForm';
                this.createOrEditDialog.model = 'newForm';
                this.createOrEditDialog.dialogStatus = true;
            }

            if (which === 'edit') {
                this.editedForm = Form.fromModel(item);
                this.createOrEditDialog.method = 'editForm';
                this.createOrEditDialog.model = 'editedForm';
                this.createOrEditDialog.dialogStatus = true;
            }

        },
        createForm: async function () {
            if (this.newForm.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            let data = this.newForm.toObjectRequest();

            //Clear form information
            // this.newForm = new Form();

            try {
                let request = await axios.post(route('api.forms.store'), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllForms();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 3000);
            }
        }
    },


}
</script>
