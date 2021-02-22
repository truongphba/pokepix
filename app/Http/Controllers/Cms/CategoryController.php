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

    public function index(Request $request, $name)
    {
        $categories = config('global.categories');
        if (!in_array($name, $categories)) {
            return abort('404');
        }
        $keyword = $request->get('keyword');
        if ($name == 'category') {
            $list = Category::query();
        } else if ($name == 'theme') {
            $list = Theme::query();
        }
        $list = $list->orderByRaw('ISNULL(position), position ASC')->orderBy('created_at', 'DESC')->where(function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('id', $keyword);
        })->paginate(10)->appends($request->only('keyword'))->appends($request->only('category'));
        return view('cms.category.index', ['list' => $list, 'keyword' => $keyword, 'categories' => $categories, 'currentCategory' => $name]);
    }

    public function create()
    {
        $categories = config('global.categories');
        return view('cms.category.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable',
            'categories' => 'required'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
            'categories.required' => 'Bắt buộc phải chọn danh mục.',
        ]);
        $categories = $request->categories;
        if ($categories == 'category') {
            $item = new Category();
            $msg = 'Thêm mới category thành công.';
        } else if ($categories == 'theme') {
            $item = new Theme();
            $msg = 'Thêm mới theme thành công.';
        } else {
            return redirect('/cms/categories/create');
        }
        $item->name = $request->name;
        $item->position = $request->position;
        $item->save();

        return redirect('/cms/categories/' . $categories . '/' . $item->id)->withSuccess($msg);
    }

    public function detail($name, $id)
    {
        $categories = config('global.categories');
        if (!in_array($name, $categories)) {
            return abort('404');
        }
        if ($name == 'category') {
            $item = Category::find($id);
        } elseif ($name == 'theme') {
            $item = Theme::find($id);
        } else {
            return redirect()->back();
        }

        return view('cms.category.detail', ['item' => $item, 'categories' => $categories, 'currentCategory' => $name]);
    }

    public function edit($name, $id)
    {
        $categories = config('global.categories');
        if (!in_array($name, $categories)) {
            return abort('404');
        }
        if ($name == 'category') {
            $item = Category::find($id);
        } elseif ($name == 'theme') {
            $item = Theme::find($id);
        } else {
            return redirect()->back();
        }
        return view('cms.category.edit', ['item' => $item, 'categories' => $categories, 'currentCategory' => $name]);
    }

    public function update(Request $request, $name, $id)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'numeric|min:1|nullable'
        ], [
            'name.required' => 'Name bắt buộc phải nhập.',
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
        ]);
        $categories = $name;
        if ($categories == 'category') {
            $item = Category::find($id);
            $msg = 'Cập nhật category thành công.';
        } else if ($categories == 'theme') {
            $item = Theme::find($id);
            $msg = 'Cập nhật theme thành công.';
        } else {
            return redirect()->back();
        }
        $item->name = $request->name;
        $item->position = $request->position;
        $item->save();

        return redirect('/cms/categories/' . $categories . '/' . $item->id)->withSuccess($msg);
    }

    public function delete($name, $id)
    {
        if ($name == 'category') {
            $item = Category::find($id);
            $msg = 'Xoá category thành công.';
        } else if ($name == 'theme') {
            $item = Theme::find($id);
            $msg = 'Xoá theme thành công.';
        } else {
            return redirect()->back();
        }
        $item->delete();

        return redirect('/cms/categories/' . $name . '/list')->withSuccess($msg);
    }

    public function updatePosition(Request $request, $name, $id)
    {
        $request->validate([
            'position' => 'numeric|min:1|nullable'
        ], [
            'position.numeric' => 'Position phải là 1 số.',
            'position.min' => 'Position phải lớn hơn 0.',
        ]);
        if ($name == 'category') {
            $item = Category::find($id);
            $msg = 'Cập nhật vị trí category thành công.';
        } else if ($name == 'theme') {
            $item = Theme::find($id);
            $msg = 'Cập nhật vị trí theme thành công.';
        } else {
            return redirect()->back();
        }
        $item->position = $request->position;
        $item->save();
        return redirect('/cms/categories/' . $name . '/list')->withSuccess($msg);
    }
}
