<template>
    <div class="pie-chart-container">
        <div class="chart-wrapper">
            <svg ref="chartSvg" width="200" height="100" viewBox="0 0 42 42">
                <!-- Semi-doughnut background -->
                <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#d2d3d4" stroke-width="3"></circle>

                <!-- Semi-doughnut foreground (data) -->
                <circle ref="donutSegment" class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#00aaff" stroke-width="3"></circle>

                <!-- Centered text -->
                <text x="50%" y="50%" class="donut-number" text-anchor="middle" dominant-baseline="middle" font-size="8px" fill="#333">{{ value }}</text>
            </svg>
        </div>
        <h2 class="chart-title">{{ title }}</h2>
    </div>
</template>

<style scoped>
.pie-chart-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 100%;
}

.chart-wrapper {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-bottom: 10px; /* Adjust as needed for spacing between chart and title */
}

.chart-title {
    margin: 0;
    padding: 0;
    font-size: 16px; /* Adjust as needed */
    line-height: 1.2;
    max-width: 80%; /* Ensures title doesn't stretch too wide */
}

.donut-segment {
    stroke-dasharray: 0 100; /* Start at 0% fill */
    stroke-dashoffset: 25;
    transition: stroke-dasharray 0.5s ease-out; /* Animation duration */
}
</style>

<script>
import html2canvas from "html2canvas";

export default {
    name: 'PieChart',
    props: {
        value: {
            type: [Number, String],
            required: true
        },
        title: {
            type: String,
            required: true
        },
        max: {
            type: Number,
            default: 5
        }
    },
    watch: {
        value: {
            immediate: true,
            handler() {
                this.$nextTick(() => {
                    this.updateChart();
                });
            }
        }
    },
    methods: {
        updateChart() {
            const percentage = (parseFloat(this.value) / this.max) * 100;
            const donutSegment = this.$refs.donutSegment;
            donutSegment.style.strokeDasharray = `${percentage} ${100 - percentage}`;
        },

        generateChartImage(format = 'image/png', quality = 1, scaleFactor = 2) {
            return new Promise((resolve) => {
                this.$nextTick(() => {
                    setTimeout(() => {
                        // Use html2canvas with options to prevent black background
                        html2canvas(this.$el, {
                            scale: scaleFactor,   // Higher scale for better quality (e.g., 2x or 3x)
                            useCORS: true,        // Enable cross-origin resource sharing if external assets are used
                            backgroundColor: format === 'image/png' ? null : '#fff' // Transparent for PNG, white for JPEG
                        }).then(canvas => {
                            resolve(canvas.toDataURL(format, quality)); // Export with specified format and quality
                        });
                    }, 100); // Delay to ensure rendering is complete
                });
            });
        }
    }
}
</script>
