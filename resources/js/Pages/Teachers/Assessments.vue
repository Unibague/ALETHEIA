<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-5">
                <h3 class="align-self-start">Autoevaluación</h3>
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

            <div class="d-flex flex-column align-end mt-8 mb-5">
                <h3 class="align-self-start">Pares a evaluar por el docente</h3>
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
                          v-else-if="item.test != null">
                        <input type="hidden" name="_token" :value="token">
                        <input type="hidden" name="data" :value="JSON.stringify(item)">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">

                                <v-btn
                                    type="submit"
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 primario&#45;&#45;text"
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


            <div class="d-flex flex-column align-end mt-8 mb-5">
                <h3 class="align-self-start">Subordinados a evaluar por el docente</h3>
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
                          v-else-if="item.test != null">
                        <input type="hidden" name="_token" :value="token">
                        <input type="hidden" name="data" :value="JSON.stringify(item)">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">

                                <v-btn
                                    type="submit"
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 primario&#45;&#45;text"
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

                {text: 'Nombre', value: 'name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            headersPeers: [

                {text: 'Nombre', value: 'name'},
                {text: 'Acciones', value: 'actions', sortable: false},
            ],
            headersBoss: [

                {text: 'Nombre', value: 'name'},
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
            user:{},
            autoAssessment: [],
            peerAssessments: [],
            bossAssessments: [],
        }
    },

    props: {
        token: String
    },

    async created() {

        await this.getUserInfo();
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

        getUserInfo: async function() {

            let request = await axios.get(route('teachers.assessments.list'));

            this.user = request.data

        },


        getAutoAssessment: async function() {

            let url = route('tests.index.teacherAutoTest')

            let data={userId: this.user.id}

            let request = await axios.post(url, data);

/*            let assessmentPeriodName = request.data.assessmentPeriodName
            let pending = request.data.pending*/

            console.log(request.data);

            this.autoAssessment = request.data;

            this.autoAssessment.forEach(item =>{

                item.name = this.capitalize(item.name)

            })

        },


        getPeerAssessments: async function(){

            let url = route('tests.index.teacherPeerTests')

            let data={userId: this.user.id}

            let request = await axios.post(url, data);

            this.peerAssessments = request.data;

            this.peerAssessments.forEach(peerAssessment =>{

                peerAssessment.name = this.capitalize(peerAssessment.name)

            })

        },



        getBossAssessments: async function(){

            let url = route('tests.index.teacherBossTests')

            let data={userId: this.user.id}

            let request = await axios.post(url, data);

            this.bossAssessments = request.data;

            this.bossAssessments.forEach(bossAssessment =>{

                bossAssessment.name = this.capitalize(bossAssessment.name)

            })

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
