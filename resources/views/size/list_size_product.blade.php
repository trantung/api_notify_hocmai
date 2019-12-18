@extends('common.default')
@section('content')
<div class="container-fluid">
<!-- DataTables Example -->
<div class="card mb-3">
  <div class="card-header">
      <a class="btn btn-success" href="{{ route('size_product.create') }}"><i class="fas fa-plus"></i> Thêm Mới</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>product_id</th>
            <th>Giá</th>
            <td>weight_number</td>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
            @foreach($listSizeProduct as $sizeProduct)
            <tr>
                <td>{{ getNameProductById($sizeProduct->product_id) }}</td>
                <td>{{ $sizeProduct->price }}
                  <td>{{ $sizeProduct->weight_number }}</td>
                <td>
                    <a href="{{ action('SizeProductController@show',$sizeProduct->id) }}"><i class="fa fa-info" style="color:#8BC34A">Xem</i></a>
                    <a  href="{{ action('SizeProductController@edit',$sizeProduct->id) }}"><i class="fas fa-edit" style="color:#f783ac">Sửa</i></a>

                    <i class="glyphicon glyphicon-trash">
                        {{ Form::open(array('method'=>'DELETE', 'action' => array('SizeProductController@destroy', $sizeProduct->id), 'style' => 'display: inline-block;')) }}
                            <button class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">
                                Delete
                            </button>
                        {{ Form::close() }}
                    </i>
                </td>
            </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

@stop