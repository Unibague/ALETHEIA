<template>
    <v-row>
        <v-col cols="2" class="d-flex align-center">
            <v-text-field
                outlined
                type="number"
                label="Valor"
                required
                v-model="value"
                min="1"
                @input="notifyParent"

            ></v-text-field>
        </v-col>
        <v-col cols="7" class="d-flex align-center">
            <v-text-field
                outlined
                label="Opción de respuesta"
                required
                v-model="placeholder"
                @change="notifyParent"
            ></v-text-field>
        </v-col>
        <v-col cols="1">
            <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn icon large v-bind="attrs" v-on="on" class="mt-1"
                           @click="$emit('deleted',index)">
                        <v-icon>
                            mdi-delete
                        </v-icon>
                    </v-btn>
                </template>
                <span>Borrar opción de respuesta</span>
            </v-tooltip>
        </v-col>
    </v-row>

</template>

<script>
export default {
    name: "QuestionOption",
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
