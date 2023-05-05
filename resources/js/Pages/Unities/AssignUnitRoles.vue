<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>


            <h3 class="align-self-start mb-5" > Asignar Pares y Jefes </h3>

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
                                    label="Por favor selecciona un usuario"
                                    :items="listOfTeachers"
                                    v-model="peerSelected[item.id]"
                                    item-text="name"
                                    item-value="id"
                                    return-object
                                    single-line
                                    @change="assignRolesToTeacher('par',item.id, peerSelected[item.id].userId)"
                                ></v-autocomplete>

                            </template>

                        </v-tooltip>

                    </template>


                    <template v-slot:item.bosses="{ item }">

                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">

                                <v-autocomplete
                                    label="Por favor selecciona un usuario"
                                    :items="listOfTeachers"
                                    v-model="bossSelected[item.id]"
                                    item-text="name"
                                    item-value="id"
                                    return-object
                                    single-line
                                    @change="assignRolesToTeacher('jefe',item.id, bossSelected[item.id].userId)"
                                ></v-autocomplete>

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
            rolesRelationsArray: [],
            peerSelected: [{name:'', userId:''}],
            bossSelected: [{name:'', userId:''}],
            unitAdmin: false,
            unitAdminDialog:false,
            selectedTeacher: '',
            selectedTeacherName: '',
            listOfTeachers:[],
            teachers: [],
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

        this.currentUnitTitle  = this.capitalize(this.currentUnit.name);

        await this.getTeacherRoleId();

    },

    async mounted(){

        await this.getTeachersFromCurrentUnit();

        await this.retrieveRolesFromTeachers();
    },



    methods:{

        async getTeachersFromCurrentUnit () {

            let url = route('api.units.teachers', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            let users = request.data[0].users;

            let teachers = users.filter(user => {

                return user.pivot.role_id==this.teacherRoleId;

            })

            this.teachers = teachers;

            this.includeTeachersCodeOnArrayAndCapitalize();

            await this.getAllTeachersAndSortAlphabetically();

        },


        includeTeachersCodeOnArrayAndCapitalize (){

            /*This is the list of the teachers that belong to the current unit, these are the ones that are gonna be assigned with roles*/

            this.teachers.forEach(teacher => {

                teacher.code = teacher.email.substring(0,teacher.email.indexOf("@"));

                teacher.unitName = this.currentUnitTitle

                teacher.name= this.capitalize(teacher.name);

                teacher.position = this.capitalize(teacher.teacher_profile.position);

            })

        },

        async getAllTeachersAndSortAlphabetically () {

            /*This is the list of the "DTC" and "ESI" employee_type teachers that are going to be available for the user to select on the v-autocomplete*/

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

        async retrieveRolesFromTeachers (){

            let url = route('api.unity.roles.assignment')

            try {

                let request = await axios.get(url);

                this.rolesRelationsArray = request.data

                this.teachers.forEach(teacher =>{

                    this.rolesRelationsArray.forEach( relation =>{

                        if(teacher.id === relation.evaluated_id){

                            if(relation.role == "par"){

                                 let evaluatorName= this.listOfTeachers.filter(listedTeacher=>

                                    listedTeacher.userId == relation.evaluator_id

                                )

                                evaluatorName = evaluatorName[0].name

                                this.peerSelected[relation.evaluated_id] = {userId :relation.evaluator_id,
                                    name: evaluatorName}

                            }

                            if(relation.role == "jefe"){

                                let evaluatorName= this.listOfTeachers.filter(listedTeacher=>

                                    listedTeacher.userId == relation.evaluator_id

                                )

                                evaluatorName = evaluatorName[0].name

                                this.bossSelected[relation.evaluated_id] = {userId :relation.evaluator_id,
                                    name: evaluatorName}
                            }

                        }

                    })

                })

                console.log(this.peerSelected, this.bossSelected)


                showSnackbar(this.snackbar, "Asignaciones cargadas correctamente", 'success');

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }


        },


        async assignRolesToTeacher (which, beingAssignedUserId, assignedToUserId){

            console.log(this.peerSelected);

            if (which === 'par'){

                let role = 'par'

                let data = {role, beingAssignedUserId, assignedToUserId}

                let url = route('unity.roles.assignment')

                try {
                    let request = await axios.post(url, data);
                    showSnackbar(this.snackbar, request.data.message, 'success');
                } catch (e) {
                    showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
                }

            }

            if (which === 'jefe'){

                let role = 'jefe'

                let data = {role, beingAssignedUserId, assignedToUserId}

                let url = route('unity.roles.assignment')

                try {
                    let request = await axios.post(url, data);
                    showSnackbar(this.snackbar, request.data.message, 'success');
                } catch (e) {
                    showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
                }


            }

        },

        capitalize($field){

           return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },

        getTeacherRoleId: async function(){


            let request = await axios.get(route('api.roles.index'))

            let roles = request.data

            let teacherRole= roles.filter(role => {

                return role.name == "docente"

            })

            this.teacherRoleId = teacherRole[0].id;

            console.log(this.teacherRoleId);

        }



    }

}
</script>

