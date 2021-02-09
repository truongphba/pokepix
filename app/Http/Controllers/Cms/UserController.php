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
        $this->middleware('auth');
    }

    public function index(){
        $list = User::orderBy('created_at', 'DESC')->paginate(20);
        return view('cms.user.index',['list' => $list]);
    }

    public function create(){
        return view('cms.user.create');
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'device_id' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024'
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
        }


        $user->name = $request->name;
        $user->device_id = $request->device_id;

        $user->save();

        return redirect('/cms/users/'. $user->id)->withSuccess('Adding new user success.');
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
        }

        $user->name = $request->name;
        $user->device_id = $request->device_id;
        $user->save();

        return redirect('/cms/users/'. $user->id)->withSuccess('Edit user success.');
    }

    public function delete($id){
        $user = User::find($id);
        $user->delete();

        return redirect('/cms/users/')->withSuccess('Delete user success.');
    }
}
