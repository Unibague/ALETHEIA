<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar Facultades</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="openNewAssignmentModal"
                    >
                        Crear nueva
                    </v-btn>
                </div>
            </div>

            <!-- Inicia tabla -->
            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar por nombre"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="faculties"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.actions="{ item }">
                        <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="openEditModal(item)"
                                >
                                    mdi-pencil
                                </v-icon>
                            </template>
                            <span> Editar </span>
                        </v-tooltip>

                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario--text"
                                    @click="openDeleteModal(item)"
                                >
                                    mdi-delete
                                </v-icon>
                            </template>
                            <span> Borrar facultad </span>
                        </v-tooltip>
                    </template>

                    <!-- Slot for service areas -->
                    <template v-slot:item.service_areas="{ item }">
                        <div>
                            <ul>
                                <li v-for="area in item.service_areas" :key="area.service_area_code">
                                    {{ area.name }}
                                </li>
                            </ul>
                        </div>
                    </template>

                </v-data-table>
            </v-card>
            <!-- Acaba tabla -->

            <!-- New Assignment Modal -->
            <v-dialog v-model="modal.newAssignmentVisible" max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline">Crear Nueva Facultad</span>
                    </v-card-title>
                    <v-card-text>
                        <v-text-field
                            v-model="newFacultyName"
                            label="Dale un nombre a la facultad"
                        ></v-text-field>
                        <v-select
                            v-model="selectedServiceAreas"
                            :items="serviceAreas"
                            item-text="name"
                            item-value="code"
                            multiple
                            chips
                            label="Selecciona áreas de servicio"
                        >
                            <template v-slot:selection="{ item }">
                                <v-chip v-if="item" :key="item.code" color="primario"
                                        class="grey--text text--lighten-4 ml-1 mt-2">
                                    {{ item.name }}
                                </v-chip>
                            </template>
                        </v-select>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="primario"
                               class="grey--text text--lighten-4 ml-4"
                               @click="createServiceAreaAssignment">
                            Guardar
                        </v-btn>
                        <v-btn text @click="closeNewAssignmentModal">Cerrar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <!-- Edit Modal -->
            <v-dialog v-model="modal.editVisible">
                <v-card>
                    <v-card-title v-if="selectedFaculty !== null">
                        <span class="headline">Áreas de Servicio de "{{this.selectedFaculty.name}}" </span>
                    </v-card-title>
                    <v-card-text>
                        <v-select
                            v-model="selectedServiceAreas"
                            :items="serviceAreas"
                            item-text="name"
                            item-value="code"
                            multiple
                            chips
                            label="Selecciona áreas de servicio"
                        >
                            <template v-slot:selection="{ item }">
                                <v-chip v-if="item" :key="item.code" color="primario"
                                        class="grey--text text--lighten-4 ml-1 mt-2">
                                    {{ item.name }}
                                </v-chip>
                            </template>
                        </v-select>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="primario"
                               class="grey--text text--lighten-4 ml-4"
                               @click="assignServiceAreas">
                            Guardar
                        </v-btn>
                        <v-btn text @click="closeEditModal">Cerrar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <!-- Delete Modal -->
            <v-dialog v-model="modal.deleteVisible" max-width="500px">
                <v-card>
                    <v-card-title class="headline">Eliminar Facultad</v-card-title>
                    <v-card-text v-if="selectedFaculty !== null">
                        ¿Estás seguro de que deseas eliminar la Facultad seleccionada?
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="primario" class="grey--text text--lighten-4 ml-4"
                               @click="deleteServiceAreasAssignment">Eliminar</v-btn>
                        <v-btn text @click="closeDeleteModal">Cancelar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions";
import Snackbar from "@/Components/Snackbar";
import axios from "axios";

export default {
    components: {
        AuthenticatedLayout,
        Snackbar,
    },
    data: () => {
        return {
            search: '',
            // Table info
            headers: [
                {text: 'Nombre', value: 'name'},
                {text: 'Áreas de servicio', value: 'service_areas'},
                {text: 'Gestionar', value: 'actions'},
            ],
            faculties: [],
            serviceAreas: [], // Holds the service areas for the select
            selectedFaculty: {}, // Holds the currently selected user for the modal
            selectedServiceAreas: [], // Holds selected service areas in the modal
            allUsers: [], // All users for creating a new assignment
            newFacultyName: '', // Holds the selected user for new assignment
            // Modal state
            modal: {
                editVisible: false,
                deleteVisible: false,
                newAssignmentVisible: false,
            },
            // Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            isLoading: true,
        };
    },
    async created() {
        await this.getAllFaculties();
        await this.getAllServiceAreas();


        // await this.getAdmins();
        // await this.getAllUsers();
        this.isLoading = false;
    },

    methods: {

        getAllFaculties: async function () {
            let request = await axios.get(route('api.faculties.index'));
            console.log(request.data);
            this.faculties = request.data;
        },

        getAllServiceAreas: async function () {
            let request = await axios.get(route('api.serviceAreas.index'));
            // console.log(request.data);
            this.serviceAreas = request.data;
        },

        getAllUsers: async function () {
            let request = await axios.get(route('staffMembers.index'));
            this.allUsers = request.data;
        },

        openEditModal(item) {
            this.selectedFaculty = item;
            this.selectedServiceAreas = item.service_areas.map(area => area.code);
            this.modal.editVisible = true;
        },

        closeEditModal() {
            this.modal.editVisible = false;
        },

        openModal(item) {
            this.selectedFaculty = item; // Set the selected user
            console.log(this.selectedFaculty);
            this.selectedServiceAreas = item.service_areas.map(area => area.code); // Pre-select the user's service areas
            this.modal.visible = true; // Show the modal
        },

        closeModal() {
            this.modal.visible = false; // Hide the modal
        },

        async assignServiceAreas() {

            console.log(this.selectedFaculty);
            try {
                let request = await axios.patch(route('api.faculties.update', { faculty: this.selectedFaculty.id }), {
                    serviceAreas: this.selectedServiceAreas,
                });
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.closeEditModal();
                await this.getAllFaculties();
            } catch (e) {
                console.log(e);
            }
        },

        async createServiceAreaAssignment() {
            try {
                await axios.post(route('api.faculties.store'), {
                    name: this.newFacultyName,
                    serviceAreas: this.selectedServiceAreas,
                });
                showSnackbar(this.snackbar, "Nueva facultad creada", 'success');
                this.closeNewAssignmentModal();
                await this.getAllFaculties()
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        async deleteServiceAreasAssignment() {
            try {
                await axios.delete(route('api.faculties.destroy',{faculty:this.selectedFaculty}));
                showSnackbar(this.snackbar, "Facultad eliminada", 'success');
                this.closeDeleteModal();
                await this.getAllFaculties();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        closeNewAssignmentModal() {
            this.modal.newAssignmentVisible = false;
        },

        openNewAssignmentModal() {
            this.newUser = null;
            this.selectedServiceAreas = [];
            this.modal.newAssignmentVisible = true;
        },

        openDeleteModal(item) {
            this.selectedFaculty = item;
            this.modal.deleteVisible = true;
        },

        closeDeleteModal() {
            this.modal.deleteVisible = false;
        },


    },
};
</script>
