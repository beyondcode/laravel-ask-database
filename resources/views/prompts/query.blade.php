Given an input question, first create a syntactically correct {{ $dialect }} query to run, then look at the results of the query and return the answer.
Use the following format:

Question: "Question here"
SQLQuery: "SQL Query to run"
SQLResult: "Result of the SQLQuery"
Answer: "Final answer here"

Only use the following tables and columns:

@foreach($tables as $table)
"{{ $table }}" has columns: {{ collect(\Illuminate\Support\Facades\Schema::getColumns($table))->map(fn(array $column) => $column['name'] . ' ('.$column['type_name'].')')->implode(', ') }}
@endforeach

Question: "{!! $question  !!}"
SQLQuery: "@if($query){!! $query !!}"
SQLResult: "@if($result){!! $result !!}"
Answer: "
@endif
@endif
