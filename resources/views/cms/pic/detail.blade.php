@extends('cms.layouts.layout')

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
                    <h4 class="m-0 font-weight-bold text-primary">Chi Tiết Tranh</h4>
                </div>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success"> {{ session('success') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Id:</label>
                            <input class="form-control" name="id" value="{{ $item->id }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name:</label>
                            <input class="form-control" name="name" value="{{ $item->name }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>File:</label>
                            @if(isset($item->file))
                                <div><img style="width: 400px" src="{{  $item->getFileUrl() }}" alt="{{  $item->getFileUrl() }}">
                                </div>
                            @else
                                <p style="font-style: italic">None</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Position:</label>
                            <input class="form-control" name="position" value="{{ $item->position }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Created At:</label>
                            <input class="form-control" name="like_count" value="{{ $item->created_at }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Updated At:</label>
                            <input class="form-control" name="like_count" value="{{ $item->updated_at }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="form-group" style="display: flex">
                    <a href="/cms/pics">
                        <button type="button" class="btn btn-primary text-uppercase mr-2">Quay Lại</button>
                    </a>
                    <a href="/cms/pics/{{ $item->id }}/edit">
                        <button type="button" class="btn btn-success text-uppercase mr-2">Sửa</button>
                    </a>
                    <button type="button" class="btn btn-danger text-uppercase mr-2" data-toggle="modal"
                            data-target="#delete-confirm">Xoá
                    </button>

                </div>
            </div>
        </div>
    </div>
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
                    <form method="post" id="form-delete" action="/cms/pics/{{ $item->id }}/delete">
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

        });
    </script>
@endsection
