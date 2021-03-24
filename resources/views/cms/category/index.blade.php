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
@section('title','Quản lý danh mục')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản Lý Danh Mục</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <div class="col-md-3 col-3">
                        <h4 class="m-0 font-weight-bold text-primary">Danh Sách Danh mục</h4>
                    </div>
                    <div class="col-md-3 col-3">
                        <select name="type" id="type" class="form-control">
                            <option value="" {{$currentType == null ? 'selected' : ''}}>Tất cả</option>
                            @foreach($categoryType as $key => $type)
                                <option
                                    value="{{$key}}" {{$currentType == $key ? 'selected' : ''}}>{{$type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-3">
                        <form action="/cms/categories" method="GET"
                              class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control border-0 small" name="keyword" id="keyword"
                                       value="{{ $keyword }}" placeholder="Tìm kiếm...." aria-label="Search"
                                       aria-describedby="basic-addon2">
                                <input type="hidden" name="type" value="{{$currentType}}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3 col-6 text-right">
                        <a href="/cms/categories/create">
                            <button class="btn btn-success text-uppercase">Thêm mới</button>
                        </a>
                    </div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="row">
                    <div class="col-md-3">
                        <label>Lọc theo loại danh mục: </label>
                        <select name="pic_type" id="picTypeFilter" class="form-control">
                            <option value="">Tất cả</option>
                            @foreach($picType as $key => $type)
                                <option
                                    value="{{$key}}" {{ $currentPicType == $key ? 'selected' : ''}}>{{$type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2" style="position: relative;">
                        <button class="btn btn-primary" id="filter" style="position: absolute; bottom: 0;">Lọc</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(count($list) > 0)
                    <div class="table-responsive tableFixHead">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-dark">
                            <tr>
                                <th class="text-center">Id</th>
                                <th>Vị trí</th>
                                <th>Tên</th>
                                <th>Danh mục</th>
                                <th>Loại danh mục</th>
                                <th>Ảnh đại diện</th>
                                <th>Ảnh bìa</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $item)
                                <tr>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td style="width: 10%">
                                        <form
                                            action="/cms/categories/{{$item->id}}/updatePosition"
                                            method="POST">
                                            @csrf
                                            <input class="form-control" name="position"
                                                   value="{{$item->position}}" maxlength="255">
                                        </form>
                                    </td>
                                    <td>{{$item->name}}</td>
                                    <td>{{ config('global.categories_type')[$item->type]}}</td>
                                    <td>{{ config('global.pic_type')[$item->pic_type]}}</td>
                                    <td class="text-center">
                                        @if($item->getAvatarUrl())
                                            <img src="{{$item->getAvatarUrl()}}" style="width: auto; height:70px;">
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->getCoverUrl())
                                            <img src="{{$item->getCoverUrl()}}" style="width: auto; height:70px;">
                                        @endif
                                    </td>
                                    <td>{{date_format($item->created_at, 'Y-m-d H:i:s')}}</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-6 text-center">
                                                <a href="/cms/categories/{{$item->id}}/edit" style="color: darkorange"><i class="fas fa-edit"></i></a>
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
                            <p>Hiển thị từ {{$list->firstItem()}} đến {{$list->lastItem()}} của {{$list->total()}} bản
                                ghi</p>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                {{$list->links()}}
                            </div>
                        </div>
                    </div>
                @else
                    <h4>Không có bản ghi nào.</h4>
                @endif
            </div>
        </div>
    </div>
    @if (session()->has('success'))
        @include('cms.modal.success', ['message' => session('success')])
    @endif
    @error('position')
    @include('cms.modal.success', ['message' => $message ])
    @enderror
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
                window.location.href = '/cms/categories/' + $(this).children().first().text();
            });

        });
        $('#type').change(function () {
            const type = $(this).val();
            window.location.href = '/cms/categories?type=' + type;
        });

        $('#success-modal').modal('show')
        setTimeout(function () {
            $('#success-modal').modal('hide')
        }, 5000)

        $('.delete').click( function () {
            const id = $(this).attr('data')
            $('#delete-confirm form').attr('action','/cms/categories/' + id + '/delete')
            $('#delete-confirm').modal('show')
        })

        $('#filter').click(function () {
            const pic_type = $('#picTypeFilter').val();
            const keyword = $('#keyword').val();
            const type = $('#type').val();
            window.location.href = '/cms/categories?type=' + type + '&keyword=' + keyword + '&pic_type=' + pic_type;
        })
    </script>
@endsection
