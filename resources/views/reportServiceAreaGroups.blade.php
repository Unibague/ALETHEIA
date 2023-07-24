<!DOCTYPE html>
<html>
<head>
    <title>Reporte por docente ALETHEIA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body class="mx-5 my-5">

<img src="public/images/whiteLogo.png"
     style="max-height: 300px; max-width:300px; object-fit: contain">


<p  style="margin-top: 30px"> Sistema de Información para evaluación docente: <strong> ALETHEIA </strong></p>

<p> Periodo de evaluación: <strong> {{$assessmentPeriodName}} </strong> </p>

<p style="margin-bottom: 30px"> Visualizando desempeño del docente  <strong> {{ucwords($teacherName)}} </strong> en el área de servicio: <strong>

        {{$teacherResults[0]->service_area_name}}  </strong></p>

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>
        <th scope="col">Asignatura</th>
        <th scope="col">Grupo</th>
        @foreach($labels as $label)
        <th scope="col">{{$label}}</th>
        @endforeach
    </tr>
    </thead>

    <tbody>

    @foreach($teacherResults as $teacherResult)
    <tr>
        <td style="text-transform: capitalize">Estudiante</td>
        <td>{{$teacherResult->group_name}}</td>
        <td>{{$teacherResult->group_number}}</td>
        <td>{{$teacherResult->service_area_name}}</td>
        <td>{{$teacherResult->first_competence_average}}</td>
        <td>{{$teacherResult->second_competence_average}}</td>
        <td>{{$teacherResult->third_competence_average}}</td>
        <td>{{$teacherResult->fourth_competence_average}}</td>
        <td>{{$teacherResult->fifth_competence_average}}</td>
        <td>{{$teacherResult->sixth_competence_average}}</td>
    </tr>
    @endforeach
    </tbody>



</table>


<div style="margin-top: 20px" >
    <img src="https://quickchart.io/chart?c={{$chart}}" style="max-width: 80%; margin-left: 5%; margin-top: 5%" >
</div>


</body>



</html>
