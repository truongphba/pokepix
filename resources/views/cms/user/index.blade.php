@extends('cms.layouts.layout')
@section('style')
    <style>
        .tableFixHead {
            overflow-y: auto;
            max-height: 400px;
        }
        .tableFixHead thead th {
            position: sticky;
            top: 0;
            border-color: white!important;
            z-index: 10;
        }
    </style>
@endsection
@section('title','Quản Lý Người Dùng')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản Lý Người Dùng</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <div class="col-md-4 col-3">
                        <h4 class="m-0 font-weight-bold text-primary">Danh sách Người Dùng</h4>
                    </div>
                    <div class="col-md-4 col-3">
                        <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control border-0 small"  name="keyword" value="{{ $keyword }}" placeholder="Tìm kiếm...." aria-label="Search" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 col-6 text-right">
                        <a href="/cms/users/create">
                            <button class="btn btn-success text-uppercase">Thêm mới</button>
                        </a>
                        <button class="btn btn-danger" id="delete-selected">Xoá mục đã chọn</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(count($list) > 0)
                    <div class="table-responsive tableFixHead">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-dark">
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check" id="check-all">
                                </th>
                                <th class="text-center">Id</th>
                                <th>Tên</th>
                                <th>Id thiết bị</th>
                                <th>Số lượt thích</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $item)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="form-check product-checkbox"
                                                                   value="{{$item->id}}" name="selected[]"></td>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td>{{$item->name}}</td>
                                    <td>{{$item->device_id}}</td>
                                    <td>{{$item->likes_count}}</td>
                                    <td>{{date_format($item->created_at, 'Y-m-d H:i:s')}}</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-6 text-center">
                                                <a href="/cms/users/{{$item->id}}/edit" style="color: darkorange"><i class="fas fa-edit"></i></a>
                                            </div>
                                            <div class="col-6 text-center">
                                                <a class="delete" href="#" style="color: #b91d19" data="{{$item->id}}"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p>Hiển thị từ {{$list->firstItem()}} đến {{$list->lastItem()}} của {{$list->total()}} bản ghi</p>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                {{$list->links()}}
                            </div>
                        </div>
                    </div>
                @else
                    <h4>Không có user nào.</h4>
                @endif
            </div>
        </div>
    </div>
    @if (session()->has('success'))
        @include('cms.modal.success', ['message' => session('success')])
    @endif
    <div class="modal fade" id="delete-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> Bạn có chắc chắn muốn xoá?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <form method="post" id="form-delete" action="">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger">Có</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Không</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('#dataTable tbody tr').dblclick(function () {
                window.location.href = '/cms/users/' + $(this).children().first().next().text();
            });
            $('#check-all').click(function () {
                $('.product-checkbox').prop('checked', $(this).prop('checked'));
            });
            $('.product-checkbox').click(function(){
                $('#check-all').prop('checked',false);
            })
            $('#delete-selected').click(function () {
                var ids = $('.product-checkbox:checked').map(function(){
                    return $(this).val();
                }).get();
                if(ids.length == 0){
                    alert('Vui lòng chọn ít nhất 1 người dùng.');
                    return;
                }
                if(confirm('Bạn có chắc chắn muốn xoá không ?')){
                    $.ajax({
                        'url': '/cms/users/delete-selected',
                        'method': 'POST',
                        'data': {
                            "_token": $('meta[name="csrf-token"]').attr('content'),
                            'ids': ids,
                        },
                        'success': function () {
                            location.reload();
                        },
                        'error': function () {
                            alert('Đã có lỗi xảy ra');
                        }
                    })
                }
            })
            $('#success-modal').modal('show')
            setTimeout(function () {
                $('#success-modal').modal('hide')
            }, 5000)
            $('.delete').click( function () {
                const id = $(this).attr('data')
                $('#delete-confirm form').attr('action','/cms/users/' + id + '/delete')
                $('#delete-confirm').modal('show')
            })
        });
    </script>
@endsection
