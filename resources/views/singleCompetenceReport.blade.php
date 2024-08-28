<table>
    <thead>
    <tr>
        <th>Docente</th>
        <th>Asignatura</th>
        <th>Grupo</th>
        @foreach ($questions as $question)
            <th>{{ $question }}</th>
        @endforeach
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
