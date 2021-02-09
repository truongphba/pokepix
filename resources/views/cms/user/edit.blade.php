@extends('cms.layouts.layout')

@section('title','User Management')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <h4 class="m-0 font-weight-bold text-primary">Edit User</h4>
                </div>
            </div>
            <div class="card-body">
                <form id="product_form" method="post" action="/cms/users/{{  $item->id  }}/edit"
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
                                <label>Device Id:</label>
                                <input class="form-control" name="device_id" value="{{ $item->device_id }}"
                                       {{old('device_id')}} maxlength="255">
                                @error('device_id')
                                <p style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="avatar">Avatar</label>
                        <input type="file" name="avatar" class="form-control-file" id="avatar">
                        @error('avatar')
                        <p style=" color: red">{{ $message }}</p>
                        @enderror
                        <img style="width: 400px; margin: 20px 0;" id="blah" src="{{ $item->avatar }}"/>
                    </div>
                    <div class="form-group">
                        <a href="/cms/users/{{$item->id}}">
                            <button type="button" class="btn btn-primary text-uppercase">Back</button>
                        </a>
                        <button class="btn btn-success text-uppercase">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#avatar').change(function () {
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
