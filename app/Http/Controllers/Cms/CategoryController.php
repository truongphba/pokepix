<?php

namespace App\Http\Controllers\Cms;

use App\Models\Category;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.cms');
    }

    public function index(Request $request)
    {
        $categoryType = config('global.categories_type');
        $currentType = $request->type;
        if (!array_key_exists($currentType, $categoryType)) {
            return abort('404');
        }
        $keyword = $request->get('keyword');
        $list = Category::where('type', $currentType)->orderByRaw('ISNULL(position), position ASC')->orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
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
            'type' => 'required'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
        ]);
        $item = new Category();
        $item->name = $request->name;
        $item->position = $request->position;
        $item->type = $request->type;
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
            'type' => 'required'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
            'type.required' => 'Bắt buộc phải chọn danh mục.',
        ]);

        $item = Category::find($id);
        $item->name = $request->name;
        $item->position = $request->position;
        $item->type = $request->type;
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
