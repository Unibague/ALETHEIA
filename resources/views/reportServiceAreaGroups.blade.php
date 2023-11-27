<!DOCTYPE html>
<html>
<head>
    <title>Reporte por docente ALETHEIA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body class="mx-5 my-5">

<img src="/images/whiteLogo.png"
     style="max-height: 300px; max-width:300px; object-fit: contain">

<p  style="margin-top: 30px"> Sistema de Información para evaluación docente: <strong> ALETHEIA </strong></p>

<p> Periodo de evaluación: <strong> {{$assessmentPeriodName}} </strong> </p>

<p> <strong> Reporte por grupos </strong> </p>

<p style="margin-bottom: 30px"> Visualizando desempeño del docente  <strong> {{ucwords($teacherName)}} </strong>:

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>
        <th scope="col">Asignatura</th>
        <th scope="col">Grupo</th>
        @foreach($labels as $label)
        <th scope="col">{{$label}}</th>
        @endforeach
        <th scope="col"> Estudiantes que participaron</th>
        <th scope="col"> Estudiantes totales</th>
    </tr>
    </thead>

    <tbody>

    @foreach($teacherResults as $teacherResult)
    <tr>
        <td style="text-transform: capitalize">Estudiante</td>
        <td>{{$teacherResult->group_name}}</td>
        <td>{{$teacherResult->group_number}}</td>
        <td>{{$teacherResult->first_competence_average}}</td>
        <td>{{$teacherResult->second_competence_average}}</td>
        <td>{{$teacherResult->third_competence_average}}</td>
        <td>{{$teacherResult->fourth_competence_average}}</td>
        <td>{{$teacherResult->fifth_competence_average}}</td>
        <td>{{$teacherResult->sixth_competence_average}}</td>
        <td>{{$teacherResult->students_amount_reviewers}}</td>
        <td>{{$teacherResult->students_amount_on_group}}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div style="margin-top: 20px; width: 700px;height: 700px" >
    <img src="https://quickchart.io/chart?c={{$chart}}" style="margin-left: 5%; margin-top: 5%" >
</div>


@if(count($openAnswersFromStudents) > 0)
    <div style="margin-top: 30px">
        <p style="font-weight: bold; margin-top: 15px"> Comentarios de parte de Estudiantes</p>
        @foreach($openAnswersFromStudents as $question)
            <p class="black--text pt-2"> Pregunta: </p>
            <p style="font-weight: bold">{{$question->question_name}}</p>
            <div style="margin-left: 20px">
                @foreach($question->service_areas as $service_area)
                    <p class="pt-3">Área de Servicio: <span style="font-weight: bold;" > {{$service_area->service_area_name}} </span></p>
                    <div style="margin-left: 30px">
                        @foreach($service_area->groups as $group)
                            <p><span style="font-weight: bold "> {{$group->group_name}} - Grupo {{$group->group_number}}</span></p>
                            <div style="margin-left: 40px">
                                @foreach($group->answers as $answer)
                                    <p>- {{$answer}} </p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

<h6 style="margin-top: 100px; font-weight: bold" > Reporte generado en: {{$timestamp}}</h6>

<script>
    window.addEventListener('load', function (){
        window.print();
    })
</script>

</body>
</html>
