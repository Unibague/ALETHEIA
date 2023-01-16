<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Carga académica</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="syncEnrolls"
                    >
                        Sincronizar carga académica
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <div class="text-center mb-5">
                <v-pagination
                    v-model="pagination.current"
                    :length="pagination.total"
                    @input="onPageChange"
                ></v-pagination>
            </div>
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="enrolls"
                :items-per-page="20"
                
                class="elevation-1"

            >
            </v-data-table>


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
            headers: [
                {text: 'Nombre estudiante', value: 'user.name'},
                {text: 'Grupo', value: 'group.name'},
                {text: 'Docente', value: 'group.teacher.name'},
                {text: 'Periodo académico', value: 'academic_period.name'},

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
        }
    },
    async created() {
        await this.getAllEnrolls();
        this.isLoading = false;
    },

    methods: {

        syncEnrolls: async function () {
            try {
                console.log('entre')
                let request = await axios.post(route('api.enrolls.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.getAllEnrolls();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },


        getAllEnrolls: async function () {
            let request = await axios.get(route('api.enrolls.index'), {
                params: {
                    page: this.pagination.current
                }
            });
            this.enrolls = request.data.data;
            this.pagination.current = request.data.current_page;
            this.pagination.total = request.data.last_page;
        },
        onPageChange() {
            this.getAllEnrolls();
        }

    },


}
</script>
