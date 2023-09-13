<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>

            <div class="d-flex flex-column align-end mb-4">
                <h3 class="align-self-start">Definir ideales de respuesta por facultad</h3>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="createResponseIdeal"
                    >
                        Agregar nuevo ideal de respuesta
                    </v-btn>
                </div>
            </div>

                <!--Inicia tabla para mostrar todos los ideales de respuesta-->
                <v-card class="mt-5">
                    <v-card-title>
                        <v-text-field
                            v-model="search"
                            append-icon="mdi-magnify"
                            label="Filtrar por Facultad o Escalafón"
                            single-line
                            hide-details
                        ></v-text-field>
                    </v-card-title>
                    <v-data-table
                        :search="search"
                        loading-text="Cargando, por favor espere..."
                        :loading="isLoading"
                        :headers="headers"
                        :items="facultiesTeachingLadders"
                        :items-per-page="20"
                        :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                        class="elevation-1"
                    >
                        <template v-slot:item.name="{ item }">
                            {{ capitalize(item.name) }}
                        </template>

                        <template v-slot:item.teaching_ladder="{ item }">
                            {{ capitalize(item.teaching_ladder) }}
                        </template>

                        <template v-slot:item.c1="{ item }">
                            {{ item.response[0].value }}
                        </template>

                        <template v-slot:item.c2="{ item }">
                            {{ item.response[1].value }}
                        </template>

                        <template v-slot:item.c3="{ item }">
                            {{ item.response[2].value }}
                        </template>

                        <template v-slot:item.c4="{ item }">
                            {{ item.response[3].value }}
                        </template>

                        <template v-slot:item.c5="{ item }">
                            {{ item.response[4].value }}
                        </template>

                        <template v-slot:item.c6="{ item }">
                            {{ item.response[5].value }}
                        </template>

                        <template v-slot:item.actions="{ item }">

                            <v-tooltip top>
                                <template v-slot:activator="{on,attrs}">

                                    <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        class="mr-2 primario--text"
                                        @click="editResponseIdeals(item)"
                                    >
                                        mdi-pencil
                                    </v-icon>

                                </template>
                                <span>Editar ideales</span>
                            </v-tooltip>

                            <v-tooltip top>
                                <template v-slot:activator="{on,attrs}">

                                    <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        class="mr-2 primario--text"
                                        @click="confirmDeleteResponseIdeals(item)"
                                    >
                                        mdi-delete
                                    </v-icon>

                                </template>
                                <span>Borrar ideales</span>
                            </v-tooltip>
                        </template>
                    </v-data-table>
                </v-card>
                <!--Acaba tabla-->
        </v-container>


        <!--Modal para editar ideales de respuesta-->
        <v-dialog
            v-model="editDialog"
            persistent
        >
            <v-card class="py-4 px-4">

                <v-card-title v-if="Object.keys(infoFromEditTeachingLadder).length>0">
                    <h4 style="margin: auto"> Editando ideales de respuesta para el escalafón: <strong> {{capitalize(infoFromEditTeachingLadder.teaching_ladder)}} </strong>
                        perteneciente a: <strong> {{capitalize(infoFromEditTeachingLadder.name)}} </strong> </h4>
                </v-card-title>


                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headersEditTeachingLadder"
                    :items="competencesToEdit"
                    :hide-default-footer="true"
                >

                    <template v-slot:item.value="{ item }">
                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <v-text-field
                                    label="Valor de la competencia"
                                    v-model="competencesToEdit[item.id].value"
                                    single-line
                                    style="text-align: center"
                                    type="number"
                                    min=1
                                    max="5"
                                    :rules="numberRules"
                                    step="0.1"
                                >
                                </v-text-field>
                            </template>
                        </v-tooltip>
                    </template>
                </v-data-table>

                <v-card-actions>
                    <v-btn
                        color="primario"
                        class="white--text"
                        @click="editDialog = false"
                    >
                        Salir
                    </v-btn>

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="updateResponseIdeals(competencesToEdit)"
                    >
                        Guardar Cambios
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!--Modal para crear nuevos ideales de respuesta-->
        <v-dialog
            v-model="createDialog"
            persistent
            max-width="50%"
        >
            <v-card class="py-4 px-4">

                <v-card-title>
                    <h3 style="margin: auto"> Creando nuevos ideales de respuesta </h3>
                </v-card-title>
                <v-row style="max-width: 80%; margin: auto" >
                    <v-col cols="6">
                        <v-select
                            color="primario"
                            v-model="selectedFaculty"
                            :items="faculties"
                            label="Selecciona la facultad"
                            :item-value="(faculty)=>faculty.unit_identifier"
                            :item-text="(faculty)=>faculty.name"
                        ></v-select>
                    </v-col>
                    <v-col cols="6" class="d-flex justify-center">
                        <v-select
                            color="primario"
                            v-model="selectedTeachingLadder"
                            :items="teachingLadders"
                            label="Selecciona el escalafón"
                            :item-value="(teachingLadder)=>teachingLadder.name"
                            :item-text="(teachingLadder)=>teachingLadder.name"
                        ></v-select>
                    </v-col>
                </v-row>
                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headersEditTeachingLadder"
                    :items="competencesForNewResponseIdeal"
                    :hide-default-footer="true"
                    style="margin: auto; max-width: 70%"
                >

                    <template v-slot:item.value="{ item }">
                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <v-text-field
                                    label="Valor de la competencia"
                                    v-model="competencesForNewResponseIdeal[item.id].value"
                                    single-line
                                    style="text-align: center"
                                    type="number"
                                    min=1
                                    max="5"
                                    :rules="numberRules"
                                    step="0.1"
                                >
                                </v-text-field>
                            </template>
                        </v-tooltip>
                    </template>
                </v-data-table>

                <v-card-actions class="mt-5">

                    <v-btn
                        color="primario"
                        class="white--text"
                        @click="cancelCreateResponseIdeal"
                    >
                        Salir
                    </v-btn>

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="saveNewResponseIdeals(competencesForNewResponseIdeal)"
                    >
                        Guardar Cambios
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>


