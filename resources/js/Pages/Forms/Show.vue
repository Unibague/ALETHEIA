<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end">

                <v-btn color="primario"
                       large
                       class="grey--text text--lighten-4"
                       @click="saveForm"
                >Guardar formulario
                </v-btn>

                <h2 class="align-self-center">Crear preguntas del formulario</h2>
            </div>
            <v-row class="mt-3" justify="center" dense>
                <v-col cols="12" :lg="7">
                    <v-card
                        class="align-self-end mt-3 mb-15"
                        outlined
                        rounded="lg"
                        elevation="2"
                        v-for="(question,questionKey) in questions" :key="questionKey">

                        <v-card-text>
                            <v-row>
                                <v-col cols="6">
                                    <v-select
                                        color="primario"
                                        v-model="question.competence"
                                        :items="questionModel.getPossibleCompetences()"
                                        label="Selecciona una competencia"
                                    ></v-select>
                                </v-col>
                                <v-col cols="6">
                                    <v-text-field
                                        label="Pregunta"
                                        required
                                        v-model="question.name"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row v-for="(option, optionKey) in question.options" :key="option.value"
                            >
                                <v-col cols="2" class="d-flex align-center">
                                    <v-text-field
                                        outlined
                                        type="number"
                                        label="Valor"
                                        required
                                        v-model="option.value"
                                        min="1"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="7" class="d-flex align-center">
                                    <v-text-field
                                        outlined
                                        label="Opción de respuesta"
                                        required
                                        v-model="option.placeholder"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="1">
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-btn icon large v-bind="attrs" v-on="on" class="mt-1"
                                                   @click="removeQuestionOption(questionKey,optionKey)">
                                                <v-icon>
                                                    mdi-delete
                                                </v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Borrar opción de respuesta</span>
                                    </v-tooltip>
                                </v-col>
                            </v-row>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn large icon v-bind="attrs" v-on="on" @click="addAnotherOption(questionKey)">
                                        <v-icon>
                                            mdi-plus
                                        </v-icon>
                                    </v-btn>
                                </template>
                                <span>Añadir otra opción de respuesta</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon large v-bind="attrs" v-on="on" @click="copyQuestion(questionKey)">
                                        <v-icon>
                                            mdi-content-copy
                                        </v-icon>
                                    </v-btn>
                                </template>
                                <span>Copiar pregunta</span>
                            </v-tooltip>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon large v-bind="attrs" v-on="on"
                                           @click="confirmDeleteQuestion(questionKey)">
                                        <v-icon>
                                            mdi-delete
                                        </v-icon>
                                    </v-btn>
                                </template>
                                <span>Borrar pregunta</span>
                            </v-tooltip>

                        </v-card-actions>

                    </v-card>
                </v-col>
            </v-row>

            <v-row justify="center">
                <v-col cols="12" class="d-flex justify-center">
                    <v-btn color="primario"
                           large
                           class="grey--text text--lighten-4"
                           @click="addAnotherQuestion"
                    >Añadir otra pregunta
                    </v-btn>
                </v-col>

            </v-row>

            <!------------Seccion de dialogos ---------->

            <!--Confirmar borrar rol-->
            <confirm-dialog
                :show="deleteFormDialog"
                @canceled-dialog="deleteFormDialog = false"
                @confirmed-dialog="deleteQuestion"
            >
                <template v-slot:title>
                    Estas a punto de eliminar el rol seleccionado
                </template>

                ¡Cuidado! esta acción es irreversible

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>

            <!--Confirmar borrar rol-->
            <confirm-dialog
                :show="deleteQuestionDialog"
                @canceled-dialog="deleteQuestionDialog = false"
                @confirmed-dialog="deleteQuestion()"
            >
                <template v-slot:title>
                    Confirmación
                </template>

                ¿Estas seguro que deseas borrar esta pregunta? Esta acción es irreversible

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
            questionModel: new FormQuestions(),
            questions: [],
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
        await this.getQuestions();
        this.isLoading = false;

    },

    methods: {
        async getQuestions() {
            const url = route('api.forms.questions.show', {form: this.getFormId()});
            try {
                let request = await axios.get(url);
                const questions = request.data;
                this.questions = questions === '' ? [] : request.data;
                console.log(this.questions);
            } catch (e) {
            }
        },
        async saveForm() {
            let data = this.questions;
            const url = route('api.forms.questions.store', {form: this.getFormId()});
            try {
                let request = await axios.patch(url, {
                    questions: data
                });
                showSnackbar(this.snackbar, request.data.message, 'success');
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'red', 3000);
            }
        },
        getFormId() {
            const path = window.location.pathname;
            const items = path.split('/');
            return items[items.length - 1];
        },
        copyQuestion(questionKey) {
            const question = JSON.parse(JSON.stringify(this.questions[questionKey]));
            this.questions.push(question);
        },
        confirmDeleteQuestion(questionKey) {
            this.deletedQuestionKey = questionKey;
            this.deleteQuestionDialog = true;
        },
        deleteQuestion() {
            this.questions.splice(this.deletedQuestionKey, 1);
            this.deleteQuestionDialog = false;
        },
        addAnotherQuestion() {
            this.questions.push({options: [{}]});
        },
        addAnotherOption(questionKey) {
            this.questions[questionKey].options.push({});
        },
        removeQuestionOption(questionKey, optionKey) {

            this.questions[questionKey].options.splice(optionKey, 1);
        }
    }


}
</script>
