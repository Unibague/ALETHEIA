<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-5" v-if="this.autoAssessment.length>0">
                <h3 class="align-self-start">Autoevaluación </h3>
                <span class="light-blue--text"> Desde: {{user.autoAssessmentStartDate}} hasta: {{user.autoAssessmentEndDate}}</span>
            </div>

            <!--Tabla autoevaluacion-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headersAuto"
                :items="autoAssessment"
                :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                class="elevation-1"
                v-show="this.autoAssessment.length>0"
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
                        <span>No hay una autoevaluación disponible para este profesor</span>
                    </v-tooltip>


                    <form :action="route('tests.startTest',{testId: item.test.id})" method="POST"
                          v-else-if="item.pending === 1">
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

            <div class="d-flex flex-column align-end mt-8 mb-5" v-if="this.peerAssessments.length>0">
                <h3 class="align-self-start">Profesores a evaluar como par </h3>
                <span class="light-blue--text"> Desde: {{user.peerAssessmentStartDate}} hasta: {{user.peerAssessmentEndDate}}</span>
            </div>


            <!--Tabla pares-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headersPeers"
                :items="peerAssessments"
                :items-per-page="20"
                :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                class="elevation-1"
                v-show="peerAssessments.length>0"
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
                          v-else-if="item.pending === 1">
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


            <div class="d-flex flex-column align-end mt-8 mb-5" v-if="this.bossAssessments.length>0">
                <h3 class="align-self-start">Profesores a evaluar como jefe</h3>
                <span class="light-blue--text"> Desde: {{user.bossAssessmentStartDate}} hasta: {{user.bossAssessmentEndDate}}</span>
            </div>



            <!--Tabla jefes-->
            <v-data-table
                loading-text="Cargando, por favor espere..."
                :loading="isLoading"
                :headers="headersBoss"
                :items="bossAssessments"
                :items-per-page="20"
                :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                class="elevation-1"
                v-show="bossAssessments.length>0"
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
                          v-else-if="item.pending === 1">
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
            headersAuto: [
                {align: 'center'},
                {text: 'Nombre', value: 'name', width:'90%'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            headersPeers: [
                {align: 'center'},
                {text: 'Nombre', value: 'name', width:'90%'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            headersBoss: [
                {align: 'center'},
                {text: 'Nombre', value: 'name', width:'90%'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            tests: [],
            /*token: '',*/
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            isLoading: true,
            autoAssessment: [],
            peerAssessments: [],
            bossAssessments: [],
            user: {}
        }

    },

    props: {
        token: String
    },

    async created() {
        await this.getAutoAssessment();
        await this.getPeerAssessments();
        await this.getBossAssessments();
        this.isLoading = false;
    },


    methods: {

   /*     applyForTestStarting: async function () {
            let request = await axios.post(route('api.tests.index'));
            this.tests = request.data;
        },*/

        getAutoAssessment: async function() {
            let url = route('tests.index.teacherAutoTest')
            let request = await axios.get(url);
            this.autoAssessment = request.data;
            if(this.autoAssessment.length>0) {
                this.user = {
                    autoAssessmentStartDate: this.autoAssessment[0].self_start_date,
                    autoAssessmentEndDate: this.autoAssessment[0].self_end_date
                }
                console.log(this.user);
                console.log(this.autoAssessment);
                this.autoAssessment.forEach(item =>{

                    item.name = this.capitalize(item.name)

                })
            }
        },


        getPeerAssessments: async function(){

            let url = route('tests.index.teacherPeerTests')
            let request = await axios.get(url);
            this.peerAssessments = request.data;
            if(this.peerAssessments.length>0) {
                this.user.peerAssessmentStartDate = this.peerAssessments[0].colleague_start_date
                this.user.peerAssessmentEndDate = this.peerAssessments[0].colleague_end_date
                this.peerAssessments.forEach(peerAssessment => {
                    peerAssessment.name = this.capitalize(peerAssessment.name)
                })
            }
        },

        getBossAssessments: async function(){
            let url = route('tests.index.teacherBossTests')
            let request = await axios.get(url);
            this.bossAssessments = request.data;
            if(this.bossAssessments.length>0){
                this.user.bossAssessmentStartDate= this.bossAssessments[0].boss_start_date
                this.user.bossAssessmentEndDate= this.bossAssessments[0].boss_end_date
                this.bossAssessments.forEach(bossAssessment =>{
                    bossAssessment.name = this.capitalize(bossAssessment.name)
                })
            }
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

    },


}
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {getCSRFToken, prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Group from "@/models/Group";

import Snackbar from "@/Components/Snackbar";
</script>
