<template>
    <v-card
        class="align-self-end mt-3 mb-15"
        outlined
        rounded="lg"
        elevation="2"
    >
        <v-card-text>
            <v-row>
                <v-col cols="6">
                    <v-select
                        color="primario"
                        v-model="type"
                        :items="questionModel.getPossibleTypes()"
                        :value="(questionType)=>questionType.value"
                        :item-text="(questionType)=>questionType.placeholder"
                        label="Selecciona el tipo de pregunta"
                        @change="notifyParent"
                    ></v-select>
                </v-col>
                <v-col cols="6">
                    <v-select
                        color="primario"
                        v-model="competence"
                        :items="type === 'abierta' ? ['General']:questionModel.getPossibleCompetences()"
                        label="Selecciona una competencia"
                        @change="notifyParent"
                    ></v-select>
                </v-col>
                <v-col cols="12">
                    <v-text-field
                        label="Pregunta"
                        required
                        v-model="name"
                        @change="notifyParent"
                    ></v-text-field>
                </v-col>
            </v-row>
            <!--Question options-->
            <div v-if="type  !== 'abierta'">
                <QuestionOption v-for="(option, optionKey) in options" :key="option.placeholder"
                                :initialValue="option.value" :initialPlaceholder="option.placeholder"
                                :index="optionKey"
                                @deleted="removeQuestionOption"
                                @questionOptionUpdated="updateQuestionOption"
                />
            </div>

        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-tooltip bottom v-if="question.type  !== 'abierta'">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn large icon v-bind="attrs" v-on="on" @click="addAnotherOption()">
                        <v-icon>
                            mdi-plus
                        </v-icon>
                    </v-btn>
                </template>
                <span>Añadir otra opción de respuesta</span>
            </v-tooltip>

            <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn icon large v-bind="attrs" v-on="on"
                           @click="$emit('copyQuestion', {name,competence,type,options})">
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
                           @click="$emit('deleteQuestion', baseIndex)">
                        <v-icon>
                            mdi-delete
                        </v-icon>
                    </v-btn>
                </template>
                <span>Borrar pregunta</span>
            </v-tooltip>
        </v-card-actions>
    </v-card>

</template>

<script>
import Question from "@/models/Question";
import QuestionOption from "./QuestionOption";

export default {
    name: "Question",
    components: {
        QuestionOption
    },
    props: {
        question: Object,
        baseIndex: Number,
    },
    data() {
        return {
            questionModel: new Question(),
            finalOptions: {},
            deleteQuestionDialog: false,
            name: '',
            competence: '',
            type: '',
            options: []
        }
    },
    created() {
        const question = JSON.parse(JSON.stringify(this.question));
        this.name = question.name;
        this.competence = question.competence;
        this.type = question.type;
        this.options = question.options;
        this.finalOptions = question.options;
    },
    methods: {
        updateQuestionOption({index,value,placeholder}){
            this.finalOptions[index].value = value;
            this.finalOptions[index].placeholder = placeholder;
            this.notifyParent();
        },
        notifyParent() {
            this.$emit('questionUpdated', {
                question: new Question(this.type, this.name, this.options, this.competence),
                index: this.baseIndex
            })
        },

        removeQuestionOption(optionKey) {
            this.options.splice(optionKey, 1)
        },
        addAnotherOption() {
            this.options.push({});
        },

    },
}

</script>

<style scoped>

</style>
