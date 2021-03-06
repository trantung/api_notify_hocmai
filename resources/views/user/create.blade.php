@extends('common.default')
@section('content')
<div class="container">
  <div class="card card-register mx-auto mt-5">
    <div class="card-header">Create a User</div>
    <div class="card-body">
      {{ Form::open(array('method'=>'POST', 'action' => array('UserController@store'))) }}
      <div class="form-group">
        <div class="col-md-3">
          <div class="form-row">
            <div class="form-label-group">
            <label>User name</label>
              {{ Form::text('username', null, array('class' => 'form-control')) }}
            </div>
          </div>
          <div class="form-group">
            <div class="form-label-group">
            <label>Name</label>
              {{ Form::text('name', null, array('class' => 'form-control')) }}
            </div>
          </div>
          <div class="form-group">
            <div class="form-label-group">
            <label>email</label>
              {{ Form::text('email', null, array('class' => 'form-control')) }}
              
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="form-group">
          <label>Role</label>
          <div class="form-label-group">
            {{ Form::select('role_id', getListRole(), array('class' => 'form-control')) }}
          </div>
        </div>

        <div class="form-group">
          <div class="form-label-group">
            {{ Form::password('password', null, array('class' => 'form-control')) }}
            <label>Change password</label>
          </div>
        </div>

        {{ Form::submit('Submit', array('class' => 'btn btn-primary btn-block')) }}
        {{ Form::close() }}
      </div>
    </div>
  </div>
  @stop