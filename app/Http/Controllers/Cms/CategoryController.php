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

        $currentPicType = $request->pic_type;
        $picType = config('global.pic_type');
        $keyword = $request->get('keyword');
        $list = Category::query();
        $currentType = null;
        if ($request->type){
            $currentType = $request->type;
            $list = $list->where('type', $request->type);
        }
        if ($currentPicType) {
            $list = $list->where('pic_type',$currentPicType);
        }
        $list = $list->orderByRaw('ISNULL(position), position ASC')->orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('id', $keyword);
        })->paginate(10)->appends($request->only('keyword'))->appends($request->only('type'));

        return view('cms.category.index', ['list' => $list, 'keyword' => $keyword, 'categoryType' => $categoryType, 'currentType' => $currentType, 'picType' => $picType,'currentPicType' => $currentPicType]);
    }

    public function create()
    {
        $categoryType = config('global.categories_type');
        $picType = config('global.pic_type');
        return view('cms.category.create', ['categoryType' => $categoryType, 'picType' => $picType]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable',
            'type' => 'required',
            'picType' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024',
            'cover' => 'file|mimes:jpeg,png,jpg|max:1024'
        ], [
            'name.required' => 'Tên bắt buộc phải nhập.',
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
            'picType.required' => 'Bắt buộc phải chọn loại danh mục.',
            'avatar.file' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'Ảnh đại diện có kích thước tối đa là 1024kb',
            'cover.file' => 'Chủ đề phải có định dạng jpeg, png, jpg',
            'cover.mimes' => 'Chủ đề phải có định dạng jpeg, png, jpg',
            'cover.max' => 'Chủ đề có kích thước tối đa là 1024kb'
        ]);
        $item = new Category();
        $item->name = $request->name;
        $position = $request->position == '' ? null : $request->position;
        $item->position = $position;
        $item->type = $request->type;
        $item->pic_type = $request->picType;
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

        return redirect('/cms/categories/' . $item->id)->withSuccess('Thêm mới danh mục thành công.');
    }

    public function detail($id)
    {
        $categoryType = config('global.categories_type');
        $picType = config('global.pic_type');
        $item = Category::find($id);

        return view('cms.category.detail', ['item' => $item, 'categoryType' => $categoryType, 'picType' => $picType]);
    }

    public function edit($id)
    {
        $categoryType = config('global.categories_type');
        $picType = config('global.pic_type');
        $item = Category::find($id);
        return view('cms.category.edit', ['item' => $item, 'categoryType' => $categoryType, 'picType' => $picType]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable',
            'type' => 'required',
            'picType' => 'required',
            'avatar' => 'file|mimes:jpeg,png,jpg|max:1024',
            'cover' => 'file|mimes:jpeg,png,jpg|max:1024'
        ], [
            'name.required' => 'Tên bắt buộc phải nhập.',
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
            'picType.required' => 'Bắt buộc phải chọn loại danh mục.',
            'avatar.file' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg',
            'avatar.max' => 'Ảnh đại diện có kích thước tối đa là 1024kb',
            'cover.file' => 'Chủ đề phải có định dạng jpeg, png, jpg',
            'cover.mimes' => 'Chủ đề phải có định dạng jpeg, png, jpg',
            'cover.max' => 'Chủ đề có kích thước tối đa là 1024kb'
        ]);

        $item = Category::find($id);
        $item->name = $request->name;
        $position = $request->position == '' ? null : $request->position;
        $item->position = $position;
        $item->type = $request->type;
        $item->pic_type = $request->picType;
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

//                if ($fileAvatar->getClientOriginalExtension() != 'gif') {
//                    // copy($file->getRealPath(), $destination);
//                    ImageManagerStatic::configure(array('driver' => 'imagick'));
//                    // open an image file
//                    $imgAvatar = ImageManagerStatic::make($pathAvatar . '/' . $nameAvatar);
//                    // resize image instance
//                    $imgAvatar->resize(600, 600);
//                    $imgAvatar->save($pathAvatar . '/' . $nameAvatar);
//                }
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

        return redirect('/cms/categories/' . $item->id)->withSuccess('Cập nhật danh mục thành công.');
    }

    public function delete($id)
    {
        $item = Category::find($id);
        $item->delete();

        return redirect()->back()->withSuccess('Xoá danh mục thành công.');
    }

    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'numeric|min:1|nullable'
        ], [
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.',
        ]);

        $item = Category::find($id);
        $position = $request->position == '' ? null : $request->position;
        $item->position = $position;
        $item->save();
        return redirect()->back()->withSuccess('Cập nhật vị trí danh mục thành công.');
    }
}
