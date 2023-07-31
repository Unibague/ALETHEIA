<!DOCTYPE html>
<html>
<head>
    <title>Reporte por docente ALETHEIA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body style="font-size: 15px; background-color: #edf2f7">


<section style="width:75%; padding: 5% 10%">

    <h2 style="text-align: center"> Aletheia - Universidad de Ibagué </h2>

    <div style="background: #ffffff; padding: 5% 5%">



<p> Apreciado(a) <strong>{{$data['name']}}:</strong></p>

<p> Desde Vicerrectoría se lleva a cabo el proceso de evaluación docente 360 para la Universidad de Ibagué, mediante el sistema de información Aletheia.</p>


@if($data['role'] === 'Autoevaluación')

    <p> Para el periodo de evaluación actual ({{$data['assessment_period_name']}}), usted será evaluado de manera integral por parte de todos los actores de la evaluación 360</p>

    <p> Parte de esta evaluación incluye diligenciar su <strong> {{$data['role']}} </strong></p>

    <p> Para la Universidad es de suma importancia contar con su colaboración y apoyo durante este proceso evaluativo. </p>

    <p> Recuerde que usted puede proceder con el diligenciamiento de esta autoevaluación a partir del <strong> {{$data['start_date']}} </strong>
        y tiene plazo máximo hasta el <strong> {{$data['end_date']}} </strong> </p>

@endif



@if($data['role'] === 'Jefe de Evaluación 360°' || $data['role'] === 'Par de Evaluación 360°' )

<p> Para el periodo de evaluación actual ({{$data['assessment_period_name']}}), usted ha sido designado como <strong>{{$data['role']}}</strong> de los siguientes docentes:</p>

@foreach($data['teachers_to_evaluate'] as $teacher)
<p style="line-height: 70%"> - {{$teacher}}</p>
@endforeach


<p> Para la Universidad es de suma importancia contar con su colaboración y apoyo durante este proceso evaluativo. </p>

<p> Recuerde que usted puede proceder con la evaluación de estos docentes a partir del <strong> {{$data['start_date']}} </strong>
    y tiene plazo máximo hasta el <strong> {{$data['end_date']}} </strong> </p>

@endif



@if($data['role'] === 'Estudiante')

    <p> Para el periodo de evaluación actual, desde su rol de <strong>{{$data['role']}}</strong>,
        y de acuerdo con sus asignaturas matriculadas, usted debe evaluar a los siguientes docentes:</p>

    @foreach($data['teachers_to_evaluate'] as $teacher)
        <p style="line-height: 90%"> Asignatura: <strong> {{$teacher->group_name}} </strong> -  Docente:  <strong>{{$teacher->teacher_name}}</strong></p>
    @endforeach

    <p> Para la Universidad es de suma importancia contar con su colaboración y apoyo durante este proceso evaluativo. </p>

    <p> Recuerde que usted puede proceder con la evaluación de estos docentes a partir del <strong> {{$data['start_date']}} </strong>
        y tiene plazo máximo hasta el <strong> {{$data['end_date']}} </strong> </p>

@endif


<p style="margin-bottom: 100px">En caso de cualquier inquietud o pregunta, puede enviar un correo a: <br style="text-underline: #00acc1"> vicerrectoria.desempeno@unibague.edu.co</p>


    <div style="margin-top: 30px">
    <p>Vicerrectoría Universidad de Ibagué.
    <br> Tel.: (57) + 8 2760010 ext.: 3004</p>

<img src="{{$message->embed(public_path().'/images/reminderEmailLogo.png')}}" style="max-width:80%; object-fit: contain; margin-top: 0px">

    </div>

</div>


</section>

</body>

</html>
