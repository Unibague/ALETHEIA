<template>
    <v-row>
        <v-col cols="7" class="d-flex align-center">
            <v-text-field
                outlined
                label="Opción de respuesta"
                required
                value=""
                v-model="placeholder"
                :readonly="true"
                @change="notifyParent"
            ></v-text-field>
        </v-col>
    </v-row>

</template>

<script>
export default {
    name: "OpenQuestionOption",
    data() {
        return {
            value: '',
            placeholder: ''
        }
    },
    methods: {
        notifyParent() {
            this.$emit('questionOptionUpdated', {
                index: this.index,
                value: this.value,
                placeholder: this.placeholder
            });

        },
    },
    created() {
        this.value = this.initialValue;
        this.placeholder = this.initialPlaceholder;
    },
    props: {
        initialValue: String,
        initialPlaceholder: String,
        index: Number,
        getData: Function
    }

}

</script>

<style scoped>

</style>
