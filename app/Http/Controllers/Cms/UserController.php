<?php

namespace App\Http\Controllers\Cms;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.cms');
    }

    public function index(Request $request){
        $keyword = $request->get('keyword');
        $list = User::orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('id',$keyword )
                ->orWhere('device_id', 'like', '%' . $keyword . '%');
        })->paginate(20)->appends($request->only('keyword'));
        return view('cms.user.index',['list' => $list, 'keyword' => $keyword]);
    }

    public function create(){
        return view('cms.user.create');
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'device_id' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024'
        ],[
            'name.required' => 'Tên bắt buộc phải nhập.',
            'device_id.required' => 'Id thiết bị bắt buộc phải nhập.',
            'avatar.file' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'Ảnh đại diện có kích thước tối đa là 1024kb',
        ]);
        $user = new User();
        if ($request->file('avatar')){
            $file = $request->file('avatar');
            $extends = $file->getClientOriginalExtension();

            //Move Uploaded File
            $destinationPath = 'avatars/'.date('Y/m/d');
            $name   = \Str::slug($request->name).'.'.$extends;

            $path = public_path($destinationPath);
            \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

            $request->file('avatar')->move($path, $name);
            $user->avatar = $destinationPath.'/'.$name;

            Image::configure(array('driver' => 'imagick'));
            // open an image file
            $img = Image::make($path.'/'.$name);
            // resize image instance
            $img->resize(400, 400);
            $img->save($path.'/'.$name);
        }


        $user->name = $request->name;
        $user->device_id = $request->device_id;

        $user->save();

        return redirect('/cms/users/'. $user->id)->withSuccess('Thêm mới người dùng thành công.');
    }

    public function detail($id){
        $item = User::find($id);
        return view('cms.user.detail',['item' => $item]);
    }

    public function edit($id){
        $item = User::find($id);
        return view('cms.user.edit',['item' => $item]);
    }

    public function update(Request $request,$id){
        $request->validate([
            'name' => 'required',
            'device_id' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024'
        ],[
            'name.required' => 'Tên bắt buộc phải nhập.',
            'device_id.required' => 'Id thiết bị bắt buộc phải nhập.',
            'avatar.file' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'Ảnh đại diện có kích thước tối đa là 1024kb',
        ]);

        $user = User::find($id);

        if ($request->file('avatar')){
            $file = $request->file('avatar');
            $extends = $file->getClientOriginalExtension();

            //Move Uploaded File
            $destinationPath = 'avatars/'.date('Y/m/d');
            $name   = \Str::slug($request->name).'.'.$extends;

            $path = public_path($destinationPath);
            \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

            $request->file('avatar')->move($path, $name);
            $user->avatar = $destinationPath.'/'.$name;

            Image::configure(array('driver' => 'imagick'));
            // open an image file
            $img = Image::make($path.'/'.$name);
            // resize image instance
            $img->resize(400, 400);
            $img->save($path.'/'.$name);
        }

        $user->name = $request->name;
        $user->device_id = $request->device_id;
        $user->save();

        return redirect('/cms/users/'. $user->id)->withSuccess('Cập nhật người dùng thành công.');
    }

    public function delete($id){
        $user = User::find($id);
        $user->delete();

        return redirect('/cms/users/')->withSuccess('Xoá người dùng thành công.');
    }

    public function deleteSelected(Request $request){
        $ids = $request->get('ids');
        $user = User::whereIn('id', $ids);
        $user->delete();
        return $ids;
    }
}
