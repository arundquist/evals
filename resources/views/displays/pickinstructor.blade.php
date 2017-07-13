@extends('layouts.app')
@section('content')

<div class='container'>
  <ul class='list-group'>
    @foreach($instructors AS $instructor)
      <li class='list-group-item'>
        <a href='{{action('DisplayController@showInstructor',[$instructor->id])}}'>{{$instructor->name}}</a>
        link: <a href='{{action('DisplayController@getHashedId', [md5($instructor->id)])}}'>{{action('DisplayController@getHashedId', [md5($instructor->id)])}}</a>
      </li>
    @endforeach
  </ul>
</div>
@endsection
