<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Estudiantes matriculados en {{group.name}} ({{group.class_code}}) - Grupo {{group.group}}</h2>
                <div>
                    <InertiaLink
                        as="v-btn"
                        color="primario"
                        class="grey--text text--lighten-4"
                        :href="route('groups.index.view')"
                    >
                        Volver a todos los grupos
                    </InertiaLink>
                </div>
            </div>
            <!--Inicia tabla-->

            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar estudiantes"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="group.enrolls"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"

                >
                    <template v-slot:item.pivot.has_answer="{ item }">
                        {{item.pivot.has_answer  === 0 ? 'Pendiente' : 'Diligenciada'}}
                    </template>

                </v-data-table>
            </v-card>
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
            search: '',
            //Table info
            headers: [
                {text: 'Nombre del estudiante', value: 'name'},
                {text: 'Correo electrónico', value: 'email'},
                {text: 'Evaluación', value: 'pivot.has_answer'},
            ],
            group: [],
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
        }
    },
    async created() {
        await this.getGroupEnrolls();
        this.isLoading = false;
    },

    methods: {
        getGroupEnrolls: async function (showMessage = false) {
            let request = await axios.get(route('api.groups.enrolls', {groupId: route().params.groupId}));
            this.group = request.data;
            if (showMessage) {
                showSnackbar(this.snackbar, 'Se han cargado todos los grupos', 'success')
            }
        },

    },


}
</script>
