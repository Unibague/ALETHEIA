<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar docentes</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncTeachers"
                    >
                        Sincronizar docentes
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="teachers"
                :items-per-page="20"
                class="elevation-1"
                :item-class="getRowColor"

            >
                <template v-slot:item.type="{ item }">
                    {{ item.is_custom ? 'Personalizada' : 'Integración' }}
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon
                        v-if="item.status === 'suspendido'"
                        class="mr-2 primario--text"
                        @click="changeTeacherStatus(item,'activo')"
                    >
                        mdi-check
                    </v-icon>

                    <v-icon
                        v-if="item.status === 'activo'"
                        class="mr-2 primario--text"
                        @click="changeTeacherStatus(item,'suspendido')"
                    >
                        mdi-close
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <confirm-dialog
                :show="deleteTeacherDialog"
                @canceled-dialog="deleteTeacherDialog = false"
                @confirmed-dialog="deleteTeacher(deletedTeacherId)"
            >
                <template v-slot:title>
                    Suspender la sincronización del usuario {{ editedTeacher.name }}
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
import Teacher from "@/models/Teacher";
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
                {text: 'Nombre', value: 'user.name'},
                {text: 'Documento', value: 'identification_number'},
               // {text: 'Dependencia', value: 'unity'},
                {text: 'Cargo', value: 'position'},
                {text: 'Escalafón', value: 'teaching_ladder'},
                {text: 'Tipo empleado', value: 'employee_type'},
                {text: 'Estado', value: 'status'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            teachers: [],
            //Teachers models
            newTeacher: new Teacher(),
            editedTeacher: new Teacher(),
            deletedTeacherId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteTeacherDialog: false,
            createOrEditDialog: {
                model: 'newTeacher',
                method: 'createTeacher',
                dialogStatus: false,
            },
            isLoading: true,
        }
    },
    async created() {
        await this.getAllTeachers();
        this.isLoading = false;
    },

    methods: {

        syncTeachers: async function () {
            try {
                console.log('entre')
                let request = await axios.post(route('api.teachers.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllTeachers();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        changeTeacherStatus: async function (teacher, status) {
            try {
                let request = await axios.post(route('api.teachers.changeStatus', {teacher: teacher.id}), {
                    status
                });
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllTeachers();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }
        },

        getAllTeachers: async function () {
            let request = await axios.get(route('api.teachers.index'));
            this.teachers = request.data;
        },

        getRowColor: function (item) {
            return item.status === 'activo' ? 'green lighten-5' : item.status === 'suspendido' ? 'red lighten-5' : '';
        },
    },


}
</script>
