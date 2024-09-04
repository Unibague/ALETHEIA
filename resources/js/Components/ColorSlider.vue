<template>
    <div>
        <div class="text-center mb-2">
            <span class="text-h6">{{ currentOption ? currentOption.placeholder : '' }}</span>
        </div>
        <v-slider
            v-model="internalValue"
            :min="0"
            :max="options.length - 1"
            :step="1"
            ticks="always"
            tick-size="4"
            :track-color="trackColor"
            :thumb-color="thumbColor"
            @input="updateValue"
        >
            <template v-slot:thumb-label="{ value }">

            </template>
        </v-slider>
    </div>
</template>

<script>
export default {
    name: 'ColorSlider',
    props: {
        value: {
            type: [String, Number],
            default: null,
        },
        options: {
            type: Array,
            required: true,
        },
        rules: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            internalValue: 0,
            colors: ['#FF0000', '#FF7F00', '#0051ff', '#00FF00', '#00FF7F', '#00FFFF'],
        };
    },
    computed: {
        currentOption() {
            return this.options[this.internalValue];
        },
        trackColor() {
            const startColor = this.colors[0];
            const endColor = this.colors[this.colors.length - 1];
            return `linear-gradient(to right, ${startColor}, ${endColor})`;
        },
        thumbColor() {
            const index = Math.min(this.internalValue, this.colors.length - 1);
            return this.colors[index];
        },
    },
    watch: {
        value: {
            immediate: true,
            handler(newValue) {
                if (newValue !== null) {
                    this.internalValue = this.options.findIndex(option => option.value === newValue);
                }
            },
        },
    },
    methods: {
        updateValue(value) {
            this.$emit('input', this.options[value].value);
        },
    },
};
</script>
