<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
            </div>
            <v-row class="mt-3" justify="center" dense>
                <v-col cols="12" :lg="7">
                    <v-form
                        ref="form"
                        lazy-validation
                        v-model="valid">
                        <v-card v-for="question in test.questions" :key="question.id" class="mb-8">

                            <v-card-text>
                                <div class="text-h6 text-justify mb-5">
                                    {{ question.name }}
                                </div>
                                <v-row>
                                    <v-col cols="12" :lg="10">
                                        <v-select
                                            v-model="question.answer"
                                            required
                                            outlined
                                            placeholder="Por favor, selecciona una respuesta"
                                            :items="question.options"
                                            item-text="placeholder"
                                            item-value="value"
                                            :rules="selectRules"
                                        >
                                        </v-select>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-form>
                </v-col>
            </v-row>
            <v-row justify="center">
                <v-col cols="12" class="d-flex justify-center">
                    <v-btn color="primario"
                           large
                           class="grey--text text--lighten-4"
                           @click="validate"
                    >Enviar formulario
                    </v-btn>
                </v-col>

            </v-row>

        </v-container>
        <v-dialog
            v-model="dialog"
            persistent
            max-width="290"
        >
            <v-card>
                <v-card-title class="text-h5">
                    Formulario guardado
                </v-card-title>
                <v-card-text>El formulario ha sido diligenciado exitosamente. Seras redirigido a la página de inicio
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="redirect"
                    >
                        Aceptar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";

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
            valid: false,
            selectRules: [
                v => !!v || 'Por favor, selecciona una opción de respuesta',
            ],
            dialog: false,
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
    props: {
        test: Object,
        groupId: Number,
        teacherId: Number,
    },
    async created() {
        this.parseQuestions()
        this.isLoading = false;
    },

    methods: {
        redirect: function () {
            window.location.href = route('tests.index.view');
        },
        sendForm: async function () {
            try {
                await axios.post(route('api.tests.store'),
                    {
                        answers: this.test.questions,
                        form_id: this.test.form_id,
                        teacherId: this.teacherId,
                        groupId: this.groupId,
                    });
                this.dialog = true;

            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert')
            }
        },
        parseQuestions() {
            this.test.questions = JSON.parse(this.test.questions)
        },
        validate() {
            let isValid = this.$refs.form.validate()
            if (isValid === true) {
                this.sendForm()
            }
        },
    },


}
</script>
