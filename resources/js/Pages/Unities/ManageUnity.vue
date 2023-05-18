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

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="setDialogToAddUnitBoss"
                    >
                        Añadir Jefe de docente
                    </v-btn>
                </div>
            </div>


            <h3 class="mb-4"> Administradores y jefes de docente</h3>

            <!--Inicia tabla de admins de unidad y jefes de profesores-->
            <v-card>

                <v-data-table

                    loading-text="Cargando, por favor espere..."

                    :headers="headersAdminsAndBosses"
                    :items="adminsAndBosses"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.role="{ item }">
                        {{ item.pivot.role_id == 2 ? 'Administrador de unidad' : 'Jefe de profesor' }}
                    </template>

                    <template v-slot:item.action="{ item }">

                        <v-tooltip top v-if="item.pivot.role_id == 2" >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="primario--text align-center"
                                    @click="confirmDeleteUnitAdmin(item.id)"
                                >
                                    mdi-delete
                                </v-icon>

                            </template>
                            <span>Eliminar administrador de unidad</span>
                        </v-tooltip>


                        <v-tooltip top v-if="item.pivot.role_id == 3" >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="primario--text align-center"
                                    color="red"
                                    @click="confirmDeleteUnitBoss(item.id)"
                                >
                                    mdi-delete
                                </v-icon>

                            </template>
                            <span>Eliminar jefe de docente</span>
                        </v-tooltip>


                    </template>

                </v-data-table>
            </v-card>


            <h3 class="mt-9"> Docentes de la unidad </h3>

            <!--Tabla profesores de la unidad-->
            <v-card class="mt-4">

                <v-data-table

                    loading-text="Cargando, por favor espere..."

                    :headers="headersTeachers"
                    :items="teachers"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.actions="{ item }">

                        <v-tooltip top v-if="item.teacher_profile!=null">
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
                                    <v-autocomplete
                                        label="Nombre de la unidad"
                                        v-model="selectedUnit"
                                        :items="units"
                                        item-text="title"
                                        return-object
                                        single-line
                                    ></v-autocomplete>
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
                            @click="cancelUnitAdminDialog()"
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

            <!--Asignar jefe de docente-->

            <v-dialog
                v-model="unitBossDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border"> Añadir jefe de docente a la unidad </span>
                    </v-card-title>
                    <v-card-text>
                        <span>Ingrese el nombre del docente</span>

                        <v-autocomplete
                            label="Por favor selecciona un usuario"
                            :items="listOfTeachers"
                            v-model="unitBoss"
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
                            @click="cancelUnitBossDialog()"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="assignTeacherAsBoss(unitBoss.id)"
                        >
                            Guardar cambios
                        </v-btn>



                    </v-card-actions>
                </v-card>
            </v-dialog>



            <confirm-dialog
                :show="deleteUnitAdminDialog"
                @canceled-dialog="deleteUnitAdminDialog = false"
                @confirmed-dialog="deleteUnitAdmin()"
                max-width="600px"
            >
                <template v-slot:title>
                    Estás a punto de eliminar al administrador seleccionado
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>



            <confirm-dialog
                :show="deleteUnitBossDialog"
                @canceled-dialog="deleteUnitBossDialog = false"
                @confirmed-dialog="deleteUnitBoss()"
            >
                <template v-slot:title>
                    Estás a punto de eliminar al jefe de docente escogido
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>


            <confirm-dialog
                :show="sureDeleteUnitBossDialog"
                @canceled-dialog="sureDeleteUnitBossDialog = false"
                @confirmed-dialog="sureDeleteUnitBoss()"
            >
                <template v-slot:title>
                    Ese jefe ya tiene asignaciones realizadas en esta unidad. <br>¿Confirmas que deseas eliminarlo?
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>



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
            listOfTeachers: [],
            unitAdminDialog:false,
            unitBossDialog: false,
            adminsAndBosses: [],
            selectedTeacher: '',
            selectedTeacherName: '',
            selectedStaffMember: '',
            selectedUnit: {title: '', value:''},
            unitAdmin: {name: '', id: ''},
            unitBoss:{name:'', id:''},
            units:[],
            deletedUnitAdminId: 0,
            deleteUnitAdminDialog: false,
            confirmDeleteUnitAdminDialog: false,
            deletedUnitBossId: 0,
            deleteUnitBossDialog: false,
            sureDeleteUnitBossDialog: false,
            listOfUnits:[],
            teachers: [],
            transferTeacherDialog: false,
            currentUnit: '',
            currentUnitTitle: '',
            teacherRoleId: '',
            roles:[],
            headersAdminsAndBosses:[
                {text: 'Código', value: 'code', align: 'center'},
                {text: 'Nombre', value: 'name'},
                {text: 'Rol', value: 'role'},
                {text: 'Elimnar rol', value: 'action', sortable:false}
            ],
            headersTeachers: [
                {text: 'Código', value: 'code', align: 'center'},
                {text: 'Nombre', value: 'name'},
                {text: 'Dependencia', value: 'unitName'},
                {text: 'Cargo', value: 'position'},
                {text: 'Transferir Docente', value: 'actions', width: '10%', sortable: false},
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
/*
        console.log(this.currentUnit);*/

       /* await this.getAllRoles();*/
        /*console.log(this.currentUnit);*/

        this.currentUnitTitle  = this.capitalize(this.currentUnit.name);




    },

    async mounted(){

        await this.getAdminsAndBosses();

        await this.getTeachersFromCurrentUnit();


    },


    methods:{


        async getTeachersFromCurrentUnit () {

            let url = route('unit.teachers', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            this.teachers = request.data.teachers_from_unit;

        /*    console.log(this.teachers);*/

            this.includeTeachersCodeOnArrayAndCapitalize();

            await this.getAllUnitsAndSortAlphabetically();

        },


        async getAdminsAndBosses(){

            let url = route('unit.adminsAndBosses', {unitId:this.currentUnit.identifier})

            let request = await axios.get(url);

            this.adminsAndBosses = request.data.admins_and_bosses;

            this.adminsAndBosses.forEach(adminOrBoss => {

                adminOrBoss.code = adminOrBoss.email.substring(0,adminOrBoss.email.indexOf("@"));

                adminOrBoss.name= this.capitalize(adminOrBoss.name);

            })

            console.log(this.adminsAndBosses);



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

                teacher.position = this.capitalize(teacher.teacher_profile.position);


            })


        },

        async getAllTeachersAndSortAlphabetically () {

            /*This is the list of the "DTC" and "ESI" employee_type teachers that are going to be available
            for the user to select on the v-autocomplete*/

            let url = route('teachers.getSuitableList')

            let request = await axios.get(url);

            let data = request.data;

            data.forEach(teacher => {

                this.listOfTeachers.push({
                    name: this.capitalize(teacher.user.name),
                    id: teacher.user.id
                })

            })

            console.log(this.listOfTeachers);

        },


        async getAllUnitsAndSortAlphabetically (){

            let request = await axios.get(route('api.units.index'));

            this.listOfUnits = request.data;

            this.sortArrayAlphabetically(this.listOfUnits);

            this.listOfUnits.forEach(unit => {

                 this.units.push({
                    title: this.capitalize(unit.name),
                    value: unit.identifier
                })
            })


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

        },

        async setDialogToAddUnitBoss(){

            await this.getAllTeachersAndSortAlphabetically();

            this.unitBossDialog = true

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

                this.unitAdmin= {name: '', id:''};
                this.unitAdminDialog= false;
                await this.getAdminsAndBosses();
                showSnackbar(this.snackbar, request.data.message, 'success');

            }

            catch (e){

                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }

        },

        assignTeacherAsBoss: async function (teacherId){

            try {

                let data = {
                    unitIdentifier: this.currentUnit.identifier,
                    userId: teacherId,
                    role: 'jefe de profesor'
                }

                let request = await axios.post(route('api.units.assignUnitBoss'), data);

                this.unitBossDialog= false;
                this.unitBoss= {name: '', id:''};
                await this.getAdminsAndBosses();
                showSnackbar(this.snackbar, request.data.message, 'success');

            }

            catch (e){

                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }

        },


