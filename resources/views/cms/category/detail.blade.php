@extends('cms.layouts.layout')

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
                    <h4 class="m-0 font-weight-bold text-primary">Chi tiết Danh Mục</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Danh mục:</label>
                            <select disabled name="type" id="type" class="form-control">
                                @foreach($categoryType as $key => $type)
                                    <option
                                        value="{{$key}}" {{$item->type == $key ? 'selected' : ''}}>{{$type}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
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
                            <label>Tên:</label>
                            <input class="form-control" name="name" value="{{ $item->name }}" disabled>
                        </div>
                    </div>
                </div>
                @if($item->type == 2)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ảnh đại diện:</label>
                                @if(isset($item->avatar))
                                    <div><img style="max-width: 200px;" src="{{  $item->getAvatarUrl() }}"
                                              alt="{{  $item->getAvatarUrl() }}">
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
                                <label>Ảnh bìa:</label>
                                @if(isset($item->cover))
                                    <div><img style="max-width: 200px;" src="{{  $item->getCoverUrl() }}"
                                              alt="{{  $item->getCoverUrl() }}">
                                    </div>
                                @else
                                    <p style="font-style: italic">None</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Vị trí:</label>
                            <input class="form-control" name="position" value="{{ $item->position }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ngày tạo:</label>
                            <input class="form-control" name="like_count" value="{{ $item->created_at }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ngày cập nhật:</label>
                            <input class="form-control" name="like_count" value="{{ $item->updated_at }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="form-group" style="display: flex">
                    <a href="/cms/categories">
                        <button type="button" class="btn btn-primary text-uppercase mr-2">Quay lại</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @if (session()->has('success'))
        @include('cms.modal.success', ['message' => session('success')])
    @endif
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('#success-modal').modal('show')
            setTimeout(function () {
                $('#success-modal').modal('hide')
            }, 5000)
        });
    </script>
@endsection
