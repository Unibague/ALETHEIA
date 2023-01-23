<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end">

                <InertiaLink as="v-btn" color="primario"
                             class="grey--text text--lighten-4 ml-4" :href="route('answers.index.view')">
                    Volver a todos los formularios
                </InertiaLink>
                <h2 class="align-self-center">Viendo respuestas del formulario</h2>

            </div>
            <v-row class="mt-3" justify="center" dense>
                <v-col cols="12" :lg="7">
                    <v-card
                        class="align-self-end mt-3 mb-15"
                        outlined
                        rounded="lg"
                        elevation="2"
                        v-for="(question,questionKey) in answers" :key="questionKey">

                        <v-card-text>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Pregunta"
                                        required
                                        v-model="question.name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-radio-group v-model="question.answer">
                                        <v-radio v-for="(option, optionKey) in question.options" :key="option.value"
                                        :value="option.value" :label="option.placeholder" readonly>
                                        </v-radio>
                                    </v-radio-group>
                                </v-col>
                            </v-row>

                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";
import FormQuestions from "@/models/FormQuestions";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },
    data: () => {
        return {
            record: null,
            answers: [],
            formMethod: 'create',
            deletedFormId: 0,

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            deleteFormDialog: false,
            deleteQuestionDialog: false,
            deletedQuestionKey: 0,
            isLoading: true,
        }
    },
    async created() {
        await this.getAnswers();
        this.isLoading = false;
    },

    methods: {
        async getAnswers() {
            const url = route('api.answers.show', {answer: this.getAnswerId()});
            try {
                let request = await axios.get(url);
                this.record = request.data;
                this.answers = JSON.parse(this.record.answers);
                console.log(this.answers);
            } catch (e) {
            }
        },

        getAnswerId() {
            const path = window.location.pathname;
            const items = path.split('/');
            return items[items.length - 1];
        },
    }


}
</script>
