<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <h3 class="align-self-start mb-5" > Estado de evaluación de la unidad {{this.currentUnitTitle}}</h3>
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
                    <template v-slot:item.pair="{ item }">
                        {{item.pair ? item.pair : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.pair !== '' && item.isPairPending === 0)"
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
                            v-if="(item.boss !=='' && item.isBossPending === 0 )"
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

                    <template v-slot:item.autoEvaluation="{ item }">
                        {{item.role === "autoevaluación" ? item.role : ""}}
                        <v-icon
                            color="red"
                            v-if="(item.autoEvaluation === 1)"
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
            isPairPending: '',
            isBossPending:'',
            listOfTeachers:[],
            assignments: '',
            teachers: [],
            currentUnitTitle: '',
            currentUnit: '',
            teacherRoleId: '',
            headers: [
                {text: 'Nombre', value: 'name', align: 'center'},
                {text: 'Par', value: 'pair'},
                {text: 'Jefe', value: 'boss'},
                {text: 'Autoevaluación', value: 'autoEvaluation'}
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
    },

    async mounted(){
        await this.getTeachersFromCurrentUnit();
    },

    methods:{
        async getTeachersFromCurrentUnit () {
            let url = route('unit.teachers', {unitId:this.currentUnit.identifier})
            let request = await axios.get(url);
            this.teachers = request.data.teachers_from_unit;
            this.includeTeachersCodeOnArrayAndCapitalize();
            await this.retrieveAssessmentStatus();
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
        async retrieveAssessmentStatus (){
            let url = route('api.unity.roles.unitAssignments')
            let data = this.teachers;
            try {
                let request = await axios.post(url, data);
                console.log(request.data);
                this.assignments = request.data
                console.log('Docentes de la unidad', this.teachers)
                console.log('Asignaciones de la unidad', this.assignments)
                this.assignments.forEach(assignment =>{
                    assignment.name = this.capitalize(assignment.name)
                    this.teachers.forEach(teacher =>{
                        if(assignment.evaluated_id == teacher.id){
                            if(assignment.role == "autoevaluación"){
                                teacher.autoEvaluation = assignment.pending
                            }
                            if(assignment.role == "par"){
                              teacher.pair = assignment.name
                                teacher.isPairPending = assignment.pending
                            }
                            if(assignment.role == "jefe"){
                                teacher.boss = assignment.name
                                teacher.isBossPending = assignment.pending
                            }
                        }
                    })
                })
            }
            catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
                showSnackbar(this.snackbar, "Estado de evaluación cargado correctamente", 'success');
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }
    }
}
</script>

