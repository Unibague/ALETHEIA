<!DOCTYPE html>
<html>
<head>
    <title>Reporte por docente ALETHEIA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body class="mx-5 my-5">

<img src="/images/whiteLogo.png"
     style="max-height: 300px; max-width:300px; object-fit: contain" alt="Logo_universidad">

<p  style="margin-top: 30px"> Sistema de Información para evaluación docente: <strong> ALETHEIA </strong></p>

<p> Periodo de evaluación: <strong> {{$assessmentPeriodName}} </strong> </p>

<p style="margin-bottom: 30px"> Visualizando desempeño en evaluación 360° del docente: <strong> {{ucwords($teacherName)}} </strong></p>

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>
        @foreach($labels as $label)
        <th scope="col">{{$label}}</th>
        @endforeach
        <th scope="col"> Actores involucrados</th>
        <th scope="col"> Actores totales</th>
    </tr>
    </thead>

    <tbody>

    @foreach($teacherResults as $teacherResult)
    <tr>
        <td style="text-transform: capitalize">{{$teacherResult->unit_role}}</td>
        <td>{{$teacherResult->first_competence_average}}</td>
        <td>{{$teacherResult->second_competence_average}}</td>
        <td>{{$teacherResult->third_competence_average}}</td>
        <td>{{$teacherResult->fourth_competence_average}}</td>
        <td>{{$teacherResult->fifth_competence_average}}</td>
        <td>{{$teacherResult->sixth_competence_average}}</td>
        <td>{{$teacherResult->aggregate_students_amount_reviewers}}</td>
        <td>{{$teacherResult->aggregate_students_amount_on_360_groups}}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div style="margin-top: 20px">
    <img src="https://quickchart.io/chart?c={{$chart}}" style="max-width: 80%; margin-left: 5%; margin-top: 5%" >
</div>


<h5 style="margin-top: 100px" > Reporte generado en: {{$timestamp}}</h5>



</body>


<script>

    window.addEventListener('load', function (){

            window.print();

    })



</script>

</html>
