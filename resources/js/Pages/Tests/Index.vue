<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Evaluaciones</h2>
            </div>

            <!--Inicia tabla-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="tests"
                :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                class="elevation-1"
            >
                <template v-slot:item.actions="{ item }">

                    <v-tooltip bottom v-if="item.test === null">
                        <template v-slot:activator="{ on, attrs }">

                            <v-icon
                                v-on="on"
                                v-bind="attrs"
                                class="mr-2 primario--text"
                            >
                                mdi-close-circle-outline
                            </v-icon>
                        </template>
                        <span>No hay una evaluación disponible para este grupo</span>
                    </v-tooltip>


                    <form :action="route('tests.startTest',{testId: item.test.id})" method="POST"
                          v-else-if="item.pivot.has_answer === 0">
                        <input type="hidden" name="_token" :value="token">
                        <input type="hidden" name="data" :value="JSON.stringify(item)">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">

                                <v-btn
                                    type="submit"
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 primario--text"
                                >
                                    <v-icon>
                                        mdi-send
                                    </v-icon>
                                </v-btn>
                            </template>
                            <span>Realizar evaluación</span>
                        </v-tooltip>
                    </form>

                    <v-tooltip bottom v-else>
                        <template v-slot:activator="{ on, attrs }">

                            <v-icon
                                v-on="on"
                                v-bind="attrs"
                                class="mr-2 primario--text"
                            >
                                mdi-check-all
                            </v-icon>
                        </template>
                        <span>Ya has realizado esta evaluación</span>

                    </v-tooltip>
                </template>
            </v-data-table>

            <!--Acaba tabla-->
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {getCSRFToken, prepareErrorText, showSnackbar} from "@/HelperFunctions"
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
                {text: 'Grupo', value: 'group'},
                {text: 'Nombre', value: 'name'},
                {text: 'Profesor', value: 'teacher.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            tests: [],
            //token: '',
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            isLoading: true,
        }
    },
    props: {
        token: String
    },
    async created() {
        await this.getAllGroups();
        this.isLoading = false;
    },


    methods: {

        applyForTestStarting: async function () {
            let request = await axios.post(route('api.tests.index'));
            this.tests = request.data;
        },
        getAllGroups: async function () {
            let request = await axios.get(route('api.tests.index'));
            this.tests = request.data;
        },

    },


}
</script>
