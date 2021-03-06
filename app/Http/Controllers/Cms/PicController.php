<?php
namespace App\Http\Controllers\Cms;
set_time_limit(0);
use App\Models\Category;
use App\Models\Pic;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic;
use Monolog\Logger;

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
        $currentPicType = $request->pic_type;
        $picType = config('global.pic_type');
        unset($picType[1]);
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
        if ($currentPicType) {
            $list = $list->where('type',$currentPicType);
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
                'currentPicType' => $currentPicType,
                'picType' => $picType
            ]);
    }

    public function create(Request $request)
    {
        $type = config('global.pic_type');
        unset($type[1]);
        $currentType = null;
        if ($request->type) {
            $currentType = $request->type;
        }
        $categories = Category::orderByRaw('ISNULL(position), position ASC')
            ->whereIn('pic_type', [$currentType, 1])
            ->where('type', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        $themes = Category::orderByRaw('ISNULL(position), position ASC')
            ->whereIn('pic_type', [$currentType, 1])
            ->where('type', 2)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('cms.pic.create', [
            'categories' => $categories,
            'themes' => $themes,
            'type' => $type,
            'currentType' => $currentType]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'file' => 'file|mimes:jpeg,png,jpg|max:1024',
            'svgImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'outlineImageUrl' => 'file|mimes:jpeg,png,jpg,bmp|max:1024',
            'originalImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'colorImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'position' => 'numeric|min:1|nullable'
        ], [
            'name.required' => 'Tên bắt buộc phải nhập.',
            'type.required' => 'Tên bắt buộc phải chọn.',
            'file.file' => 'Ảnh pixel phải có định dạng jpeg, png, jpg',
            'svgImageUrl.file' => 'Ảnh svg phải có định dạng jpeg, png, jpg',
            'outlineImageUrl.file' => 'Ảnh outline phải có định dạng jpeg, png, jpg',
            'originalImageUrl.file' => 'Ảnh original phải có định dạng jpeg, png, jpg',
            'colorImageUrl.file' => 'Ảnh color phải có định dạng jpeg, png, jpg',
            'file.mimes' => 'Ảnh pixel phải có định dạng jpeg, png, jpg',
            'svgImageUrl.mimes' => 'Ảnh svg phải có định dạng jpeg, png, jpg',
            'outlineImageUrl.mimes' => 'Ảnh outline phải có định dạng jpeg, png, jpg, bmp',
            'originalImageUrl.mimes' => 'Ảnh original phải có định dạng jpeg, png, jpg',
            'colorImageUrl.mimes' => 'Ảnh color phải có định dạng jpeg, png, jpg',
            'file.max' => 'Ảnh pixel có kích thước tối đa là 1024kb',
            'svgImageUrl.max' => 'Ảnh svg có kích thước tối đa là 1024kb',
            'outlineImageUrl.max' => 'Ảnh outline có kích thước tối đa là 1024kb',
            'originalImageUrl.max' => 'Ảnh original có kích thước tối đa là 1024kb',
            'colorImageUrl.max' => 'Ảnh color có kích thước tối đa là 1024kb',
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.'
        ]);

        DB::beginTransaction();
        try {
            $item = new Pic();
            foreach ($request->file() as $key => $file) {
                $extends = $file->getClientOriginalExtension();

                //Move Uploaded File
                if ($key == 'file') {
                    $folder = 'pic/';
                } else if ($key == 'svgImageUrl') {
                    $folder = 'svg/';
                } else if ($key == 'outlineImageUrl') {
                    $folder = 'outline/';
                } else if ($key == 'originalImageUrl') {
                    $folder = 'original/';
                } else {
                    $folder = 'color/';
                }
                $destinationPath = $folder . date('Y/m/d');
                $name = \Str::slug($request->name) . '.' . $extends;

                $path = public_path($destinationPath);
                \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

                $file->move($path, $name);
                $item->$key = $destinationPath . '/' . $name;

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
            $item->type = $request->type;
            $position = $request->position == '' ? null : $request->position;
            $item->position = $position;
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
        $type = config('global.pic_type');
        unset($type[1]);
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
        return view('cms.pic.detail', ['item' => $item, 'type' => $type]);
    }

    public function edit($id)
    {
        $type = config('global.pic_type');
        unset($type[1]);
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
            ->whereIn('pic_type', [$item->type, 1])
            ->where('type', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        $themes = Category::orderByRaw('ISNULL(position), position ASC')
            ->whereIn('pic_type', [$item->type, 1])
            ->where('type', 2)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('cms.pic.edit', ['item' => $item, 'categories' => $categories, 'themes' => $themes, 'type' => $type]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'file' => 'file|mimes:jpeg,png,jpg|max:1024',
            'svgImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'outlineImageUrl' => 'file|mimes:jpeg,png,jpg, bmp|max:1024',
            'originalImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'colorImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
            'position' => 'numeric|min:1|nullable'
        ], [
            'name.required' => 'Tên bắt buộc phải nhập.',
            'file.file' => 'Ảnh pixel phải có định dạng jpeg, png, jpg',
            'svgImageUrl.file' => 'Ảnh svg phải có định dạng jpeg, png, jpg',
            'outlineImageUrl.file' => 'Ảnh outline phải có định dạng jpeg, png, jpg',
            'originalImageUrl.file' => 'Ảnh original phải có định dạng jpeg, png, jpg',
            'colorImageUrl.file' => 'Ảnh color phải có định dạng jpeg, png, jpg',
            'file.mimes' => 'Ảnh pixel phải có định dạng jpeg, png, jpg',
            'svgImageUrl.mimes' => 'Ảnh svg phải có định dạng jpeg, png, jpg',
            'outlineImageUrl.mimes' => 'Ảnh outline phải có định dạng jpeg, png, jpg, bmp',
            'originalImageUrl.mimes' => 'Ảnh original phải có định dạng jpeg, png, jpg',
            'colorImageUrl.mimes' => 'Ảnh color phải có định dạng jpeg, png, jpg',
            'file.max' => 'Ảnh pixel có kích thước tối đa là 1024kb',
            'svgImageUrl.max' => 'Ảnh svg có kích thước tối đa là 1024kb',
            'outlineImageUrl.max' => 'Ảnh outline có kích thước tối đa là 1024kb',
            'originalImageUrl.max' => 'Ảnh original có kích thước tối đa là 1024kb',
            'colorImageUrl.max' => 'Ảnh color có kích thước tối đa là 1024kb',
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.'
        ]);

        DB::beginTransaction();
        try {
            $item = Pic::find($id);

            foreach ($request->file() as $key => $file) {
                $extends = $file->getClientOriginalExtension();

                //Move Uploaded File
                if ($key == 'file') {
                    $folder = 'pic/';
                } else if ($key == 'svgImageUrl') {
                    $folder = 'svg/';
                } else if ($key == 'outlineImageUrl') {
                    $folder = 'outline/';
                } else if ($key == 'originalImageUrl') {
                    $folder = 'original/';
                } else {
                    $folder = 'color/';
                }
                $destinationPath = $folder . date('Y/m/d');
                $name = \Str::slug($request->name) . '.' . $extends;

                $path = public_path($destinationPath);
                \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

                $file->move($path, $name);
                $item->$key = $destinationPath . '/' . $name;

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
            $position = $request->position == '' ? null : $request->position;
            $item->position = $position;
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
            DB::table('category_pic')->where('pic_id', $item->id)->delete();

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
            'position.numeric' => 'Vị trí phải là 1 số.',
            'position.min' => 'Vị trí phải lớn hơn 0.',
        ]);

        $item = Pic::find($id);
        $msg = 'Cập nhật vị trí hình ảnh thành công.';

        $position = $request->position == '' ? null : $request->position;
        $item->position = $position;
        $item->save();
        return redirect('/cms/pics')->withSuccess($msg);
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->get('ids');
        DB::beginTransaction();
        try {
            $item = Pic::whereIn('id', $ids);
            $item->delete();
            DB::table('category_pic')->whereIn('pic_id', $ids)->delete();
            DB::commit();
            return $ids;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function processImage($id)
    {
        $pic = Pic::find($id);
        if (!$pic->outlineImageURL || !$pic->colorImageURL) {
            return false;
        }
        $outlineBitmap = imageCreateFromPng(public_path($pic->outlineImageURL));
        $colorBitmap = imageCreateFromPng(public_path($pic->colorImageURL));
        $width = imagesx($outlineBitmap);
        $height = imagesy($outlineBitmap);

        $isChecked = [];
        for ($x = 0; $x < $height; $x++) {
            for ($y = 0; $y < $width; $y++) {
                $isChecked[$x][$y] = false;
            }
        }
        $blockList = [];

        for ($x = 0; $x < $height; $x++) {
            for ($y = 0; $y < $width; $y++) {
                if (!$isChecked[$x][$y] && $this->shouldBeFill($outlineBitmap, $x, $y)) {
                    $isChecked[$x][$y] = true;
                    $xArray = [-1, -1, -1, 0, 0, 1, 1, 1];
                    $yArray = [-1, 0, 1, -1, 1, -1, 0, 1];

                    $pointCount = 0;
                    $maxSize = 0;
                    $centerX = $x;
                    $centerY = $y;
                    $pointQueue = [];
                    $point = [$x, $y];
                    while ($point) {
                        $pointCount++;
                        $size = $this->getDistanceToBoundary($outlineBitmap, $point[0], $point[1], $height, $width);
                        if ($maxSize < $size) {
                            $maxSize = $size;
                            $centerX = $point[0];
                            $centerY = $point[1];
                        }
                        for ($i = 0; $i < 8; $i++) {
                            $newX = $point[0] + $xArray[$i];
                            $newY = $point[1] + $yArray[$i];
                            if (!$isChecked[$newX][$newY]
                                && $newX >= 0
                                && $newX < $height
                                && $newY >= 0
                                && $newY < $width) {
                                $isChecked[$newX][$newY] = true;
                                if ($this->shouldBeFill($outlineBitmap, $newX, $newY)) {
                                    array_unshift($pointQueue, [$newX, $newY]);
                                }
                            }
                        }
                        if (count($pointQueue) > 0) {
                            $point = $pointQueue[0];
                            array_shift($pointQueue);
                        } else {
                            $point = null;
                        }
                    }
                    $block = [
                        'centerPoint' => [
                            'size' => $maxSize,
                            'x' => $centerX,
                            'y' => $centerY
                        ],
                        'color' => 0,
                        'index' => 0,
                        'pointCount' => $pointCount
                    ];

                    if ($block['centerPoint']['size'] > 1) {
                        $blockList[] = $block;
                        dd(json_encode($block));
                    }
                }
            }
        }

        dd($blockList);
    }

    private function shouldBeFill($resource, $x, $y)
    {
        $rgb = imagecolorat($resource, $x, $y);
        $red = imagecolorsforindex($resource, $rgb)['red'];
        $green = imagecolorsforindex($resource, $rgb)['green'];
        $blue = imagecolorsforindex($resource, $rgb)['blue'];

        $colorFromFRG = 0.2989 * $red + 0.5870 * $green + 0.1140 * $blue;

        if ($red == $green && $green == $blue && $colorFromFRG >= 150) {
            return true;
        }
        return false;
    }

    private function getDistanceToBoundary($resource, $x, $y, $height, $width)
    {
        $size = 1;
        while (true) {
            for ($i = $x - $size; $i < $x + $size; $i++) {
                for ($j = $y - $size; $j < $y + $size; $j++) {
                    if ($i < 0 || $i >= $height || $j < 0 || $j >= $width || !$this->shouldBeFill($resource, $x, $y)) {
                        return $size;
                    }
                }
            }
            $size++;
        }
    }
}
