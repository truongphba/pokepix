<?php

namespace App\Http\Controllers\Cms;

use App\Models\Category;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.cms');
    }

    public function index(Request $request)
    {
        $categoryType = config('global.categories_type');


        $keyword = $request->get('keyword');
        $list = Category::query();
        $currentType = null;
        if ($request->type){
            $currentType = $request->type;
            $list = $list->where('type', $request->type);
        }
        $list = $list->orderByRaw('ISNULL(position), position ASC')->orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('id', $keyword);
        })->paginate(10)->appends($request->only('keyword'))->appends($request->only('type'));

        return view('cms.category.index', ['list' => $list, 'keyword' => $keyword, 'categoryType' => $categoryType, 'currentType' => $currentType]);
    }

    public function create()
    {
        $categoryType = config('global.categories_type');
        return view('cms.category.create', ['categoryType' => $categoryType]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable',
            'type' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024',
            'cover' => 'file|mimes:jpeg,png,jpg|max:1024'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
            'avatar.file' => 'avatar phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'avatar phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'avatar có kích thước tối đa là 1024kb',
            'cover.file' => 'cover phải có định dạng jpeg, png, jpg',
            'cover.mimes' => 'cover phải có định dạng jpeg, png, jpg',
            'cover.max' => 'cover có kích thước tối đa là 1024kb'
        ]);
        $item = new Category();
        $item->name = $request->name;
        $item->position = $request->position;
        $item->type = $request->type;
        if ($request->type == 2){
            if ($request->file('avatar')) {
                $fileAvatar = $request->file('avatar');
                $extendsAvatar = $fileAvatar->getClientOriginalExtension();

                //Move Uploaded File
                $destinationPathAvatar = 'themes/avatar/' . date('Y/m/d');
                $nameAvatar = \Str::slug($request->name) . '.' . $extendsAvatar;

                $pathAvatar = public_path($destinationPathAvatar);
                \File::isDirectory($pathAvatar) or \File::makeDirectory($pathAvatar, 0777, true, true);

                $request->file('avatar')->move($pathAvatar, $nameAvatar);
                $item->avatar = $destinationPathAvatar . '/' . $nameAvatar;

                if ($fileAvatar->getClientOriginalExtension() != 'gif') {
                    // copy($file->getRealPath(), $destination);
                    ImageManagerStatic::configure(array('driver' => 'imagick'));
                    // open an image file
                    $imgAvatar = ImageManagerStatic::make($pathAvatar . '/' . $nameAvatar);
                    // resize image instance
                    $imgAvatar->resize(600, 600);
                    $imgAvatar->save($pathAvatar . '/' . $nameAvatar);
                }
            }
            if ($request->file('cover')) {
                $file = $request->file('cover');
                $extends = $file->getClientOriginalExtension();

                //Move Uploaded File
                $destinationPath = 'themes/cover/' . date('Y/m/d');
                $name = \Str::slug($request->name) . '.' . $extends;

                $path = public_path($destinationPath);
                \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

                $request->file('cover')->move($path, $name);
                $item->cover = $destinationPath . '/' . $name;

//                if ($file->getClientOriginalExtension() != 'gif') {
//                    // copy($file->getRealPath(), $destination);
//                    ImageManagerStatic::configure(array('driver' => 'imagick'));
//                    // open an image file
//                    $img = ImageManagerStatic::make($path . '/' . $name);
//                    // resize image instance
//                    $img->resize(600, 600);
//                    $img->save($path . '/' . $name);
//                }
            }
        }
        $item->save();

        return redirect('/cms/categories/' . $item->id)->withSuccess('Thêm mới danh mục thành công.');
    }

    public function detail($id)
    {
        $categoryType = config('global.categories_type');
        $item = Category::find($id);

        return view('cms.category.detail', ['item' => $item, 'categoryType' => $categoryType]);
    }

    public function edit($id)
    {
        $categoryType = config('global.categories_type');
        $item = Category::find($id);
        return view('cms.category.edit', ['item' => $item, 'categoryType' => $categoryType]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable',
            'type' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024',
            'cover' => 'file|mimes:jpeg,png,jpg|max:1024'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
            'avatar.file' => 'avatar phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'avatar phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'avatar có kích thước tối đa là 1024kb',
            'cover.file' => 'cover phải có định dạng jpeg, png, jpg',
            'cover.mimes' => 'cover phải có định dạng jpeg, png, jpg',
            'cover.max' => 'cover có kích thước tối đa là 1024kb'
        ]);

        $item = Category::find($id);
        $item->name = $request->name;
        $item->position = $request->position;
        $item->type = $request->type;
        if ($request->type == 2){
            if ($request->file('avatar')) {
                $fileAvatar = $request->file('avatar');
                $extendsAvatar = $fileAvatar->getClientOriginalExtension();

                //Move Uploaded File
                $destinationPathAvatar = 'themes/avatar/' . date('Y/m/d');
                $nameAvatar = \Str::slug($request->name) . '.' . $extendsAvatar;

                $pathAvatar = public_path($destinationPathAvatar);
                \File::isDirectory($pathAvatar) or \File::makeDirectory($pathAvatar, 0777, true, true);

                $request->file('avatar')->move($pathAvatar, $nameAvatar);
                $item->avatar = $destinationPathAvatar . '/' . $nameAvatar;

                if ($fileAvatar->getClientOriginalExtension() != 'gif') {
                    // copy($file->getRealPath(), $destination);
                    ImageManagerStatic::configure(array('driver' => 'imagick'));
                    // open an image file
                    $imgAvatar = ImageManagerStatic::make($pathAvatar . '/' . $nameAvatar);
                    // resize image instance
                    $imgAvatar->resize(600, 600);
                    $imgAvatar->save($pathAvatar . '/' . $nameAvatar);
                }
            }
            if ($request->file('cover')) {
                $file = $request->file('cover');
                $extends = $file->getClientOriginalExtension();

                //Move Uploaded File
                $destinationPath = 'themes/cover/' . date('Y/m/d');
                $name = \Str::slug($request->name) . '.' . $extends;

                $path = public_path($destinationPath);
                \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

                $request->file('cover')->move($path, $name);
                $item->cover = $destinationPath . '/' . $name;

                if ($file->getClientOriginalExtension() != 'gif') {
                    // copy($file->getRealPath(), $destination);
                    ImageManagerStatic::configure(array('driver' => 'imagick'));
                    // open an image file
                    $img = ImageManagerStatic::make($path . '/' . $name);
                    // resize image instance
                    $img->resize(600, 600);
                    $img->save($path . '/' . $name);
                }
            }
        }
        $item->save();

        return redirect('/cms/categories/' . $item->id)->withSuccess('Cập nhật danh mục thành công.');
    }

    public function delete($id)
    {
        $item = Category::find($id);
        $item->delete();

        return redirect('/cms/categories?type=' . $item->type)->withSuccess('Xoá danh mục thành công.');
    }

    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'numeric|min:1|nullable'
        ], [
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
        ]);

        $item = Category::find($id);
        $item->position = $request->position;
        $item->save();
        return redirect('/cms/categories?type=' . $item->type)->withSuccess('Cập nhật vị trí danh mục thành công.');
    }
}
