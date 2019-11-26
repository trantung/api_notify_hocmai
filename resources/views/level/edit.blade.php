@extends('common.default')
@section('content')

<div class="container">
  <div class="card card-register mx-auto mt-5">
    <div class="card-header">Edit a level</div>
    <div class="card-body">
      {{ Form::open(array('method'=>'PUT', 'action' => array('LevelController@update', $level->id))) }}
        <div class="form-group">
          <div class="form-row">
            <div class="col-md-6">
              <div class="form-label-group">
                {{ Form::text('name', $level->name, array('class' => 'form-control')) }}
                <label>Level name</label>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="form-label-group">
            {{ Form::text('description', $level->description, array('class' => 'form-control')) }}
            <label>Decription</label>
          </div>
        </div>
        {{ Form::submit('Submit', array('class' => 'btn btn-primary btn-block')) }}
      {{ Form::close() }}
    </div>
  </div>
</div>

@stop
