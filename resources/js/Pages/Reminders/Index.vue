<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Editar Notificaciones de Evaluación 360</h2>
<!--                <div>
                    <v-btn
                        color="primario"
                        class="grey&#45;&#45;text text&#45;&#45;lighten-4"
                        @click="syncEnrolls"
                    >

                    </v-btn>
                </div>-->
            </div>

            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="reminders"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"

                    class="elevation-1"

                >


                    <template v-slot:item.actions="{ item }">

                        <v-icon
                            class="mr-2 primario--text"
                            @click="editReminderDialog(item)"
                        >
                            mdi-pencil
                        </v-icon>


                    </template>




                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

        </v-container>


        <v-dialog
            v-model="editReminder"
            persistent
            max-width="350"
        >
            <v-card>
                <v-card-title class="text-h5">
                </v-card-title>
                <v-card-text>

                    {{this.reminderToEdit.reminder_name}}

                    <v-text-field
                        color="primario"
                        v-model="reminderToEdit.days_in_advance"
                        label="Selecciona los días de antelación para enviar el correo"
                        type="number"
                        min=1
                        max="60"
                        class="mt-3"
                    >

                    </v-text-field>

                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="updateReminder()"
                    >
                        Aceptar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

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

            pagination: {
                current: 1,
                total: 0
            },
            //Table info
            search: '',
            headers: [
                {text: 'Nombre', value: 'reminder_name'},
                {text: 'Días de antelación', value: 'days_in_advance'},
                {text: 'Editar', value: 'actions'},

            ],
            enrolls: [],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            createOrEditDialog: {
                model: 'newTeacher',
                method: 'createTeacher',
                dialogStatus: false,
            },
            isLoading: true,
            reminders:[],
            editReminder: false,
            reminderToEdit: [],
            selectedReminderDays: '',
        }
    },
    async created() {
        await this.getAllEnrolls();
        this.isLoading = false;
    },

    methods: {

        getAllEnrolls: async function () {
            let request = await axios.get(route('reminders.get'));
            this.reminders = request.data;
        },

        updateReminder: async function(){

            console.log(this.reminderToEdit);

            let request = await axios.post(route('reminders.update'), {reminderToEdit: this.reminderToEdit});

            this.editReminder = false;

            this.getAllEnrolls();


            showSnackbar(this.snackbar, request.data.message, 'success');



        },

        editReminderDialog(item){

            this.editReminder = true;
            this.selectedReminderDays = item.days_in_advance
            console.log(item);
            this.reminderToEdit = item;

        }
    },
}
</script>
