<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Sincronizar grupos por periodo</h2>
                <div>
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
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="groups"
                :items-per-page="20"
                class="elevation-1"
                :item-class="getRowColor"

            >
            </v-data-table>
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

        syncGroups: async function () {
            try {
                let request = await axios.post(route('api.groups.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllAcademicPeriods();
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

        getAllGroups: async function () {
            let request = await axios.get(route('api.groups.index'));
            this.groups = request.data;
        },

        getRowColor: function (item) {
            return item.status === 'activo' ? 'green lighten-5' : item.status === 'suspendido' ? 'red lighten-5' : '';
        },
    },


}
</script>
