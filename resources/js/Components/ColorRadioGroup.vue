<template>
    <div class="color-radio-group-wrapper">
        <div class="color-radio-group">
            <v-radio-group
                v-model="localValue"
                row
                dense
                :rules="rules"
                @change="$emit('input', localValue)"
            >
                <div
                    v-for="(option, index) in options"
                    :key="index"
                    class="option-container"
                >
                    <div
                        class="color-rectangle"
                        :style="{ backgroundColor: getColor(index) }"
                    >
                        <v-radio
                            :value="index"
                            :color="getTextColor(getColor(index))"
                            class="ma-0 pa-0 radio-centered"
                        ></v-radio>
                    </div>
                    <div
                        class="option-label"
                        :style="{ color: getColor(index) }"
                    >
                        {{ option.placeholder }}
                    </div>
                </div>
            </v-radio-group>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ColorRadioGroup',
    props: {
        value: {
            type: Number,
            default: null
        },
        options: {
            type: Array,
            required: true
        },
        rules: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            localValue: this.value,
            colorScheme: ['#ff2300', '#ff9b00', '#ffcc00', '#9efc05', '#00cf1f']
        }
    },
    methods: {
        getColor(index) {
            const totalOptions = this.options.length;
            if (totalOptions <= this.colorScheme.length) {
                const colorIndex = Math.floor((index / (totalOptions - 1)) * (this.colorScheme.length - 1));
                return this.colorScheme[colorIndex];
            } else {
                return this.colorScheme[index % this.colorScheme.length];
            }
        },
        getTextColor(color) {
            // Ensure color is a string and starts with '#'
            if (typeof color !== 'string' || !color.startsWith('#')) {
                console.error('Invalid color format:', color);
                return '#000000'; // Default to black if invalid color
            }

            // Convert hex to RGB
            const r = parseInt(color.slice(1, 3), 16);
            const g = parseInt(color.slice(3, 5), 16);
            const b = parseInt(color.slice(5, 7), 16);

            // Calculate relative luminance
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

            // Return black for light colors, white for dark colors
            return luminance > 0.5 ? '#000000' : '#FFFFFF';
        }
    },
    watch: {
        value(newValue) {
            this.localValue = newValue;
        }
    }
}
</script>

<style scoped>
.color-radio-group-wrapper {
    display: flex;
    justify-content: center;
    width: 100%;
}

.color-radio-group {
    display: flex;
    justify-content: center;
    max-width: 900px;
    width: 100%;
}

.option-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    max-width: 100%; /* Ensures each option takes up no more than 20% of the width */
}

.color-rectangle {
    width: 100%;
    padding: 0px 30px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.radio-centered {
    margin: 0 !important;
}

.option-label {
    font-size: 12px;
    text-align: center;
    margin-top: 4px;
    font-weight: bold;
}
</style>
