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
                    <h4 class="m-0 font-weight-bold text-primary">Sửa Tranh</h4>
                </div>
            </div>
            <div class="card-body">
                <form id="product_form" method="post" action="/cms/pics/{{  $item->id  }}/edit"
                      enctype="multipart/form-data">
                    @csrf
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
                                <input class="form-control" name="name" value="{{ $item->name }}"
                                       {{old('name')}} maxlength="255">
                                @error('name')
                                <p style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position:</label>
                                <input class="form-control" name="position" value="{{ $item->position }}"
                                       {{old('position')}} maxlength="255">
                                @error('position')
                                <p style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file">File</label>
                        <input type="file" name="file" class="form-control-file" id="file">
                        @error('file')
                        <p style=" color: red">{{ $message }}</p>
                        @enderror
                        <img style="width: 400px; margin: 20px 0;" id="blah" src="{{ $item->getFileUrl() }}"/>
                    </div>
                    <div class="form-group">
                        <a href="/cms/pics/{{$item->id}}">
                            <button type="button" class="btn btn-primary text-uppercase">Quay Lại</button>
                        </a>
                        <button class="btn btn-success text-uppercase">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#file').change(function () {
            readURL(this);
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#blah')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                $('#blah')
                    .attr('src', '');
            }
        }
    </script>
@endsection
