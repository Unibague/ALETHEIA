<!DOCTYPE html>
<html>
<head>
    <title>Aletheia_reportes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>

        body {
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            margin: 2px 1px;
        }

        table {
            font-size: 12px;
            margin: 5px 0;
        }

        table th, table td {
            white-space: normal;
            word-break: normal;
            overflow: hidden;
        }

        .no-break-table {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .chart-container {
            width: 70%; /* Adjust this value to make images smaller or larger */
            margin: 0 auto; /* Centers the container if smaller than full width */
        }

        .chart-image {
            max-width: 100%;
            height: auto;
        }

    </style>
</head>

<body>


<table style="width: 100%; margin-bottom: 15px;">
    <tr>
        <td style="width: 30%; vertical-align: top; padding-right: 20px;">
            <img src="images/aletheia_logo.png" alt="Aletheia Logo" style="max-width: 80%; height: auto;">
        </td>
        <td style="width: 70%; vertical-align: top">
            <p style="font-size: 15px"><strong>Percepción de la docencia</strong></p>
            <p style="font-size: 15px; margin-top: 0"> Periodo de evaluación: <strong>{{ $assessmentPeriodName }}</strong></p>
        </td>
    </tr>
</table>

{{--<div class="report-header">--}}
{{--    <img src="images/aletheia_logo.png" alt="Aletheia Logo">--}}
{{--    <div class="report-text">--}}
{{--        <p><strong>Percepción de la Docencia</strong></p>--}}
{{--        <p>Periodo de evaluación: <strong>{{ $assessmentPeriodName }}</strong></p>--}}
{{--    </div>--}}
{{--</div>--}}

    <p><strong>Reporte por facultad</strong></p>


<p>Visualizando a la facultad: <strong>{{ ucwords($facultyName) }}</strong></p>

<table style="width: 100%; table-layout: fixed; margin-top: 19px; margin-bottom: 30px">
    <tr>
        <td :style="{ width: satisfactionChart ? '50%' : '100%', textAlign: 'center', verticalAlign: 'middle', padding: '10px' }">
            <div class="chart-container">
                <img src="{{ $overallAverageChart }}" alt="Gráfica promedio general" class="chart-image">
            </div>
        </td>
        <!-- Only display the second chart if `satisfactionChart` is not null -->
        @if($satisfactionChart)
            <td style="width: 50%; text-align: center; vertical-align: middle; padding: 10px;">
                <div class="chart-container">
                    <img src="{{ $satisfactionChart }}" alt="Gráfica satisfacción" class="chart-image">
                </div>
            </td>
        @endif
    </tr>
</table>

<p style="margin-top: 40px; font-weight: bold">Reporte generado en: {{ \Carbon\Carbon::now() }}</p>

</body>
</html>
