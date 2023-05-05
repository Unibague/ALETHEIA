<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start" > {{this.currentUnitTitle}} </h2>

                <div>

                    <InertiaLink :href="route('units.assessment.status',{unitId:this.currentUnit.identifier}) ">

                        <v-btn>
                            Estado de la evaluación
                        </v-btn>

                    </InertiaLink>

                    <InertiaLink :href="route('units.roles.manage',{unitId:this.currentUnit.identifier}) ">

                        <v-btn class="ml-4">
                           Gestionar Evaluadores
                        </v-btn>

                    </InertiaLink>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="setDialogToAddUnitAdmin"
                    >
                        Añadir Administrador
                    </v-btn>
                </div>
            </div>

            <!--Inicia tabla-->
            <v-card>

                <v-data-table

                    loading-text="Cargando, por favor espere..."

                    :headers="headers"
                    :items="teachers"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.position="{ item }">
                        {{ item.pivot.role_id===teacherRoleId ? item.position : 'Administrador de unidad' }}
                    </template>



                    <template v-slot:item.actions="{ item }">


                        <span v-if="item.pivot.role_id!==teacherRoleId"> No disponible </span>


                        <v-tooltip top v-if="item.pivot.role_id===teacherRoleId">
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="primario--text align-center"
                                    @click="setDialogToTransferTeacher(item)"
                                >
                                    mdi-swap-horizontal
                                </v-icon>

                            </template>
                            <span>Transferir docente</span>
                        </v-tooltip>

                    </template>

                </v-data-table>
            </v-card>


            <!--Seccion de dialogos-->

                <!--Transferir docente entre unidades-->

            <v-dialog
                v-model="transferTeacherDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border">Transfiriendo al docente {{this.selectedTeacherName}}</span>
                    </v-card-title>
                    <v-card-text>
                        <span>Seleccione la unidad donde desea transferir al docente</span>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-select
                                        label="Nombre de la unidad"
                                        v-model="selectedUnit"
                                        :items="units"
                                        item-text="title"
                                        return-object
                                        single-line
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                        <small>Los campos con * son obligatorios </small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primario"
                            text
                            @click="transferTeacherDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="transferTeacherToSelectedUnit(selectedUnit.value)"
                        >
                            Guardar cambios
                        </v-btn>



                    </v-card-actions>
                </v-card>
            </v-dialog>


            <!--Asignar Admin de unidad-->

            <v-dialog
                v-model="unitAdminDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border"> Añadir adminsitrador a la unidad </span>
                    </v-card-title>
                    <v-card-text>
                        <span>Ingrese el nombre del funcionario</span>

                            <v-autocomplete
                                label="Por favor selecciona un usuario"
                                :items="listOfStaffMembers"
                                v-model="unitAdmin"
                                item-text="name"
                                item-value="id"
                                return-object
                            ></v-autocomplete>


                        <small>Los campos con * son obligatorios </small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primario"
                            text
                            @click="unitAdminDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="assignStaffMemberAsAdmin(unitAdmin.id)"
                        >
                            Guardar cambios
                        </v-btn>



                    </v-card-actions>
                </v-card>
            </v-dialog>



        </v-container>







    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import Unit from "@/models/Unit";
