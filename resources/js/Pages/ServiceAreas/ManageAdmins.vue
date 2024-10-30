<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">Gestionar administradores de áreas de servicio</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4 ml-4"
                        @click="openNewAssignmentModal"
                    >
                        Crear nuevo admin
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
                    :items="admins"
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
                            <span> Borrar asignaciones </span>
                        </v-tooltip>
                    </template>

                    <!-- Slot for service areas -->
                    <template v-slot:item.service_areas="{ item }">
                        <div>
                            <ul>
                                <li v-for="area in item.service_areas" :key="area.service_area_code">
                                    {{ area.service_area_name }}
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
                        <span class="headline">Crear Nueva Asignación</span>
                    </v-card-title>
                    <v-card-text>
                        <v-autocomplete
                            v-model="newUser"
                            :items="allUsers"
                            item-text="name"
                            item-value="id"
                            label="Selecciona un usuario"
                        ></v-autocomplete>
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
                    <v-card-title v-if="selectedUser.user !== undefined">
                        <span class="headline">Áreas de Servicio de {{ selectedUser.user.name }}</span>
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
                    <v-card-title class="headline">Eliminar Asignaciones</v-card-title>
                    <v-card-text v-if="selectedUser.user !== undefined">
                        ¿Estás seguro de que deseas eliminar todas las asignaciones de áreas de servicio para {{ selectedUser.user.name }}?
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="red" @click="deleteServiceAreasAssignment">Eliminar</v-btn>
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
                {text: 'Nombre', value: 'user.name'},
                {text: 'Áreas de servicio', value: 'service_areas'},
                {text: 'Gestionar', value: 'actions'},
            ],
            admins: [],
            serviceAreas: [], // Holds the service areas for the select
            selectedUser: {}, // Holds the currently selected user for the modal
            selectedServiceAreas: [], // Holds selected service areas in the modal
            allUsers: [], // All users for creating a new assignment
            newUser: null, // Holds the selected user for new assignment
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
        await this.getAllServiceAreas();
        await this.getAdmins();
        await this.getAllUsers();
        this.isLoading = false;
    },

    methods: {
        syncServiceAreas: async function () {
            try {
                let request = await axios.post(route('api.serviceAreas.sync'));
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getAllServiceAreas();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        getAllServiceAreas: async function () {
            let request = await axios.get(route('api.serviceAreas.index'));
            console.log(request.data);
            this.serviceAreas = request.data;
        },

        getAdmins: async function () {
            let request = await axios.get(route('serviceAreas.admins'));
            this.admins = request.data;
        },

        getAllUsers: async function () {
            let request = await axios.get(route('staffMembers.index'));
            this.allUsers = request.data;
        },

        openEditModal(item) {
            this.selectedUser = item;
            this.selectedServiceAreas = item.service_areas.map(area => area.service_area_code);
            this.modal.editVisible = true;
        },

        closeEditModal() {
            this.modal.editVisible = false;
        },

        openModal(item) {
            this.selectedUser = item; // Set the selected user
            console.log(this.selectedUser);
            this.selectedServiceAreas = item.service_areas.map(area => area.service_area_code); // Pre-select the user's service areas
            this.modal.visible = true; // Show the modal
        },
        closeModal() {
            this.modal.visible = false; // Hide the modal
        },

        async assignServiceAreas() {
            try {
                let request = await axios.post(route('api.serviceAreas.assign'), {
                    userId: this.selectedUser.user.id,
                    serviceAreas: this.selectedServiceAreas,
                });
                showSnackbar(this.snackbar, request.data.message, 'success');
                this.closeEditModal();
                await this.getAdmins();
            } catch (e) {
                console.log(e);
            }
        },

        async createServiceAreaAssignment() {
            try {
                await axios.post(route('api.serviceAreas.assign'), {
                    userId: this.newUser,
                    serviceAreas: this.selectedServiceAreas,
                });
                showSnackbar(this.snackbar, "Nueva asignación creada", 'success');
                this.closeNewAssignmentModal();
                await this.getAdmins();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        async deleteServiceAreasAssignment() {
            try {
                await axios.post(route('api.serviceAreas.deleteAssignments'), {
                    userId: this.selectedUser.user.id,
                });
                showSnackbar(this.snackbar, "Asignaciones eliminadas", 'success');
                this.closeDeleteModal();
                await this.getAdmins();
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
            this.selectedUser = item;
            this.modal.deleteVisible = true;
        },

        closeDeleteModal() {
            this.modal.deleteVisible = false;
        },


    },
};
</script>
