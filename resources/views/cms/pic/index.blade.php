@extends('cms.layouts.layout')
@section('style')
    <style>
        .select-active {
            display: none;
        }
    </style>
@endsection
@section('title','Quản Lý Tranh')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản Lý Tranh</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <div class="col-md-4 col-3">
                        <h4 class="m-0 font-weight-bold text-primary">Danh sách Tranh</h4>
                    </div>
                    <div class="col-md-4 col-3">
                        <form
                            class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control border-0 small" name="keyword"
                                       value="{{ $keyword }}" placeholder="Tìm kiếm...." aria-label="Search"
                                       aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 col-6 text-right">
                        <a href="/cms/pics/create">
                            <button class="btn btn-success text-uppercase">Thêm mới</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success"> {{ session('success') }}</div>
                @endif
                @error('position')
                <div class="alert alert-danger"> {{ $message }}</div>
                @enderror
                @if(count($list) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th class="text-center">Id</th>
                                <th>Position</th>
                                <th>Name</th>
                                <th>Picture</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $item)
                                <tr>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td style="width: 20%">
                                        <form
                                            action="/cms/pics/{{  $item->id  }}/updatePosition"
                                            method="POST">
                                            @csrf
                                            <input class="form-control" name="position" type="number"
                                                   value="{{$item->position}}" {{old('position')}} maxlength="255">
                                        </form>
                                    </td>
                                    <td>{{$item->name}}</td>
                                    <td class="text-center">
                                        <img src="{{$item->getFileUrl()}}" style="width: 150px; height:100px;">
                                    </td>
                                    <td>{{date_format($item->created_at, 'Y-m-d H:i:s')}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
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
                    <h4>Không có tranh nào.</h4>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('#dataTable tbody tr').dblclick(function () {
                window.location.href = '/cms/pics/' + $(this).children().first().text();
            });
        });
    </script>
@endsection
