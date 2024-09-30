@foreach ($questions as $question)
    <p class="mt-3" style="font-weight: bold; margin-left: 30px">{{ $question["question"] }}</p>
    @foreach ($question["commentType"] as $commentType)
        <p style="margin-left: 35px"> - {{ $commentType["type"] }} ({{ count($commentType["answers"]) }})</p>
        <ul class="mt-1">
            @foreach ($commentType["answers"] as $answer)
                <li style="margin-left: 40px">{{ $answer }}</li>
            @endforeach
        </ul>
    @endforeach
@endforeach
