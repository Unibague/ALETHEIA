<template>
    <AuthenticatedLayout>
        <!--Snackbars-->
         <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
            :color="snackbar.color + ' accent-2'"
            top
            right
        >
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn
                    text
                    v-bind="attrs"
                    @click="snackbar.status = false"
                >
                    Cerrar
                </v-btn>
            </template>
        </v-snackbar>

       Bienvenido al inicio de un nuevo proyecto
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";

export default {
    components: {
        AuthenticatedLayout,
        InertiaLink,
        ConfirmDialog
    },
    data: () => ({
        snackbar:{

        },
        showDialog: false
    }),
    methods: {}
}
</script>
