<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>


            <h3 class="align-self-start mb-5" > Asignar Pares y Jefes de {{this.currentUnitTitle}}</h3>

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
                    <template v-slot:item.pairs="{ item }">

                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <v-autocomplete
                                    label="Por favor, selecciona un usuario"
                                    :items="listOfTeachers"
                                    v-model="peerSelected[item.id]"
                                    item-text="name"
                                    item-value="id"
                                    :hint="peerSelected[item.id] ? 'Click al ícono de la derecha para borrar asignación' : ''"
                                    persistent-hint
                                    return-object
                                    single-line
                                    @change="assignRolesToTeacher('par',item.id, peerSelected[item.id].userId, unitIdentifier)"
                                >

                                    <template v-slot:append-outer>

                                            <v-icon
                                                :color="'info'"
                                                v-text="'mdi-delete'"
                                                v-show="peerSelected[item.id]"
                                                @click="removeAssignedRole('par',item.id, peerSelected[item.id].userId, unitIdentifier)"
                                            ></v-icon>

                                    </template>


                                </v-autocomplete>


                            </template>

                        </v-tooltip>

                    </template>


                    <template v-slot:item.bosses="{ item }">

                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">

                                <v-autocomplete
                                    label="Por favor selecciona un usuario"
                                    :items="bosses"
                                    v-model="bossSelected[item.id]"
                                    item-text="name"
                                    item-value="userId"
                                    :hint="bossSelected[item.id] ? 'Click al ícono de la derecha para borrar asignación' : ''"
                                    persistent-hint
                                    return-object
                                    single-line
                                    @change="assignRolesToTeacher('jefe',item.id, bossSelected[item.id].userId, unitIdentifier)"
                                >

                                    <template v-slot:append-outer>

                                        <v-icon
                                            :color="'info'"
                                            v-text="'mdi-delete'"
                                            v-show="bossSelected[item.id]"
                                            @click="removeAssignedRole('jefe',item.id, bossSelected[item.id].userId, unitIdentifier)"
                                        ></v-icon>

                                    </template>

                                </v-autocomplete>


                            </template>



                        </v-tooltip>

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

    props: ['unitId'],

    data: () => {
        return {
            //Table info
            assignments:[],
            peerAssignments: [],
            bossAssignments:[],
            rolesRelationsArray: [],
            peerSelected: {name:'', userId:'', isFilled: false},
            bossSelected: {name:'', userId:'', isFilled: false},
            unitAdmin: false,
            unitAdminDialog:false,
            unitIdentifier:'',
            selectedTeacher: '',
            selectedTeacherName: '',
            currentUnitTitle:'',
            listOfTeachers:[],
            teachers: [],
            bosses: [],
            currentUnit: '',
            teacherRoleId: '',
            headers: [
                {text: 'Nombre', value: 'name', align: 'center'},
                {text: 'Lista de pares', value: 'pairs'},
                {text: 'Lista Jefes', value: 'bosses'}
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

        //Tomamos toda la info de los props y la colocamos en la variable currentUnit
        this.currentUnit= this.unitId[0];

        this.unitIdentifier = this.currentUnit.identifier

        this.currentUnitTitle  = this.capitalize(this.currentUnit.name);

        await this.getTeachersFromCurrentUnit();
        await this.getAllUnitBosses();
        await this.getAllAssignments();


    },


    methods:{

        async getTeachersFromCurrentUnit () {

            let url = route('unit.teachers', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            this.teachers = request.data.teachers_from_unit;

            this.teachers.forEach(teacher => {
                teacher.name= this.capitalize(teacher.name);
            })

            await this.getAllTeachersAndSortAlphabetically();

        },


        async getAllTeachersAndSortAlphabetically () {

            /*This is the list of the "DTC" and "ESI" employee_type teachers that are going to
            be available for the user to select on the v-autocomplete for peers column*/

            let url = route('teachers.getSuitableList')

            let request = await axios.get(url);

            let data = request.data;

            data.forEach(teacher => {

                this.listOfTeachers.push({
                    name: this.capitalize(teacher.user.name),
                    userId: teacher.user.id
                })
            })

            console.log(this.listOfTeachers);

        },


        async getAllUnitBosses(){

            /*This is the list of unit bosses */

            let url = route('unit.bosses', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            this.bosses = request.data.bosses;

            this.bosses.forEach(boss =>{

                boss.name = this.capitalize(boss.name)
                boss.userId = boss.id

            })

           /* console.log(this.bosses);*/

        },

        async getAllAssignments (){

            let url = route('api.unity.roles.unitAssignments')

            let data = this.teachers;

            try {

                let request = await axios.post(url, data);

                this.assignments = request.data

                this.teachers.forEach(teacher =>{

                    this.assignments.forEach(assignment=>{

                        if(teacher.id === assignment.evaluated_id){

                            if(assignment.role == "par"){

                                 let evaluatorName= this.listOfTeachers.filter(listedTeacher=>

                                    listedTeacher.userId == assignment.evaluator_id

                                )

                                evaluatorName = evaluatorName[0].name

                                this.peerSelected[assignment.evaluated_id] = {userId :assignment.evaluator_id,
                                    name: evaluatorName, isFilled:true}

                            }


                            if(assignment.role == "jefe"){

                                let evaluatorName= this.bosses.filter(boss=>

                                    boss.userId == assignment.evaluator_id

                                )

                                evaluatorName = evaluatorName[0].name

                                this.bossSelected[assignment.evaluated_id] = {userId :assignment.evaluator_id,
                                    name: evaluatorName, isFilled:true}
                            }

                        }

                    })

                })

                showSnackbar(this.snackbar, "Asignaciones cargadas correctamente", 'success', 5000);

            } catch  {
                showSnackbar(this.snackbar, "Si transferiste a un docente desde otra unidad y ya tenía" +
                    " un jefe asignado, recuerda que debes asignarlo" +
                    " como jefe primero en esta unidad", 'alert', 10000);
            }


        },


/*
        async getBossAssignments (){

            let url = route('api.unity.roles.unitAssignments')

            let data = this.teachers;

            try {

                let request = await axios.post(url, data);

                console.log(request.data);

                this.bossAssignments = request.data

                this.teachers.forEach(teacher =>{

                    this.bossAssignments.forEach(bossAssignment =>{

                        if(teacher.id === bossAssignment.evaluated_id){

                            if(bossAssignment.role == "jefe"){



                        }

                    })

                })

                /!*     console.log(this.peerSelected, this.bossSelected)*!/

                showSnackbar(this.snackbar, "Asignaciones cargadas correctamente", 'success', 5000);

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }

        },*/



        async assignRolesToTeacher (which, beingAssignedUserId, assignedToUserId, unitIdentifier){

            let role = "";


            if (which === 'par'){

                role = 'par'
            }

            if(which === 'jefe'){

                role = 'jefe'
            }

            let data = {role, beingAssignedUserId, assignedToUserId, unitIdentifier}

            let url = route('unity.roles.assignment')

            try {
                let request = await axios.post(url, data);
                showSnackbar(this.snackbar, request.data.message, 'success');
            }
            catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }


        },


        async removeAssignedRole (which, beingAssignedUserId, assignedToUserId, unitIdentifier){

            let role = "";

            if (which === 'par'){

                role = 'par'
            }

            if (which === 'jefe'){

                role = 'jefe'

            }

                let data = {role, beingAssignedUserId, assignedToUserId, unitIdentifier}

                let url = route('unity.roles.removeAssignment')

                try {

                    let request = await axios.post(url, data);

                    if (which === 'par'){

                        this.peerSelected[beingAssignedUserId] = {userId :'',
                            name: '', isFilled:false}

                    }

                    else{

                        this.bossSelected[beingAssignedUserId] = {userId :'',
                            name: '', isFilled:false}
                    }

                    showSnackbar(this.snackbar, request.data.message, 'success');

                } catch (e) {
                    showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
                }

        },


        capitalize($field){

           return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        }

    }

}
</script>

