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
                class="elevation-1"
            >
                <template v-slot:item.actions="{ item }">
                    <InertiaLink
                        as="v-icon"
                        v-if="item.has_answer"
                        class="mr-2 primario--text"
                    >
                        mdi-send
                    </InertiaLink>
                    <v-icon
                        v-else
                        class="mr-2 primario--text"
                    >
                        mdi-check-all
                    </v-icon>
                </template>
            </v-data-table>
            <!--Acaba tabla-->
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
                {text: 'Nombre', value: 'name'},
                {text: 'Profesor', value: 'teacher.name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            tests: [],

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
    async created() {
        await this.getAllGroups();
        this.isLoading = false;
    },

    methods: {
        getAllGroups: async function () {
            let request = await axios.get(route('api.tests.index'));
            this.tests = request.data;
        },

    },


}
</script>
