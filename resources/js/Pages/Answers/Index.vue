<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-5">
                <h2 class="align-self-start">Gestionar respuestas de formularios</h2>

            </div>

            <!--Inicia tabla-->
            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar registros"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="answers"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.actions="{ item }">

                        <InertiaLink as="v-icon" class="primario--text"
                                     :href="route('answers.show.view',{answer:item.id})">
                            mdi-file-search
                        </InertiaLink>

                    </template>

                </v-data-table>
            </v-card>

            <!------------Seccion de dialogos ---------->

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
            sheet: false,
            //Table info
            search: '',
            headers: [
                {text: 'Estudiante', value: 'studentName'},
                {text: 'Grupo', value: 'groupName'},
                {text: 'Profesor', value: 'teacherName'},
                {text: 'Promedio C1', value: 'first_competence_average'},
                {text: 'Promedio C2', value: 'second_competence_average'},
                {text: 'Promedio C3', value: 'third_competence_average'},
                {text: 'Promedio C4', value: 'fourth_competence_average'},
                {text: 'Promedio C5', value: 'fifth_competence_average'},
                {text: 'Promedio C6', value: 'sixth_competence_average'},
                {text: 'Fecha de envio', value: 'submitted_at'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],

            //Display data
            answers: [],

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
            isLoading: true,
        }
    },
    async created() {
        await this.getAllQuestions();
        this.isLoading = false;
    },

    methods: {
        getAllQuestions: async function (notify = false) {
            let request = await axios.get(route('api.answers.index'));
            this.answers = request.data;
            if (notify) {
                showSnackbar(this.snackbar, 'Mostrando todos los formularios')
            }
        },

    },

}
</script>
