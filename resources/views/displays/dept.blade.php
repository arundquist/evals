@extends('layouts.app')

@section('content')

<div class='container'>

  <table class='table'>
    <thead>
      <tr>
        <th></th>
        @foreach($questions AS $question)
          <th>{{$question->question}}</th>
        @endforeach
        <th>Dept average</th>
      </tr>
    </thead>
    <tbody>

      @foreach($allscores AS $akey=>$scores)
        <tr>
          <td>{{$depts[$akey]}}</td>

          @foreach($scores AS $key=> $qs)
            <td>{!! Sparkflex::sparkflex(Stats::bins($qs)) !!}<br/>

              {{Stats::avg($qs)}}
            </td>


          @endforeach
          <td>
            {{$avgs[$akey]}}
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>




</div>

@endsection
