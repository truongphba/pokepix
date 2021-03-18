@extends('cms.layouts.layout')

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
                    <h4 class="m-0 font-weight-bold text-primary">Sửa Người Dùng</h4>
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
                                <label>Tên (*):</label>
                                <input class="form-control" name="name" value="{{ old('name', $item->name )}}"
                                       {{old('name')}} maxlength="255">
                                @error('name')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Id thiết bị (*):</label>
                                <input class="form-control" name="device_id"
                                       value="{{ old('device_id', $item->device_id) }}"
                                       {{old('device_id')}} maxlength="255">
                                @error('device_id')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="avatar">Ảnh đại diện</label>
                                <input style="display:none"  type="file" name="avatar" class="form-control-file" id="avatar" accept="image/png,image/jpg,image/jpeg">
                                <div>
                                    <button type="button" class="btn btn-primary choose-file">Chọn tệp</button>
                                </div>
                                @error('avatar')
                                <p class="err" style=" color: red">{{ $message }}</p>
                                @enderror
                                <img style="max-width: 200px; margin: 20px 0;" id="blah" src="{{ $item->avatar }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="/cms/users">
                            <button type="button" class="btn btn-primary text-uppercase">Quay Lại</button>
                        </a>
                        <button class="btn btn-success text-uppercase">Lưu</button>
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

        $('.err').each(function () {
            $(this).prev().css('border', 'solid red 1px')
        })
        $('form input').keydown(function(){
            let border = '1px solid #d1d3e2';
            if ($(this).prev().attr('type') == 'file') {
                border = 'none'
            }
            $(this).css('border', border)
            $(this).parent().find('.err').remove()
        })
        $(".choose-file").click(function () {
            $(this).parent().prev().trigger('click');
        });
    </script>
@endsection
