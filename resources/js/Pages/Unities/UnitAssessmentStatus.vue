<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <h3 class="align-self-start mb-5" > Estado de evaluación de la unidad: {{this.unit.name}}</h3>
            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headers"
                    :items="teachers"
                    :items-per-page="10"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.peer="{ item }">
                        {{item.peer ? item.peer : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.peer !== '' && item.peerPending === 0)"
                        >
                            mdi-check-bold
                        </v-icon>

                        <v-icon
                            color="red"
                            v-else
                        >
                            mdi-close-thick
                        </v-icon>
                    </template>

                    <template v-slot:item.boss="{ item }">
                        {{item.boss ? item.boss : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.boss !=='' && item.bossPending === 0 )"
                        >
                            mdi-check-bold
                        </v-icon>

                        <v-icon
                            color="red"
                            v-else
                        >
                            mdi-close-thick
                        </v-icon>
                    </template>


                    <template v-slot:item.auto="{ item }">
                        <v-icon
                            color="red"
                            v-if="(item.autoPending === 1)"
                        >
                            mdi-close-thick
                        </v-icon>
                        <v-icon
                            color="green"
                            v-else
                        >
                            mdi-check-bold
                        </v-icon>
                    </template>
                </v-data-table>
            </v-card>
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import Unit from "@/models/Unit";
import Snackbar from "@/Components/Snackbar";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },

    props: {
        unit: Object
    },

    data: () => {
        return {
            //Table info
            teachers: [],
            headers: [
                {text: 'Nombre', value: 'name', align: 'center'},
                {text: 'Jefe', value: 'boss'},
                {text: 'Par', value: 'peer'},
                {text: 'Autoevaluación', value: 'auto'}
            ],
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
        await this.getTeachersAssessments()
    },

    methods:{
        async getTeachersAssessments(){
            let request = await axios.get(route('api.unit.assessments', {unit:this.unit.identifier}));
            console.log(request.data);
            this.teachers = request.data;
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }
    }
}
</script>

