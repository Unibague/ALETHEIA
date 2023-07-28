<!DOCTYPE html>
<html>
<head>
    <title>Reporte por docente ALETHEIA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body class="mx-5 my-5" style="font-size: 15px">


<p> Apreciado(a) <strong>{{$data['boss_name']}}:</strong></p>

<p> Desde Vicerrectoría se lleva a cabo el proceso de evaluación docente 360 para la Universidad de Ibagué, mediante el sistema de información Aletheia.</p>

<p> Para el periodo de evaluación actual ({{$data['assessment_period_name']}}), usted ha sido designado como <strong>{{$data['role']}}</strong> de los siguientes docentes:</p>

@foreach($data['teachers_to_evaluate'] as $teacher)
<p style="line-height: 70%"> - {{$teacher}}</p>
@endforeach


<p> Para la universidad es de suma importancia contar con su colaboración y apoyo durante este proceso evaluativo. </p>

<p> Recuerde que usted puede proceder con la evaluación de estos docentes a partir del <strong> {{$data['start_date']}} </strong>
    y tiene plazo máximo hasta el <strong> {{$data['end_date']}} </strong> </p>

<p style="margin-bottom: 100px">  Si tiene alguna inquietud, escribir correo a: <br style="text-underline: #00acc1"> vicerrectoria.desempeno@unibague.edu.co</p>

<p > Saludos cordiales,
    <br> Vicerrectoría Universidad de Ibagué.
    <br> Tel.: (57) + 8 2760010 ext.: 3004</p>


</body>

<script>


</script>

</html>
