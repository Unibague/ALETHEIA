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

<p> <strong> Reporte evaluación 360° </strong> </p>

<p style="margin-bottom: 30px"> Visualizando desempeño en evaluación 360° del docente: <strong> {{ucwords($teacherName)}} </strong></p>

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>
        @foreach($labels as $label)
        <th scope="col">{{$label}}</th>
        @endforeach
        <th scope="col"> Actores que participaron</th>
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

<div style="margin-top: 20px; margin-bottom: 20px; width: 700px;height: 900px">
    <img src="https://quickchart.io/chart?c={{$chart}}" style="margin-left: 5%; margin-top: 5%;" >
</div>


<p style="font-weight: bold"> Descripción por grupos (Perspectiva del estudiante) </p>

<table class="table" style="max-width: 85%; margin-top: 15px" >
    <thead>
    <tr>
        <th scope="col">Asignatura</th>
        <th scope="col">Número de grupo</th>
        @foreach($labels as $label)
            <th scope="col">{{$label}}</th>
        @endforeach
        <th scope="col"> Actores involucrados</th>
        <th scope="col"> Actores totales</th>
    </tr>
    </thead>

    <tbody>

    @foreach($groupsResults as $group)
        <tr>
            <td style="text-transform: capitalize">{{$group->group_name}}</td>
            <td style="text-transform: capitalize">{{$group->group_number}}</td>
            <td>{{$group->first_competence_average}}</td>
            <td>{{$group->second_competence_average}}</td>
            <td>{{$group->third_competence_average}}</td>
            <td>{{$group->fourth_competence_average}}</td>
            <td>{{$group->fifth_competence_average}}</td>
            <td>{{$group->sixth_competence_average}}</td>
            <td>{{$group->students_amount_reviewers}}</td>
            <td>{{$group->students_amount_on_group}}</td>
        </tr>
    @endforeach
    </tbody>
</table>


@if(count($openAnswersFromTeachers) > 0)
    <div style="margin-top: 30px">
    <p style="font-weight: bold"> Comentarios del jefe, par y de autoevaluación</p>
    @foreach($openAnswersFromTeachers as $question)
        <p class="black--text pt-2"> Pregunta: </p>
        <p style="font-weight: bold; margin-left: 10px">{{$question->question_name}}</p>
        <div style="margin-left: 20px">
        @foreach($question->answers as $person)
            <p style="font-weight: bold"> {{$person->name}} - ({{$person->unit_role}})</>
            @foreach($person->answers as $answer)
                <p>{{$answer}}</p>
            @endforeach
        @endforeach
        </div>
    @endforeach
</div>
    @endif


@if(count($openAnswersFromStudents) > 0)
    <div style="margin-top: 30px">
    <p style="font-weight: bold"> Comentarios de Estudiantes</p>
    @foreach($openAnswersFromStudents as $question)
            <p class="black--text pt-2"> Pregunta: </p>
            <p style="font-weight: bold; margin-left: 10px">{{$question->question_name}}</p>
            <div style="margin-left: 20px">
            @foreach($question->groups as $group)
                <p><span style="font-weight: bold "> {{$group->group_name}} - Grupo {{$group->group_number}}</span></p>
                @foreach($group->answers as $answer)
                    <p>- {{$answer}} </p>
                @endforeach
            @endforeach
            </div>
    @endforeach
    </div>
@endif


<h6 style="margin-top: 100px; font-weight: bold" > Reporte generado en: {{$timestamp}}</h6>
</body>


<script>
    window.addEventListener('load', function (){
            window.print();
    })
</script>

</html>
