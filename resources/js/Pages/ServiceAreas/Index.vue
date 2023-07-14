<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar areas de servicio</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="syncServiceAreas"
                    >
                        Sincronizar
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
                    :items="serviceAreas"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.actions="{ item }">
                    <v-tooltip top>
                        <template v-slot:activator="{on,attrs}">

                            <InertiaLink :href="route('serviceAreas.manageServiceArea', {serviceAreaCode:item.code})">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                >
                                    mdi-account-group
                                </v-icon>

                            </InertiaLink>

                        </template>
                        <span>Gestionar Usuarios</span>
                    </v-tooltip>
                    </template>

                </v-data-table>







            </v-card>
            <!--Acaba tabla-->
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import Snackbar from "@/Components/Snackbar";

export default {
    components: {
        AuthenticatedLayout,
        Snackbar,
    },
    data: () => {
        return {
            search: '',
            //Table info
            headers: [
                {text: 'Nombre', value: 'name'},
                {text: 'Código', value: 'code'},
                {text: 'Gestionar', value: 'actions'},
            ],
            serviceAreas: [],
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
        await this.getAllServiceAreas();
        this.isLoading = false;
    },

    methods: {
        syncServiceAreas: async function () {
            try {
                let request = await axios.post(route('api.serviceAreas.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getAllServiceAreas();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },
        getAllServiceAreas: async function () {
            let request = await axios.get(route('api.serviceAreas.index'));
            console.log(request.data);
            this.serviceAreas = request.data;
        },

    },


}
</script>
