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
                    <h4 class="m-0 font-weight-bold text-primary">Thêm Mới Danh Mục</h4>
                </div>
            </div>
            <div class="card-body">
                <form id="product_form" method="post" action="/cms/categories" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Danh mục (*):</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categoryType as $key => $type)
                                        <option {{old('type') == $key ? 'selected' : ''}} value="{{$key}}">{{$type}}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên (*):</label>
                                <input class="form-control" name="name" value="{{old('name')}}" maxlength="255">
                                @error('name')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div id="theme-type" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="avatar">Ảnh đại diện:</label>
                                    <input type="file" style="display: none" name="avatar" class="form-control-file" id="avatar" accept="image/png,image/jpg,image/jpeg">
                                    <div>
                                        <button type="button" class="btn btn-primary choose-file">Chọn tệp</button>
                                    </div>
                                    @error('avatar')
                                    <p class="err" style="color: red">{{ $message }}</p>
                                    @enderror
                                    <img style="max-width: 200px; margin: 20px 0;" id="avatar-img" src=""/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cover">Ảnh bìa:</label>
                                    <input type="file" style="display: none" name="cover" class="form-control-file" id="cover" accept="image/png,image/jpg,image/jpeg">
                                    <div>
                                        <button type="button" class="btn btn-primary choose-file">Chọn tệp</button>
                                    </div>
                                    @error('cover')
                                    <p class="err" style="color: red">{{ $message }}</p>
                                    @enderror
                                    <img style="max-width: 200px; margin: 20px 0;" id="cover-img" src=""/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vị trí:</label>
                                <input class="form-control" name="position" value="{{old('position')}}" maxlength="255">
                                @error('position')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="/cms/categories">
                            <button type="button" class="btn btn-primary text-uppercase">Quay lại</button>
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
        if ($('#type').val() == 2){
            $('#theme-type').show();
        } else {
            $('#theme-type').hide();
        }

        $('#type').change(function(){
            let type = $(this).val();
            if (type == 2){
                $('#theme-type').show();
            } else {
                $('#theme-type').hide();
            }
        });

        $('#avatar').change(function () {
            readURL(this, '#avatar-img');
        });
        $('#cover').change(function () {
            readURL(this, '#cover-img');
        });
        function readURL(input, id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $(id)
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                $(id)
                    .attr('src', '');
            }
        }

        $('.err').each(function () {
            $(this).prev().not('div').css('border', 'solid red 1px')
        })
        $('form input').keydown(function(){
            let border = '1px solid #d1d3e2';
            if ($(this).prev().attr('type') == 'file') {
                border = 'none'
            }
            $(this).css('border', border)
            $(this).parent().find('.err').remove()
        })
        $('#type').change(function (){
            $(this).css('border', '1px solid #d1d3e2')
            $(this).parent().find('.err').remove()
        })
        $(".choose-file").click(function () {
            $(this).parent().parent().find('.err').remove();
            $(this).parent().prev().trigger('click');
        });
    </script>
@endsection
