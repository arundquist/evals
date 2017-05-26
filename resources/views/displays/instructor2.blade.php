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
          <td>{{$evalcounts[$course->id]}}/{{$classinfo[$course->id]['enrollment']}}
            @if ($classinfo[$course->id]['enrollment'])
              {{number_format($evalcounts[$course->id]/$classinfo[$course->id]['enrollment']*100)}}%
            @endif
          </td>

          @foreach($allbins[$course->id] AS $key=> $qs)
            <td>{!! Sparkflex::sparkflex(Stats::augmentBins($qs)) !!}<br/>

              <a href="#" rel="popover" data-popover-content="#myPopover{{$course->id}}{{$key}}">{{Stats::avgBin($qs)}}
                ({{isset($comments[$course->id][$key])?count($comments[$course->id][$key]):0}})</a>
            </td>


          @endforeach

          <td>
            <a href="#" rel="popover" data-popover-content="#gencomments{{$course->id}}">{{$avgs[$course->id]}} ({{count($gencomments[$course->id])}})</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>



@foreach ($comments as $course_id=>$qcomments)
  @foreach ($qcomments AS $question_id=>$qc)
    <div id="myPopover{{$course_id}}{{$question_id}}" class="hide">
      @foreach ($qc AS $score=>$single)

        <p>{{$score}}: {{$single}}</p>

      @endforeach
    </div>
  @endforeach
@endforeach

@foreach ($gencomments as $course_id=>$ccomments)

    <div id="gencomments{{$course_id}}" class="hide">
      @foreach($ccomments AS $comment)
        <p>{{$comment->comment}}</p>
      @endforeach
    </div>

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
