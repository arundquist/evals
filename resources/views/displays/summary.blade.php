@extends('layouts.app')

@section('content')

<div class='container'>
  <h1>{{$instructor->name}}</h1>
  <ul class='list-group'>
    @foreach ($ayavgs AS $key=>$arr)
      <li class='list-group-item'>
        {{{$key}}}: course averages between {{{number_format(min($arr),1)}}} and {{{number_format(max($arr),1)}}}
      </li>
    @endforeach
  </ul>
  
  <table class='table'>
    <thead>
      <tr>
        <th>Term</th>
        <th>Course</th>
        <th>Evals</th>
        <th>Class average</th>
      </tr>
    </thead>
    <tbody>

      @foreach($courses AS $course)
        <tr bgcolor="{{$colors[$course->semester->ay]}}">
          <td>{{$course->semester->ay}} {{$course->semester->season}}</td>
          <td><a href='{{action('DisplayController@getDept',[$course->dept])}}'>{{$course->dept}}</a>
              <a href='{{action('DisplayController@getAllWithSameCourse', [$course->dept,$course->number])}}'>{{$course->number}}</a>
              {{$classinfo[$course->id]['title']}}
              @if($classinfo[$course->id]['credits'] != '')
                ({{$classinfo[$course->id]['credits']}} cr)
              @endif
            </td>
          <td>{{$evalcounts[$course->id]}}/{{$classinfo[$course->id]['enrollment']}}
            @if ($classinfo[$course->id]['enrollment'])
              {{number_format($evalcounts[$course->id]/$classinfo[$course->id]['enrollment']*100)}}%
            @endif
          </td>





          <td>
            {{$avgs[$course->id]}}
              [{{Stats::percentile($avgs[$course->id],$means["all"])}}]
          </td>
        </tr>

      @endforeach

    </tbody>
  </table>





@endsection
