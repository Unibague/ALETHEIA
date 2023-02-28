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
                    <QuestionComponent v-for="(question,questionKey) in questions"  v-bind:key="question.id"
                                       :question="question" :baseIndex="questionKey"
                                       @copyQuestion="copyQuestion" @deleteQuestion="confirmDeleteQuestion"
                                       @questionUpdated="updateQuestion"
                    />
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
import Question from "@/models/Question";
import QuestionComponent from "@/Components/Question";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
        QuestionComponent
    },
    data: () => {
        return {
            questionModel: new Question(),
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
        updateQuestion({question,index}){
            this.questions[index] = question;
        },
        async getQuestions() {
            const url = route('api.forms.questions.show', {form: this.getFormId()});
            try {
                let request = await axios.get(url);
                this.questions = Question.fromRequest(request.data);
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
            return route().params.form;
        },
        copyQuestion(question) {
            this.questions.push(Question.fromModel(question));
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
            this.questions.push(new Question());
        },


    }


}
</script>