import Snackbar from "@/Components/Snackbar";
import {showSnackbar} from "@/HelperFunctions";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },


    props: ['unit'],

    data: () => {
        return {
            //Table info

            listOfStaffMembers: [],
            unitAdminDialog:false,
            selectedTeacher: '',
            selectedTeacherName: '',
            selectedStaffMember: '',
            selectedUnit: {title: '', value:''},
            unitAdmin: {name: '', id: ''},
            units:[],
            listOfUnits:[],
            teachers: [],
            transferTeacherDialog: false,
            currentUnit: '',
            currentUnitTitle: '',
            teacherRoleId: '',
            headers: [
                {text: 'Código', value: 'code', align: 'center'},
                {text: 'Nombre', value: 'name'},
                {text: 'Dependencia', value: 'unitName'},
                {text: 'Cargo', value: 'position'},
                {text: 'Transferir a otra unidad', value: 'actions', sortable: false},
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
        this.currentUnit= this.unit[0];

        console.log(this.currentUnit);

        /*console.log(this.currentUnit);*/

        this.currentUnitTitle  = this.capitalize(this.currentUnit.name);

        await this.getTeacherRoleId();

    },

    async mounted(){

        await this.getTeachersFromCurrentUnit();

    },


    methods:{


        async getTeachersFromCurrentUnit () {

            let url = route('api.units.teachers', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            let data = request.data[0];

            this.teachers = data.users;

/*            this.teachers = this.teachers.filter(teacher =>{
                return teacher.teacher_profile != null;
            })*/

            this.includeTeachersCodeOnArrayAndCapitalize();

            await this.getAllUnitsAndSortAlphabetically();

        },


        async getStaffMembersAndSortAlphabetically(){

            let request = await axios.get(route('staffMembers.index'));

            this.listOfStaffMembers = request.data;

            this.sortArrayAlphabetically(this.listOfStaffMembers);

                this.listOfStaffMembers.forEach(staffMember => {

                    staffMember.name = this.capitalize(staffMember.name)

                })

            console.log(this.listOfStaffMembers);


        },


        includeTeachersCodeOnArrayAndCapitalize (){

            this.teachers.forEach(teacher => {

                teacher.code = teacher.email.substring(0,teacher.email.indexOf("@"));

                teacher.unitName = this.currentUnitTitle

                teacher.name= this.capitalize(teacher.name);

                if (teacher.teacher_profile != null) {
                    teacher.position = this.capitalize(teacher.teacher_profile.position);

                }
            })

            console.log(this.teachers);


        },

        async getAllUnitsAndSortAlphabetically (){

            let request = await axios.get(route('api.units.index'));

            this.listOfUnits = request.data;

            this.sortArrayAlphabetically(this.listOfUnits);

            this.listOfUnits.forEach(unit => {

                 this.units.push({
                    title: unit.name,
                    value: unit.identifier
                })
            })

           console.log(this.units);

        },

        capitalize($field){

           return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        },


        setDialogToTransferTeacher(item){

            this.transferTeacherDialog = true
            this.selectedTeacher = item
            console.log(this.selectedTeacher);
            this.selectedTeacherName = this.selectedTeacher.name;


        },


        async setDialogToAddUnitAdmin(){

            this.unitAdminDialog = true

            await this.getStaffMembersAndSortAlphabetically();

            await this.retrieveUnitAdmin(this.currentUnit.identifier);

        },


        sortArrayAlphabetically(array){

            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);

        },


        transferTeacherToSelectedUnit: async function(identifier) {

            try{

                let userId = this.selectedTeacher.id
                let data = {
                    unitIdentifier: identifier,
                    userId: userId
                }

                console.log(data);

                let request = await axios.post(route('api.units.transfer'), data);

                await this.getTeachersFromCurrentUnit();

                this.transferTeacherDialog = false

                showSnackbar(this.snackbar, request.data.message, 'success');
            }
            catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }

        },

        assignStaffMemberAsAdmin: async function (staffMemberId){

            try {

               let data = {
                    unitIdentifier: this.currentUnit.identifier,
                    userId: staffMemberId,
                    role: 'administrador de unidad'
                }

                let request = await axios.post(route('api.units.assignUnitAdmin'), data);

                this.unitAdminDialog= false;

                await this.getTeachersFromCurrentUnit();

                showSnackbar(this.snackbar, request.data.message, 'success');

            }

            catch (e){

                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }

        },

        retrieveUnitAdmin: async function ($unitIdentifier){

          let url = route('units.unitAdmin.index')

            try{

               let data = {unitId: $unitIdentifier}

               let request = await axios.post(url, data)

               let unitAdminName =  request.data[0].name;
               let unitAdminId =  request.data[0].user_id;

               this.unitAdmin = {name: unitAdminName, id: unitAdminId}

               console.log(this.unitAdmin);

            }


            catch (e){



            }
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

