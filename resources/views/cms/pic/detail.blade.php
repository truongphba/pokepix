@extends('cms.layouts.layout')

@section('title','Quản Lý Hình Ảnh')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản Lý Hình Ảnh</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <h4 class="m-0 font-weight-bold text-primary">Chi Tiết Hình Ảnh</h4>
                </div>
            </div>
            <div class="card-body">
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
                            <label>Loại hình ảnh:</label>
                            <select disabled name="type" id="type" class="form-control">
                                @foreach($type as $key => $i)
                                    <option
                                        value="{{$key}}" {{$item->type == $key ? 'selected' : ''}}>{{$i}}</option>
                                @endforeach
                            </select>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Thể loại:</label>
                            <select disabled name="category_id" id="category_id" class="form-control">
                                <option value="">{{ $item->category }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Chủ đề:</label>
                            <select disabled name="theme_id" id="theme_id" class="form-control">
                                <option value="">{{ $item->theme }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                @if($item->type == 2)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ảnh pixel:</label>
                                @if(isset($item->file))
                                    <div><img style="max-width: 200px;" src="{{  $item->getFileUrl() }}"
                                              alt="{{  $item->getFileUrl() }}">
                                    </div>
                                @else
                                    <p style="font-style: italic">None</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Ảnh svg:</label>
                                @if(isset($item->svgImageURL))
                                    <div><img style="max-width: 200px;" src="{{  $item->getSvgImageUrl() }}"
                                              alt="{{  $item->getSvgImageUrl() }}">
                                    </div>
                                @else
                                    <p style="font-style: italic">None</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Ảnh outline:</label>
                                @if(isset($item->outlineImageURL))
                                    <div><img style="max-width: 200px;" src="{{  $item->getOutlineImageUrl() }}"
                                              alt="{{  $item->getOutlineImageUrl() }}">
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
                                <label for="file">Ảnh original:</label>
                                @if(isset($item->originalImageURL))
                                    <div><img style="max-width: 200px;" src="{{  $item->getOriginalImageUrl() }}"
                                              alt="{{  $item->getOriginalImageUrl() }}">
                                    </div>
                                @else
                                    <p style="font-style: italic">None</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Ảnh color:</label>
                                @if(isset($item->colorImageURL))
                                    <div><img style="max-width: 200px;" src="{{  $item->getColorImageUrl() }}"
                                              alt="{{  $item->getColorImageUrl() }}">
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
                    <a href="/cms/pics">
                        <button type="button" class="btn btn-primary text-uppercase mr-2">Quay Lại</button>
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