/*

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
*/
        cancelUnitBossDialog(){
            this.unitBossDialog = false;

        },

        cancelUnitAdminDialog(){

            this.unitAdmin= {name: '', id:''};
            this.unitAdminDialog = false;

        },

        confirmDeleteUnitAdmin: function (userId) {
            this.deletedUnitAdminId = userId;
            console.log(this.deletedUnitAdminId);
            this.deleteUnitAdminDialog = true;
        },

        confirmDeleteUnitBoss: function (userId){
            this.deletedUnitBossId = userId;
            console.log(this.deletedUnitAdminId);
            this.deleteUnitBossDialog = true;

        },


        deleteUnitAdmin: async function(){

            console.log(this.deletedUnitAdminId);

            let data = {
                unitIdentifier: this.currentUnit.identifier,
                userId: this.deletedUnitAdminId
            }

            let url = route('unit.deleteUnitAdmin');

            let request = await axios.post(url, data);

            this.deleteUnitAdminDialog = false;

            await this.getAdminsAndBosses();

            showSnackbar(this.snackbar, request.data.message, 'success');
        },

        deleteUnitBoss: async function(){

            let data = {
                unitIdentifier: this.currentUnit.identifier,
                userId: this.deletedUnitBossId
            }

            try{

                let url = route('unit.deleteUnitBoss');

                let request = await axios.post(url, data);

                this.deleteUnitBossDialog = false;

                await this.getAdminsAndBosses();

                showSnackbar(this.snackbar, request.data.message, 'success');
            }

            catch(e){

                this.deleteUnitBossDialog = false;
                this.sureDeleteUnitBossDialog = true
                /*await this.sureDeleteUnitBoss(data.unitIdentifier, data.userId);*/

            }


        },

        sureDeleteUnitBoss: async function(){

            let data = {
                unitIdentifier: this.currentUnit.identifier,
                userId: this.deletedUnitBossId
            }

            try{

                let url = route('unit.confirmDeleteUnitBoss');

                let request = await axios.post(url, data);

                this.sureDeleteUnitBossDialog = false;

                await this.getAdminsAndBosses();

                showSnackbar(this.snackbar, request.data.message, 'success');
            }

            catch(e){

                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);

            }


        }


},



}
</script>

