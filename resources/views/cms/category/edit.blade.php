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
                      action="/cms/categories/{{  $item->id  }}/edit">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Danh mục:</label>
                                <select  name="type" id="type" class="form-control">
                                    @foreach($categoryType as $key => $type)
                                        <option
                                            value="{{$key}}" {{$key == $item->type ? 'selected' : ''}}>{{$type}}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                <p style="color: red">{{ $message }}</p>
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
                                <input type="number" class="form-control" name="position" value="{{ $item->position }}"
                                       {{old('position')}} maxlength="255">
                                @error('position')
                                <p style="color: red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="/cms/categories/{{$item->id}}">
                            <button type="button" class="btn btn-primary text-uppercase">Quay lại</button>
                        </a>
                        <button class="btn btn-success text-uppercase">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
