<?php

namespace App\Http\Controllers\Cms;

use App\Models\Category;
use App\Models\Pic;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic;

class PicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.cms');
    }

    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $currentThemeId = $request->theme_id;
        $currentCategoryId = $request->category_id;
        $filter = [];
        if ($currentThemeId) {
            $filter[] = $currentThemeId;
        }
        if ($currentCategoryId) {
            $filter[] = $currentCategoryId;
        }
        $list = Pic::with('categories')->orderByRaw('ISNULL(position), position ASC')
            ->orderBy('created_at', 'DESC')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('id', $keyword);
            });
        if (count($filter) > 0) {
            $list = $list->whereHas('categories', function ($query) use ($filter) {
                $query->whereIn('category_id', $filter);
            });
        }
        $list = $list->paginate(10)->appends($request->only('keyword'))->appends($request->only('category_id'))->appends($request->only('theme_id'));
        foreach ($list as $item) {
            $item->category = '';
            $item->theme = '';

            foreach ($item->categories as $category) {
                if ($category->pivot->type == 1) {
                    $item->category = $category->name;
                }
                if ($category->pivot->type == 2) {
                    $item->theme = $category->name;
                }
            }
        }

        $categories = Category::where('type', 1)
            ->orderByRaw('ISNULL(position), position ASC')
            ->orderBy('created_at', 'DESC')->get();
        $themes = Category::where('type', 2)
            ->orderByRaw('ISNULL(position), position ASC')
            ->orderBy('created_at', 'DESC')->get();
        return view('cms.pic.index',
            [
                'list' => $list,
                'keyword' => $keyword,
                'themes' => $themes,
                'categories' => $categories,
                'currentThemeId' => $currentThemeId,
                'currentCategoryId' => $currentCategoryId,
            ]);
    }

    public function create()
    {
        $categories = Category::orderByRaw('ISNULL(position), position ASC')
            ->where('type', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        $themes = Category::orderByRaw('ISNULL(position), position ASC')
            ->where('type', 2)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('cms.pic.create', ['categories' => $categories, 'themes' => $themes]);
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

        DB::beginTransaction();
        try {
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

            $item->name = $request->name;
            $item->position = $request->position;
            $item->save();

            $data = [];
            if ($request->category_id) {
                $data[] = [
                    'category_id' => $request->category_id,
                    'pic_id' => $item->id,
                    'type' => 1
                ];
            }
            if ($request->theme_id) {
                $data[] = [
                    'category_id' => $request->theme_id,
                    'pic_id' => $item->id,
                    'type' => 2
                ];
            }
            if (count($data) > 0) {
                DB::table('category_pic')->insert($data);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/cms/pics/create');
        }

        return redirect('/cms/pics/' . $item->id)->withSuccess('Thêm mới hình ảnh thành công.');
    }

    public function detail($id)
    {
        $item = Pic::find($id);
        $item->category = '';
        $item->theme = '';
        foreach ($item->categories as $category) {
            if ($category->pivot->type == 1) {
                $item->category = $category->name;
            }
            if ($category->pivot->type == 2) {
                $item->theme = $category->name;
            }
        }
        return view('cms.pic.detail', ['item' => $item]);
    }

    public function edit($id)
    {
        $item = Pic::find($id);
        $item->category_id = '';
        $item->theme_id = '';
        foreach ($item->categories as $category) {
            if ($category->type == 1) {
                $item->category_id = $category->id;
                break;
            }
        }
        foreach ($item->categories as $category) {
            if ($category->type == 2) {
                $item->theme_id = $category->id;
                break;
            }
        }
        $categories = Category::orderByRaw('ISNULL(position), position ASC')
            ->where('type', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        $themes = Category::orderByRaw('ISNULL(position), position ASC')
            ->where('type', 2)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('cms.pic.edit', ['item' => $item, 'categories' => $categories, 'themes' => $themes]);
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

        DB::beginTransaction();
        try {
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

            $item->name = $request->name;
            $item->position = $request->position;
            $item->save();
            if ($request->category_id) {
                DB::table('category_pic')->updateOrInsert(['pic_id' => $item->id, 'type' => 1], ['category_id' => $request->category_id]);
            } else {
                $categoryPic = DB::table('category_pic')->where(['pic_id' => $item->id, 'type' => 1])->first();
                if ($categoryPic) {
                    DB::table('category_pic')->where(['pic_id' => $item->id, 'type' => 1])->delete();
                }
            }
            if ($request->theme_id) {
                DB::table('category_pic')->updateOrInsert(['pic_id' => $item->id, 'type' => 2], ['category_id' => $request->theme_id]);
            } else {
                $themePic = DB::table('category_pic')->where(['pic_id' => $item->id, 'type' => 2])->first();
                if ($themePic) {
                    DB::table('category_pic')->where(['pic_id' => $item->id, 'type' => 2])->delete();
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/cms/pics/' . $id . '/edit');
        }

        return redirect('/cms/pics/' . $item->id)->withSuccess('Cập nhật hình ảnh thành công.');
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $item = Pic::find($id);
            $item->delete();
            DB::table('category_pic')->where('pic_id' , $item->id)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/cms/pics/');
        }
        return redirect('/cms/pics/')->withSuccess('Xoá hình ảnh thành công.');
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
        $msg = 'Cập nhật vị trí hình ảnh thành công.';

        $item->position = $request->position;
        $item->save();
        return redirect('/cms/pics')->withSuccess($msg);
    }

    public function deleteSelected(Request $request){
        $ids = $request->get('ids');
        DB::beginTransaction();
        try {
            $item = Pic::whereIn('id', $ids);
            $item->delete();
            DB::table('category_pic')->whereIn('pic_id' , $ids)->delete();
            DB::commit();
            return $ids;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
