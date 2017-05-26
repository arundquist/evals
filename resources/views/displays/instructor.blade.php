@extends('layouts.app')

@section('content')

<div class='container'>
  <h1>{{$instructor->name}}</h1>
  <table class='table'>
    <thead>
      <tr>
        <th>Term</th>
        <th>Course</th>
        <th>Evals</th>
        @foreach($questions AS $question)
          <th>{{$question->question}}</th>
        @endforeach
        <th>Class average</th>
      </tr>
    </thead>
    <tbody>

      @foreach($courses AS $course)
        <tr>
          <td>{{$course->semester->ay}} {{$course->semester->season}}</td>
          <td><a href='{{action('DisplayController@getDept',[$course->dept])}}'>{{$course->dept}}</a>
              <a href='{{action('DisplayController@getLevel', [$course->number])}}'>{{$course->number}}</a></td>
          <td>{{$scores[$course->id][1]->count()}}</td>
          @foreach($scores[$course->id] AS $key=> $qs)
            <td>{!! Sparkflex::sparkflex(Stats::bins($qs)) !!}<br/>

              <a href="#" rel="popover" data-popover-content="#myPopover{{$course->id}}{{$key}}">{{Stats::avg($qs)}} ({{Stats::numComments($qs)}})</a>
            </td>


          @endforeach
          <td>
            <a href="#" rel="popover" data-popover-content="#gencomments{{$course->id}}">{{$avgs[$course->id]}} ({{Stats::numGenComments($course)}})</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>



@foreach ($scores as $ckey=>$c)
  @foreach ($c AS $key=>$q)
    <div id="myPopover{{$ckey}}{{$key}}" class="hide">
      @foreach ($q AS $single)
        @if(!(is_null($single->comment)))
        <p>{{$single->score}}: {{$single->comment->comment}}</p>
        @endif
      @endforeach
    </div>
  @endforeach
@endforeach

@foreach ($courses as $course)
  @if(!(is_null($course->gencomments)))
    <div id="gencomments{{$course->id}}" class="hide">
      @foreach($course->gencomments AS $comment)
        <p>{{$comment->comment}}</p>
      @endforeach
    </div>
  @endif
@endforeach

</div>
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
$(function(){
    $('[rel="popover"]').popover({
        container: 'body',
        html: true,
        content: function () {
            var clone = $($(this).data('popover-content')).clone(true).removeClass('hide');
            return clone;
        }
    }).click(function(e) {
        e.preventDefault();
    });
});
</script>
@endsection
