@extends('layouts.app')
@section('content')

<div class='container'>
  <ul class='list-group'>
    @foreach($instructors AS $instructor)
      <li class='list-group-item'>
        <a href='{{action('DisplayController@showInstructor',[$instructor->id])}}'>{{$instructor->name}}</a>
      </li>
    @endforeach
  </ul>
</div>
@endsection
