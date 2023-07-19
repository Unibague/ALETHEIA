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

<p style="margin-bottom: 30px"> Visualizando desempeño en evaluación 360° del docente: <strong> {{ucwords($teacherName)}} </strong></p>

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>
        @foreach($labels as $label)
        <th scope="col">{{$label}}</th>
        @endforeach
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
    </tr>
    @endforeach
    </tbody>



</table>

<!--<div class="container" >

</div>-->


<div style="margin-top: 20px" >
    <img src="https://quickchart.io/chart?c={{$chart}}" style="max-width: 80%; margin-left: 5%; margin-top: 5%" >
</div>

<!--
<div class="container" >
    <img src="https://quickchart.io/chart?c={type:%27bar%27,data:{labels:[2012,2013,2014,2015,2016],datasets:[{label:%27Users%27,data:[120,60,50,180,120]}]}}" style="max-width: 500px">
</div>
-->


<!--    <img src="https://quickchart.io/chart?c={type:'line',data:{labels:['C1', 'C2', 'C3', 'C4', 'C5','C6'],datasets:[{label:'Desempeño del docente',data:[120,60,50,180,120,130]}],
options:{legend:{display: false}, scales:{x:{title:{display:true, text:'competencias'}}}}}},
" style="max-width: 500px">-->


</body>



</html>
