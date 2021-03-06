@extends('cms.layouts.layout')

@section('title','Quản lý danh mục')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản lý Danh Mục</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <h4 class="m-0 font-weight-bold text-primary">Sửa Danh Mục</h4>
                </div>
            </div>
            <div class="card-body">
                <form id="product_form" method="post"
                      action="/cms/categories/{{  $item->id  }}/edit" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Danh mục (*):</label>
                                <select  name="type" id="type" class="form-control">
                                    @foreach($categoryType as $key => $type)
                                        <option
                                            value="{{$key}}" {{$key == old('type', $item->type) ? 'selected' : ''}}>{{$type}}</option>
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
                                <label>Loại danh mục (*):</label>
                                <select  name="picType" id="picType" class="form-control">
                                    @foreach($picType as $key => $type)
                                        <option
                                            value="{{$key}}" {{$key == old('type', $item->pic_type) ? 'selected' : ''}}>{{$type}}</option>
                                    @endforeach
                                </select>
                                @error('picType')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
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
                                <label>Tên (*):</label>
                                <input class="form-control" name="name" value="{{ old('name', $item->name) }}"
                                       {{old('name')}} maxlength="255">
                                @error('name')
                                <p class="err" style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @if($item->type == 2)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="avatar">Ảnh đại diện:</label>
                                    <input type="file" style="display:none" name="avatar" class="form-control-file" id="avatar" accept="image/png,image/jpg,image/jpeg">
                                    <div>
                                        <button type="button" class="btn btn-primary choose-file">Chọn tệp</button>
                                    </div>
                                    @error('avatar')
                                    <p class="err" style="color: red">{{ $message }}</p>
                                    @enderror
                                    <img style="max-width: 200px; margin: 20px 0;" id="avatar-img" src="{{$item->getAvatarUrl()}}"/>
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
                                    <img style="max-width: 200px; margin: 20px 0;" id="cover-img" src="{{$item->getCoverUrl()}}"/>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vị trí:</label>
                                <input class="form-control" name="position" value="{{ old('position', $item->position) }}"
                                       {{old('position')}} maxlength="255">
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
        $('#picType').change(function (){
            $(this).css('border', '1px solid #d1d3e2')
            $(this).parent().find('.err').remove()
        })
        $(".choose-file").click(function () {
            $(this).parent().prev().trigger('click');
        })
    </script>
@endsection
