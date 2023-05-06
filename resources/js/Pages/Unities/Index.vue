<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-7">
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
                        @click="syncStaffMembers"
                    >
                        Sincronizar Funcionarios
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
                :items="filteredUnits"
                :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                class="elevation-1"
            >

                    <template v-slot:item.name="{ item }"  >

                        {{ item.name}}

                    </template>


                <template v-slot:item.type="{ item }"  >
                    {{ item.is_custom ? 'Personalizada' : 'Integración' }}
                </template>

                <template v-slot:item.users="{ item }">
                    {{ item.users.length }}
                </template>

                <template v-slot:item.actions="{ item }">

                    <v-tooltip top
                               v-if="item.is_custom"
                    >
                        <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="setUnitDialogToCreateOrEdit('edit',item)"
                                >
                                    mdi-pencil
                                </v-icon>

                        </template>
                        <span>Editar nombre de unidad personalizada</span>
                    </v-tooltip>


                    <v-tooltip top
                               v-if="item.is_custom"
                    >
                        <template v-slot:activator="{on,attrs}">

                            <v-icon
                                v-bind="attrs"
                                v-on="on"
                                class="mr-2 primario--text"
                                @click="confirmDeleteUnit(item)"
                            >
                                mdi-delete
                            </v-icon>

                        </template>
                        <span>Borrar unidad personalizada</span>
                    </v-tooltip>

                    <v-tooltip top>
                        <template v-slot:activator="{on,attrs}">

                            <InertiaLink :href="route('units.manageUnit', {unit:item.identifier})">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                >
                                    mdi-account-group
                                </v-icon>

                            </InertiaLink>

                        </template>
                        <span>Gestionar Unidad</span>
                    </v-tooltip>

                </template>
            </v-data-table>
            </v-card>
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

            suitableTeachers: [],
            teachers: [],
            search:'',
            headers: [
                {text: 'Nombre', value: 'name', align: 'center'},
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

    computed:{

      filteredUnits(){

          return this.units.filter(unit => {

              return unit.users.length>0 || unit.is_custom == 1;

          })

      }

    },

    async mounted(){


    },

    async created() {

        /*await this.assignTeachers();*/
        await this.getAllUnits();
        this.capitalize();
        this.isLoading = false;
    },

    methods: {

        /*assignTeachers: async function () {
            try {
                let request = await axios.post(route('api.units.assign'));

                showSnackbar(this.snackbar, request.data.message, 'success');
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },*/

        capitalize(){

            this.units.forEach((unit) => {

                unit.name = unit.name.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

            })

        },

        syncUnits: async function () {
            try {
                let request = await axios.post(route('api.units.sync'));
                console.log(request);
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getAllUnits();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        syncStaffMembers: async function () {

            try {
                let request = await axios.post(route('api.staffMembers.sync'));
                console.log(request);
                showSnackbar(this.snackbar, request.data.message, 'success');

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }


        },

        confirmDeleteUnit: function (unit) {
            this.deletedUnitId = unit.identifier;
            console.log(this.deletedUnitId);
            this.deleteUnitDialog = true;
        },
        deleteUnit: async function (unitId) {
            try {

                console.log(unitId);
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

                console.log(this.editedUnit);
                let request = await axios.patch(route('api.units.update', {'unit': this.editedUnit.code}),data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllUnits();
                //Clear role information
                this.editedUnit = new Unit();
             /*catch {
                showSnackbar(this.snackbar, prepareErrorText(), 'alert');
            }*/
        },

        createUnit: async function () {
            if (this.newUnit.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            let data = this.newUnit.toObjectRequest();
            console.log(data);

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

            this.units = request.data

            console.log(this.units)

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


