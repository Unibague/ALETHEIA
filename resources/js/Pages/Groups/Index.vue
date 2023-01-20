<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar grupos</h2>
                <div>
                    <v-bottom-sheet v-model="sheet">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn
                                class="mr-3"
                                color="red"
                                dark
                                v-bind="attrs"
                                v-on="on"
                            >
                                Otras opciones
                            </v-btn>
                        </template>
                        <v-list>
                            <v-subheader>Menú de otras opciones</v-subheader>
                            <v-list-item
                                @click="getGroupsWithoutTeacher"
                            >
                                <v-list-item-avatar>

                                    <v-icon>
                                        mdi-account-alert
                                    </v-icon>

                                </v-list-item-avatar>
                                <v-list-item-title>Ver grupos sin docente</v-list-item-title>
                            </v-list-item>
                            <v-list-item
                                @click="getAllGroups(true)"
                            >
                                <v-list-item-avatar>
                                    <v-avatar
                                        size="32px"
                                        tile
                                    >
                                        <v-icon>
                                            mdi-account-multiple
                                        </v-icon>
                                    </v-avatar>
                                </v-list-item-avatar>
                                <v-list-item-title>Ver todos los grupos</v-list-item-title>
                            </v-list-item>

                        </v-list>
                    </v-bottom-sheet>

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncGroups"
                    >
                        Sincronizar grupos
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
                    :items="groups"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                    :item-class="getRowColor"

                >
                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <confirm-dialog
                :show="deleteGroupDialog"
                @canceled-dialog="deleteGroupDialog = false"
                @confirmed-dialog="deleteGroup(deletedGroupId)"
            >
                <template v-slot:title>
                    Suspender la sincronización del usuario {{ editedGroup.name }}
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
import Group from "@/models/Group";
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
            search: '',
            //Table info
            headers: [
                {text: 'Periodo académico', value: 'academic_period.name'},
                {text: 'Código de asignatura', value: 'class_code'},
                {text: 'Nombre', value: 'name'},
                {text: 'Número de grupo', value: 'group'},
                {text: 'Nivel de formación', value: 'degree'},
                {text: 'Area de servicio', value: 'service_area.name'},
                {text: 'Profesor', value: 'teacher.name'},
                {text: 'Tipo de hora grupo', value: 'hour_type'},
            ],
            groups: [],
            //Groups models
            newGroup: new Group(),
            editedGroup: new Group(),
            deletedGroupId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            sheet:false,
            deleteGroupDialog: false,
            createOrEditDialog: {
                model: 'newGroup',
                method: 'createGroup',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllGroups();
        this.isLoading = false;
    },

    methods: {
        getGroupsWithoutTeacher: async function () {
            try {
                let request = await axios.get(route('api.groups.withoutTeacher'));
                showSnackbar(this.snackbar, 'Se han cargado los grupos sin docentes', 'success');
                this.groups = request.data;
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        syncGroups: async function () {
            try {
                let request = await axios.post(route('api.groups.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllGroups();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },
        changeGroupStatus: async function (group, status) {
            try {
                let request = await axios.post(route('api.groups.changeStatus', {group: group.id}), {
                    status
                });
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllGroups();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }
        },

        getAllGroups: async function (showMessage = false) {
            let request = await axios.get(route('api.groups.index'));
            this.groups = request.data;
            if(showMessage){
                showSnackbar(this.snackbar, 'Se han cargado todos los grupos', 'success')
            }
        },

        getRowColor: function (item) {
            return item.status === 'activo' ? 'green lighten-5' : item.status === 'suspendido' ? 'red lighten-5' : '';
        },
    },


}
</script>
