@extends('layouts.app')
@section('content')

<div class='container'>
  <ul class='list-group'>
    @foreach($instructors AS $instructor)
      <li class='list-group-item'>
        <a href='{{action('DisplayController@showInstructor',[$instructor->id])}}'>{{$instructor->name}}</a>
        <a href='{{action('DisplayController@getHashedId', [md5($instructor->id)])}}'>hash link</a>
        <a href='{{action('DisplayController@getInstructorSummary',[$instructor->id])}}'>summary</a>
      </li>
    @endforeach
  </ul>
</div>
@endsection
