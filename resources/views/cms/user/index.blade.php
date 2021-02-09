@extends('cms.layouts.layout')
@section('style')
    <style>
        .select-active {
            display: none;
        }
    </style>
@endsection
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
                    <div class="col-md-6 col-6">
                        <h4 class="m-0 font-weight-bold text-primary">User List</h4>
                    </div>
                    {{--                    <div class="col-md-3">--}}
                    {{--                        <form method="get" action="">--}}
                    {{--                            <input class="form-control" name="keyword" placeholder="Search....">--}}
                    {{--                        </form>--}}
                    {{--                    </div>--}}
                    <div class="col-md-6 col-6 text-right">
                        <a href="/cms/users/create">
                            <button class="btn btn-success text-uppercase">Add</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success"> {{ session('success') }}</div>
                @endif
                @if(count($list) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th class="text-center">Id</th>
                                <th>Name</th>
                                <th>Avatar</th>
                                <th>Device Id</th>
                                <th>Like Count</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $item)
                                <tr>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td>{{$item->name}}</td>
                                    <td>{{ $item->avatar }}</td>
                                    <td>{{$item->device_id}}</td>
                                    <td>{{$item->likes_count}}</td>
                                    <td>{{date_format($item->created_at, 'Y-m-d')}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>Showing {{$list->firstItem()}} to {{$list->lastItem()}} of {{$list->total()}} entries</p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="float-right">
                                                {{$list->links()}}
                                            </div>
                                        </div>
                                    </div>
                @else
                    <h4>Have no user</h4>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('#dataTable tbody tr').dblclick(function () {
                window.location.href = '/cms/users/' + $(this).children().first().text();
            });
        });
    </script>
@endsection
