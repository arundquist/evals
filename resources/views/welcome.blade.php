@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    <ul class="list-group">
                      <li class="list-group-item">
                        <a href='{{action('DisplayController@pickInstructor')}}'>Pick Instructor (requires approved login)</a>
                      </li>
                      <li class="list-group-item">
                        <a href='{{action('DisplayController@getAllLevels')}}'>All levels (1000, 3000, 5000)</a>
                      </li>
                      <li class="list-group-item">
                        <a href='{{action('DisplayController@getAllDepts')}}'>All departments</a>
                      </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
