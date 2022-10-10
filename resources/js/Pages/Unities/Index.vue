<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar unidades</h2>
                <div>
                    <v-btn
                        @click="syncUnits"
                    >
                        Sincronizar unidades
                    </v-btn>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="setUnitDialogToCreateOrEdit('create')"
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
                :items="units"
                :items-per-page="15"
                class="elevation-1"
            >
                <template v-slot:item.type="{ item }">
                    {{ item.is_custom ? 'Personalizada' : 'Integración' }}
                </template>

                <template v-slot:item.users="{ item }">
                    {{ item.users.length }}
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon
                        class="mr-2 primario--text"
                        @click="setUnitDialogToCreateOrEdit('edit',item)"
                    >
                        mdi-account-group
                    </v-icon>

                    <v-icon
                        v-if="item.is_custom"
                        class="mr-2 primario--text"
                        @click="confirmDeleteUnit(item)"
                    >
                        mdi-delete
                    </v-icon>

                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar unit -->
            <v-dialog
                v-model="createOrEditDialog.dialogStatus"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Crear/editar unidad</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Nombre de la unidad"
                                        required
                                        v-model="$data[createOrEditDialog.model].name"
                                    ></v-text-field>
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

            <confirm-dialog
                :show="deleteUnitDialog"
                @canceled-dialog="deleteUnitDialog = false"
                @confirmed-dialog="deleteUnit(deletedUnitId)"
            >
                <template v-slot:title>
                    Estas a punto de eliminar la unidad seleccionada
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
import Unit from "@/models/Unit";
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
            units: [],
            //Units models
            newUnit: new Unit(),
            editedUnit: new Unit(),
            deletedUnitId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteUnitDialog: false,
            createOrEditDialog: {
                model: 'newUnit',
                method: 'createUnit',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllUnits();
        this.isLoading = false;
    },

    methods: {
        syncUnits: async function () {
            try {
                let request = await axios.post(route('api.units.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getAllUnits();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },
        confirmDeleteUnit: function (unit) {
            this.deletedUnitId = unit.id;
            this.deleteUnitDialog = true;
        },
        deleteUnit: async function (unitId) {
            console.log(unitId)
            try {
                let request = await axios.delete(route('api.units.destroy', {unit: unitId}));
                this.deleteUnitDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllUnits();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },
        editUnit: async function () {
            //Verify request
            if (this.editedUnit.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'alert', 2000);
                return;
            }
            let data = this.editedUnit.toObjectRequest();
            console.log(data);
            try {
                let request = await axios.patch(route('api.units.update', {'unit': this.editedUnit.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllUnits();
                //Clear role information
                this.editedUnit = new Unit();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        createUnit: async function () {
            if (this.newUnit.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            let data = this.newUnit.toObjectRequest();

            try {
                let request = await axios.post(route('api.units.store'), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                this.getAllUnits();
                this.newUnit = new Unit();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 3000);
            }
        },

        getAllUnits: async function () {
            let request = await axios.get(route('api.units.index'));
            this.units = request.data;
        },
        setUnitDialogToCreateOrEdit(which, item = null) {
            if (which === 'create') {
                this.createOrEditDialog.method = 'createUnit';
                this.createOrEditDialog.model = 'newUnit';
                this.createOrEditDialog.dialogStatus = true;
            }
            if (which === 'edit') {
                this.editedUnit = Unit.fromModel(item);
                this.createOrEditDialog.method = 'editUnit';
                this.createOrEditDialog.model = 'editedUnit';
                this.createOrEditDialog.dialogStatus = true;
            }
        },
    },


}
</script>
