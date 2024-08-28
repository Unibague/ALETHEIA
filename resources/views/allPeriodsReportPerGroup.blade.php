<table>
    <thead>
    <tr>
        <th>Periodo</th>
        <th>Asignatura</th>
        <th>Código de la asignatura</th>
        <th>Grupo</th>
{{--        <th>Cédula</th>--}}
        <th>Docente</th>
        <th>Área de servicio</th>
        <th># de estudiantes del curso</th>
        <th># de estudiantes que presentan la evaluación</th>
        <th>Calificación definitiva</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($tableData as $rowData)
        <tr>
            @foreach ($rowData as $data)
                <td>{{ $data }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
