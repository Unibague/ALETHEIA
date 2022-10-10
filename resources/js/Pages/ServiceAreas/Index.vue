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
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headers"
                :items="serviceAreas"
                :items-per-page="15"
                class="elevation-1"
            >
            </v-data-table>
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
            //Table info
            headers: [
                {text: 'Nombre', value: 'name'},
                {text: 'Código', value: 'code'},
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
            this.serviceAreas = request.data;
        },

    },


}
</script>
