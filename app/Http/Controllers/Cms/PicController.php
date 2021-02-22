<?php

namespace App\Http\Controllers\Cms;

use App\Models\Pic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.cms');
    }

    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $list = Pic::orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('id', $keyword);
        })->paginate(10)->appends($request->only('keyword'));
        return view('cms.pic.index', ['list' => $list, 'keyword' => $keyword]);
    }

    public function create()
    {
        return view('cms.pic.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'file' => 'required|file|mimes:jpeg,png,jpg|max:1024',
            'position' => 'numeric|min:1|nullable'
        ], [
            'name.required' => 'name bắt buộc phải nhập.',
            'file.required' => 'file bắt buộc phải chọn.',
            'file.file' => 'file phải có định dạng jpeg, png, jpg',
            'file.mimes' => 'file phải có định dạng jpeg, png, jpg',
            'file.max' => 'file có kích thước tối đa là 1024kb',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.'
        ]);
        $item = new Pic();
        if ($request->file('file')) {
            $file = $request->file('file');
            $extends = $file->getClientOriginalExtension();

            //Move Uploaded File
            $destinationPath = 'pic/' . date('Y/m/d');
            $name = \Str::slug($request->name) . '.' . $extends;

            $path = public_path($destinationPath);
            \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

            $request->file('file')->move($path, $name);
            $item->file = $destinationPath . '/' . $name;
        }


        $item->name = $request->name;
        $item->position = $request->position;

        $item->save();

        return redirect('/cms/pics/' . $item->id)->withSuccess('Thêm mới tranh thành công.');
    }

    public function detail($id)
    {
        $item = Pic::find($id);
        return view('cms.pic.detail', ['item' => $item]);
    }

    public function edit($id)
    {
        $item = Pic::find($id);
        return view('cms.pic.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable'
        ], [
            'name.required' => 'name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.'
        ]);

        $item = Pic::find($id);

        if ($request->file('file')) {
            $file = $request->file('file');
            $extends = $file->getClientOriginalExtension();

            //Move Uploaded File
            $destinationPath = 'pic/' . date('Y/m/d');
            $name = \Str::slug($request->name) . '.' . $extends;

            $path = public_path($destinationPath);
            \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

            $request->file('file')->move($path, $name);
            $item->file = $destinationPath . '/' . $name;
        }


        $item->name = $request->name;
        $item->position = $request->position;

        $item->save();

        return redirect('/cms/pics/' . $item->id)->withSuccess('Cập nhật tranh thành công.');
    }

    public function delete($id)
    {
        $user = Pic::find($id);
        $user->delete();

        return redirect('/cms/pics/')->withSuccess('Xoá tranh thành công.');
    }

    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'numeric|min:1|nullable'
        ], [
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
        ]);

        $item = Pic::find($id);
        $msg = 'Cập nhật vị trí tranh thành công.';

        $item->position = $request->position;
        $item->save();
        return redirect('/cms/pics')->withSuccess($msg);
    }
}
