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

<!--                            <v-list-item-->
<!--                                @click="deleteThoseGroups"-->
<!--                            >-->
<!--                                <v-list-item-avatar>-->
<!--                                    <v-avatar-->
<!--                                        size="32px"-->
<!--                                        tile-->
<!--                                    >-->
<!--                                        <v-icon>-->
<!--                                            mdi-delete-->
<!--                                        </v-icon>-->
<!--                                    </v-avatar>-->
<!--                                </v-list-item-avatar>-->
<!--                                <v-list-item-title>Borrar esos grupos</v-list-item-title>-->
<!--                            </v-list-item>-->


                        </v-list>
                    </v-bottom-sheet>
                    <v-btn
                        class="mr-3"
                        @click="syncEnrolls"
                        :disabled="isSync"
                    >
                        Cargar estudiantes
                    </v-btn>

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncGroups"
                    >
                        Sincronizar grupos
                    </v-btn>
                </div>
            </div>



            <div class="d-flex flex-column align-center mt-12" v-if="">
                <h3 v-if="isSync">
                    Por favor espera, estamos realizando la sincronización de los estudiantes...
                </h3>
            </div>

            <!--Inicia tabla-->

            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar grupos"
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

                >
                    <template v-slot:item.actions="{ item }">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                                <InertiaLink
                                    as="v-icon"
                                    v-on="on"
                                    v-bind="attrs"
                                    class="mr-2 primario--text"
                                    :href="route('groups.enrolls.view',{ groupId: item.group_id})"
                                >
                                    mdi-account-group
                                </InertiaLink>
                            </template>
                            <span>Ver estudiantes</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->
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
                {text: 'ID', value: 'group_id'},
                {text: 'Nombre', value: 'name'},
                {text: 'Número de grupo', value: 'group'},
                {text: 'Nivel de formación', value: 'degree'},
                {text: 'Area de servicio', value: 'service_area.name'},
                {text: 'Profesor', value: 'teacher.name'},
                {text: 'Tipo de hora', value: 'hour_type'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            groups: [],
            //Groups models

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            sheet: false,
            isLoading: true,
            isSync: false
        }
    },
    async created() {
        await this.getAllGroups();
        this.isLoading = false;
    },

    methods: {
        syncEnrolls: async function () {
            try {
                this.isSync = true;
                let request = await axios.post(route('api.enrolls.sync'));
                this.isSync = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        // deleteThoseGroups: async function () {
        //     try {
        //         let request = await axios.post(route('api.enrolls.deleteThoseGroups'));
        //         showSnackbar(this.snackbar, 'Borrados correctamente', 'success');
        //     } catch (e) {
        //         showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
        //     }
        // },

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
            if (showMessage) {
                showSnackbar(this.snackbar, 'Se han cargado todos los grupos', 'success')
            }
        },
    },
}
</script>
