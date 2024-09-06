<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <v-row class="mb-2" justify="center">
                <v-col cols="12">
                    <h2 class="text-center">
                        Estás realizando la evaluación del profesor {{ teacher.name }} ({{ group.name }})
                    </h2>
                </v-col>
                <v-col cols="12" md="8" align-self="center">
                    <div class="text-justify text-h6" style="white-space: pre-wrap">{{ test.description }}</div>
                </v-col>
            </v-row>

            <v-row class="mt-3" justify="center" dense>
                <v-col cols="12" md="8">
                    <v-form
                        ref="form"
                        lazy-validation
                        v-model="valid"
                    >
                        <v-card v-for="question in test.questions" :key="question.id" class="mb-8">
                            <v-card-text>
                                <div class="text-h6 text-justify mb-5">
                                    {{ question.name }}
                                </div>
                                <v-row>
                                    <v-col cols="12">
                                        <color-radio-group
                                            v-if="question.type !== 'abierta'"
                                            v-model="question.answer"
                                            :options="question.options"
                                            :rules="selectRules"
                                        ></color-radio-group>

                                        <template v-if="question.type === 'abierta'">
                                            <v-radio-group
                                                v-model="question.commentType"
                                                :rules="selectRules"
                                                @change="updateValidity"
                                                required
                                            >
                                                <v-radio
                                                    v-for="option in question.options"
                                                    :key="option.placeholder"
                                                    :label="option.placeholder"
                                                    :value="option.placeholder"
                                                />
                                            </v-radio-group>

                                            <v-text-field
                                                v-model="question.answer"
                                                :rules="typeRules"
                                                @input="updateValidity"
                                                required
                                                placeholder="Por favor, ingresa tu respuesta en este campo"
                                            />
                                        </template>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-form>
                </v-col>
            </v-row>
            <v-row justify="center" v-if="canSend">
                <v-col cols="12" class="d-flex justify-center">
                    <v-btn color="primario" large class="grey--text text--lighten-4" @click="validate">
                        Enviar formulario
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
        <v-dialog v-model="dialog" persistent max-width="290">
            <v-card>
                <v-card-title class="text-h5">
                    Formulario guardado
                </v-card-title>
                <v-card-text>
                    El formulario ha sido diligenciado exitosamente. Serás redirigido a la página de inicio.
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" text @click="redirect">
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
import {prepareErrorText, showSnackbar} from "@/HelperFunctions";
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";
import ColorSlider from "@/Components/ColorSlider"; // Import the new component
import ColorRadioGroup from "@/Components/ColorRadioGroup"; // Import the new component

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
        ColorSlider,
        ColorRadioGroup
    },
    data: () => {
        return {
            valid: false,
            selectRules: [
                v => !!v || 'Por favor, selecciona una opción de respuesta'
            ],
            typeRules:[
                v => !!v || 'Por favor, escribe tu respuesta',
            ],
            dialog: false,
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
        group: Object,
        teacher: Object,
        canSend: Boolean
    },
    async created() {
        this.parseQuestions();
        this.isLoading = false;
    },
    methods: {

        updateValidity() {
            this.$nextTick(() => {
                this.valid = this.$refs.form.validate();
            });
        },

        redirect: function () {
            window.location.href = route('tests.index.view');
        },
        sendForm: async function () {
            try {
                console.log(this.test.questions);
                await axios.post(route('api.tests.store'), {
                    answers: this.test.questions,
                    form_id: this.test.id,
                    teacherId: this.teacher.id,
                    groupId: this.group.id,
                });
                this.dialog = true;
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert')
            }
        },
        parseQuestions() {
            this.test.questions = JSON.parse(this.test.questions);
            console.log(this.test.questions);
        },
        validate() {
            let isValid = this.$refs.form.validate();
            if (isValid === true) {
                this.sendForm();
            } else {
                showSnackbar(this.snackbar, 'Debes contestar todas las preguntas para poder enviar el formulario', 'alert');
            }
        },
        setSliderColor(question) {
            const value = question.answer || 0;
            const totalSteps = question.options.length - 1;

            const red = 255 - Math.round((255 * value) / totalSteps);
            const green = Math.round((255 * value) / totalSteps);
            const color = `rgb(${red},${green},0)`;

            // Apply the background color to the slider track
            const sliderElement = document.querySelector(`.slider-with-color[data-question-id="${question.id}"] .v-slider__track-fill`);
            if (sliderElement) {
                sliderElement.style.backgroundColor = color;
            }
        },
        getPlaceholderLabel(question) {
            if (question.options && question.answer >= 0 && question.answer < question.options.length) {
                return question.options[question.answer].placeholder;
            }
            // Fallback to a default placeholder if the answer is out of range or undefined
            return question.options[0].placeholder;
        }
    }
}
</script>

<style scoped>
.slider-with-color .v-slider__track-fill {
    transition: background-color 0.3s ease;
}
</style>