<!--        Cuadro de confirmacion para sobreescribir-->
        <confirm-dialog
            :show="confirmSaveExistingResponseIdeal"
            @canceled-dialog="confirmSaveExistingResponseIdeal = false"
            @confirmed-dialog="saveNewResponseIdeals(competencesForNewResponseIdeal, 'confirmation')"
        >
            <template v-slot:title>
                Ya existe un ideal de respuesta asociado a este escalafón en esta facultad <br> ¿deseas sobreescribirlo?
            </template>

            <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

            <template v-slot:confirm-button-text>
                Sobreescribir
            </template>
        </confirm-dialog>

        <!--Modal para borrar ideales de respuesta-->
        <confirm-dialog
            :show="deleteDialog"
            @canceled-dialog="deleteDialog = false"
            @confirmed-dialog="deleteResponseIdeals()"
        >
            <template v-slot:title>
                Estas a punto de eliminar el ideal de respuesta
            </template>

            ¡Cuidado! esta acción es irreversible

            <template v-slot:confirm-button-text>
                Borrar
            </template>
        </confirm-dialog>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Chart from "chart.js/auto";
import Snackbar from "@/Components/Snackbar";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar
    },

    data: () => {
        return {
            //Table info
            headers: [
                {text: 'Facultad', value: 'name', width:'15%'},
                {text: 'Escalafon', value: 'teaching_ladder', width:'15%'},
                {text: 'C1', value: 'c1'},
                {text: 'C2', value: 'c2'},
                {text: 'C3', value: 'c3', sortable: false},
                {text: 'C4', value: 'c4', sortable: false},
                {text: 'C5', value: 'c5', sortable: false},
                {text: 'C6', value: 'c6', sortable: false},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],


            headersEditTeachingLadder: [
                {text: 'Competencia', value: 'name'},
                {text: 'Valor', value: 'value', margin:'auto'},
            ],


            search: '',
            facultiesTeachingLadders: [],
            unit: [],
            teachingLadders: [],
            responseIdeals:[],
            dataToGraph: [],
            chart:'',
            datasets:[],
            competencesValuesAsArray:[],
            competencesValuesArray:[],
            competencesToEdit: [],
            competencesForNewResponseIdeal: [],
            responseIdealsToDelete: [],
            editDialog: false,
            createDialog: false,
            deleteDialog: false,
            confirmSaveExistingResponseIdeal: false,
            infoFromEditTeachingLadder:[],
            faculties: [],
            selectedFaculty: [],
            selectedTeachingLadder: [],
            //Snackbars
            snackbar: {
            text: "",
            color: 'red',
            status: false,
            timeout: 2000,
        },


            //Overlays
            isLoading: true,


            numberRules: [
                v => !!v || 'El valor introducido debe ser un número',
            ]
        }
    },
    async created() {
        await this.getSuitableTeachingLadders()
        await this.getFaculties();
        await this.getAllResponseIdeals()
        /* this.getGraph()*/
        this.isLoading = false;

    },
    methods: {


        setCompetencesForNewResponseIdeal() {
            let competencesName = ['Orientación a la calidad educativa',
                'Trabajo Colaborativo',
                'Empatía Universitaria',
                'Comunicación',
                'Innovación del conocimiento',
                'Productividad Académica'];

            for (let i = 0; i < 6; i++) {

                this.competencesForNewResponseIdeal.push({

                    competence: `C${i + 1}`,
                    id: i,
                    name: competencesName[i],
                    value: ''
                })
            }
        },

        getSuitableTeachingLadders: async function () {

            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));
            let teachingLadders = request.data

            teachingLadders.forEach(teachingLadder => {
                if (teachingLadder == 'AUX') {

                    this.teachingLadders.unshift({
                        name: 'Auxiliar',
                        identifier: teachingLadder
                    })
                }

                if (teachingLadder == 'ASI') {
                    this.teachingLadders.unshift({
                        name: 'Asistente',
                        identifier: teachingLadder
                    })
                }

                if (teachingLadder == 'ASO') {
                    this.teachingLadders.unshift({
                        name: 'Asociado',
                        identifier: teachingLadder
                    })
                }

                if (teachingLadder == 'TIT') {
                    this.teachingLadders.unshift({
                        name: 'Titular',
                        identifier: teachingLadder
                    })
                }
            })
        },


        async getFaculties() {

            let url = route('unit.getFaculties');
            let request = await axios.get(url);
            this.faculties = request.data
            console.log(this.faculties, 'faculties')
        },


        async getAllResponseIdeals() {

            let url = route('responseIdeals.get');
            let request = await axios.get(url);
            this.facultiesTeachingLadders = request.data;
            console.log(this.facultiesTeachingLadders, 'responseIdealsFromEndPoint');

        },


        setDatasets() {


            console.log(this.responseIdeals, 'jfjwfejfejwefj');

            this.responseIdeals.forEach(responseIdeal => {

                let hex = this.randomHexColor()

                if (responseIdeal.teaching_ladder === 'Ninguno') {
                    responseIdeal.teaching_ladder = 'Sin Escalafón'
                }

                this.datasets.push({

                    label: responseIdeal.teaching_ladder,
                    data: this.fillCompetencesArray(responseIdeal.response),
                    backgroundColor: hex,
                    borderColor: hex,
                    borderWidth: 2,
                    borderDash: [5, 5]
                })

            })
            console.log(this.datasets)
            return this.datasets

        },


        fillCompetencesArray(competencesValuesAsArrayOfObjects) {

            let array = []

            competencesValuesAsArrayOfObjects.forEach(competenceValue => {
                array.push(competenceValue.value)
            })

            return array
        },

        getGraph() {

            let miCanvas = document.getElementById("MiGrafica").getContext("2d");
            this.chart = new Chart(miCanvas, {
                type: "line",
                data: {
                    labels: ["C1", "C2", "C3", "C4", "C5", "C6"],
                    datasets: this.setDatasets(),
                },
                options: {
                    legend: {
                        display: false
                    },
                    responsive: true,
                    tooltips: {
                        mode: "index",
                        intersect: false
                    },
                    hover: {
                        mode: "nearest",
                        intersect: true
                    },
                    scales: {
                        x:
                            {
                                title: {
                                    display: true,
                                    text: 'Competencias'
                                },
                                position: "top",
                                ticks: {
                                    padding: 8,
                                }
                            }
                        ,
                        y:
                            {
                                max: 5,
                                title: {
                                    display: true,
                                    text: 'Nivel Esperado'
                                },

                                ticks: {
                                    beginAtZero: true,
                                    padding: 8,
                                    stepSize: 0.5,
                                }
                            }
                    }
                }
            })
        },

        capitalize($field) {
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        async editResponseIdeals(item) {

            console.log(item, "jejrejrjejerjrej")
            this.unit = item.unit_identifier;
            this.infoFromEditTeachingLadder = item;
            let request = await axios.get(route('unit.teachingLadder.responseIdeals.get', {
                unitId: item.unit_identifier,
                teachingLadder: item.teaching_ladder
            }));

            this.competencesToEdit = request.data
            this.updateTeachingLadder = item.teaching_ladder;
            console.log(this.competencesToEdit, 'competences to editttttt')
            this.editDialog = true
        },


        async createResponseIdeal() {
            this.setCompetencesForNewResponseIdeal();
            this.createDialog = true
        },



        cancelCreateResponseIdeal (){
            this.createDialog = false
            this.competencesForNewResponseIdeal = [];
        },

        async updateResponseIdeals(competences) {

            try {

                console.log(competences, "JCEJRIWERJCIWERJCWIEJRCWRJE")
                let url = route('responseIdeals.update');
                let request = await axios.post(url, {
                    competences: competences,
                    teachingLadder: this.updateTeachingLadder, unit: this.unit
                });

                this.competencesValuesArray.length = 0;
                this.fillArrayWithCompetencesValues(competences)
                this.editDialog = false;
                await this.getAllResponseIdeals();
                showSnackbar(this.snackbar, request.data.message, 'success');

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },


        async saveNewResponseIdeals(competences, confirmation) {

            try {

                let url = route('responseIdeals.update');
                console.log(confirmation, 'ffwkefwkefkwefkwkefkwfe');

                if (confirmation !== null) {

                    let request = await axios.post(url, {
                        competences: competences,
                        teachingLadder: this.selectedTeachingLadder,
                        unit: this.selectedFaculty,
                        confirmation: confirmation
                    });

                    this.confirmSaveExistingResponseIdeal = false
                    await this.getAllResponseIdeals();
                    this.createDialog = false;
                    showSnackbar(this.snackbar, request.data.message, 'success');
                }


                else {

                    let request = await axios.post(url, {
                        competences: competences,
                        teachingLadder: this.selectedTeachingLadder, unit: this.selectedFaculty
                    });
                    await this.getAllResponseIdeals();
                    this.createDialog = false;
                    showSnackbar(this.snackbar, request.data.message, 'success');
                }

                this.competencesForNewResponseIdeal = [];
                this.selectedFaculty = '';
                this.selectedTeachingLadder = '';

                /*this.updateGraph(this.competencesValuesArray);*/

            } catch (e) {
              /*  showSnackbar(this.snackbar, prepareErrorText(e), 'alert');*/
               this.createDialog = false;
                this.confirmSaveExistingResponseIdeal = true

            }
        },


        confirmDeleteResponseIdeals(item){
            this.responseIdealsToDelete = item;
            this.deleteDialog = true;
        },

        async deleteResponseIdeals (){

            try{

                this.unit = this.responseIdealsToDelete.unit_identifier;
                this.updateTeachingLadder = this.responseIdealsToDelete.teaching_ladder;
                let url = route('responseIdeals.delete');
                let request = await axios.post(url, {teachingLadder: this.updateTeachingLadder, unit: this.unit});
                this.deleteDialog = false;
                await this.getAllResponseIdeals();
                showSnackbar(this.snackbar, request.data.message, 'success');

            }

            catch (e){
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        fillArrayWithCompetencesValues(competences){

            competences.forEach(competence =>{
                this.competencesValuesArray.push(competence.value);
            })

        },
},


}
</script>
