<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Definir ponderaciones de actores para evaluación 360°</h2>
            </div>

            <!--Inicia tabla-->
            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar por nombre de actor"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="assessmentWeights"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.actions="{ item }">
                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <v-icon
                                    class="mr-2 primario--text"
                                    @click="editDialog(item)"
                                >
                                    mdi-pencil
                                </v-icon>
                            </template>
                        </v-tooltip>
                    </template>

                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <v-dialog
                v-model="editAssessmentWeightDialog"
                persistent
                max-width="450"
            >
                <v-card>
                    <v-card-title class="text-h5">
                    </v-card-title>
                    <v-card-text>
                        Editando ponderación de evaluación para el actor <strong> {{this.selectedAssessmentWeight.actor}}</strong>
                        <v-text-field
                            color="primario"
                            v-model="newAssessmentWeight"
                            label="Define el nuevo porcentaje para el actor"
                            type="number"
                            min=1
                            max="100"
                            class="mt-3"
                        >
                        </v-text-field>
                        <span> Escribe únicamente el número, sin espacios ni símbolos (%)</span>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            class="white--text"
                            @click="editAssessmentWeightDialog = false"
                        >
                            Salir
                        </v-btn>

                        <v-btn
                            color="primario"
                            class="white--text"
                            @click="updateAssessmentWeight()"
                        >
                            Aceptar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
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
                {text: 'Nombre del actor', value: 'actor'},
                {text: 'Porcentaje (%)', value: 'weight'},
                {text: 'Editar', value: 'actions', width: '20%'},
            ],
            assessmentWeights: [],
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            editAssessmentWeightDialog: false,
            selectedAssessmentWeight: [],
            newAssessmentWeight: [],
            isLoading: true,
        }
    },
    async created() {
        await this.getAssessmentWeights();
        this.isLoading = false;
    },

    methods: {

        async getAssessmentWeights(){

          let request = await axios.get(route('api.assessmentWeights.index'))
            this.assessmentWeights = request.data;
        },

        editDialog(assessmentWeight){
            this.editAssessmentWeightDialog = true;
            this.selectedAssessmentWeight = assessmentWeight
            this.newAssessmentWeight = assessmentWeight.weight
        },

        async updateAssessmentWeight(){

            let data = {actor: this.selectedAssessmentWeight.actor, weight: this.newAssessmentWeight}
            try {
                let request = await axios.patch(route('api.assessmentWeights.update', this.selectedAssessmentWeight), data)
                this.editAssessmentWeightDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getAssessmentWeights();
            }

            catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        }
    },
}
</script>
