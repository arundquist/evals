@extends('layouts.app')

@section('content')

<div class='container'>
  <h1>Departments</h1>

  <table class='table'>
    <thead>
      <tr>
        <th>Dept</th>

        @foreach($questions AS $question)
          <th>{{$question->question}}</th>
        @endforeach
        <th>Class average</th>
      </tr>
    </thead>
    <tbody>

      @foreach($depts AS $dept)
        <tr>
          <td>{{$dept}}</td>




          @foreach ($questions AS $qid=>$question)
            @if (!isset($all[$dept]['bins'][$question->id]))
              <td>nothing here</td>
            @else
            <td>{!! Sparkflex::sparkflex(Stats::augmentBins($all[$dept]['bins'][$question->id])) !!}<br/>

              {{Stats::avgBin($all[$dept]['bins'][$question->id])}}
            </td>
            @endif

          @endforeach


          <td>
            {{$all[$dept]['avg']}}
          </td>

        </tr>
      @endforeach
    </tbody>
  </table>





</div>

@endsection
