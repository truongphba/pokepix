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
                    <h4 class="m-0 font-weight-bold text-primary">Sửa Hình Ảnh</h4>
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
                                <label>Tên (*):</label>
                                <input class="form-control" name="name" value="{{ old('name',  $item->name) }}"
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
                                <label>Thể loại:</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="{{ old('category_id')}}">-- Chọn category --</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{$category->id}}" {{ old('category_id',$item->category_id) == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chủ đề:</label>
                                <select name="theme_id" id="theme_id" class="form-control">
                                    <option value="{{ old('theme_id')}}">-- Chọn theme --</option>
                                    @foreach($themes as $theme)
                                        <option
                                            value="{{$theme->id}}" {{ old('theme_id', $item->theme_id) == $theme->id ? 'selected' : '' }}>{{$theme->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vị trí:</label>
                                <input class="form-control" name="position"
                                       value="{{old('position', $item->position) }}"
                                       maxlength="255">
                                @error('position')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file">Hình ảnh (*):</label>
                                <input type="file" style="display:none" name="file" class="form-control-file" id="file" accept="image/png,image/jpg,image/jpeg">
                                <div>
                                    <button type="button" class="btn btn-primary choose-file">Chọn tệp</button>
                                </div>
                                @error('file')
                                <p class="err" style=" color: red">{{ $message }}</p>
                                @enderror
                                <img style="max-width: 200px; margin: 20px 0;" id="blah"
                                     src="{{ $item->getFileUrl() }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="/cms/pics">
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

        $(".choose-file").click(function () {
            $(this).parent().parent().find('.err').remove();
            $(this).parent().prev().trigger('click');
        });
    </script>
@endsection
