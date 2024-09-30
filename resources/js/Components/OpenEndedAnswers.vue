<template>
    <div>
        <div v-for="(question, questionIndex) in questions" :key="questionIndex" class="mb-4">
            <h4 class="question-text mb-2">{{ question.question }}</h4>
            <v-expansion-panels multiple>
                <v-expansion-panel v-for="(commentType, typeIndex) in question.commentType" :key="typeIndex">
                    <v-expansion-panel-header>
                        {{ commentType.type }} ({{ commentType.answers.length }})
                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                        <ul>
                            <li v-for="(answer, answerIndex) in commentType.answers" :key="answerIndex">
                                {{ answer }}
                            </li>
                        </ul>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OpenEndedAnswers',
    props: {
        questions: {
            type: Array,
            required: true
        }
    }
}
</script>

<style scoped>
.question-text {
    font-size: 1.1em;
    font-weight: 500;
    color: #34495e;
}
</style>
