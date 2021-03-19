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
            'outlineImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
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
            'outlineImageUrl.mimes' => 'Ảnh outline phải có định dạng jpeg, png, jpg',
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
                if($key == 'file') {
                    $folder = 'pic/';
                } else if($key == 'svgImageUrl'){
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
            'outlineImageUrl' => 'file|mimes:jpeg,png,jpg|max:1024',
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
            'outlineImageUrl.mimes' => 'Ảnh outline phải có định dạng jpeg, png, jpg',
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
                if($key == 'file') {
                    $folder = 'pic/';
                } else if($key == 'svgImageUrl'){
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

    public function bitmap()
    {
        function ImageCreateBMP($filename)
        {
//Ouverture du fichier en mode binaire
            if (!$f1 = fopen($filename, "rb")) return FALSE;

//1 : Chargement des ent�tes FICHIER
            $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
            if ($FILE['file_type'] != 19778) return FALSE;

//2 : Chargement des ent�tes BMP
            $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
                '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
                '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
            $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
            if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
            $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
            $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
            $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
            $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
            $BMP['decal'] = 4 - (4 * $BMP['decal']);
            if ($BMP['decal'] == 4) $BMP['decal'] = 0;

//3 : Chargement des couleurs de la palette
            $PALETTE = array();
            if ($BMP['colors'] < 16777216) {
                $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
            }

//4 : Cr�ation de l'image
            $IMG = fread($f1, $BMP['size_bitmap']);
            $VIDE = chr(0);

            $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
            $P = 0;
            $Y = $BMP['height'] - 1;
            while ($Y >= 0) {
                $X = 0;
                while ($X < $BMP['width']) {
                    if ($BMP['bits_per_pixel'] == 24)
                        $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                    elseif ($BMP['bits_per_pixel'] == 16) {
                        $COLOR = unpack("n", substr($IMG, $P, 2));
                        $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    } elseif ($BMP['bits_per_pixel'] == 8) {
                        $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                        $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    } elseif ($BMP['bits_per_pixel'] == 4) {
                        $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                        if (($P * 2) % 2 == 0) $COLOR[1] = ($COLOR[1] >> 4); else $COLOR[1] = ($COLOR[1] & 0x0F);
                        $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    } elseif ($BMP['bits_per_pixel'] == 1) {
                        $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                        if (($P * 8) % 8 == 0) $COLOR[1] = $COLOR[1] >> 7;
                        elseif (($P * 8) % 8 == 1) $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                        elseif (($P * 8) % 8 == 2) $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                        elseif (($P * 8) % 8 == 3) $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                        elseif (($P * 8) % 8 == 4) $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                        elseif (($P * 8) % 8 == 5) $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                        elseif (($P * 8) % 8 == 6) $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                        elseif (($P * 8) % 8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
                        $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                    } else
                        return FALSE;
                    imagesetpixel($res, $X, $Y, $COLOR[1]);
                    $X++;
                    $P += $BMP['bytes_per_pixel'];
                }
                $Y--;
                $P += $BMP['decal'];
            }

//Fermeture du fichier
            fclose($f1);

            return $res;
        }

        $resource = ImageCreateBMP(public_path('test.bmp'));
        $width = imagesx($resource);
        $height = imagesy($resource);
        $point = [
            'x' => 500,
            'y' => 500
        ];

        $i = 1;
        $result = [];
        while (true) {
            $a = [
                'x' => $point['x'] + $i,
                'y' => $point['y'] + $i
            ];
            if (imagecolorat($resource, $a['x'], $a['y']) == 0
                || $a['x'] == 0
                || $a['y'] == 0
                || $a['x'] == $width
                || $a['y'] == $height) {
                $result = $a;
                break;
            }
            $b = [
                'x' => $point['x'] + $i,
                'y' => $point['y'] - $i
            ];
            if (imagecolorat($resource, $b['x'], $b['y']) == 0
                || $b['x'] == 0
                || $b['y'] == 0
                || $b['x'] == $width
                || $b['y'] == $height) {
                $result = $b;
                break;
            }
            $c = [
                'x' => $point['x'] - $i,
                'y' => $point['y'] + $i
            ];
            if (imagecolorat($resource, $c['x'], $c['y']) == 0
                || $c['x'] == 0
                || $c['y'] == 0
                || $c['x'] == $width
                || $c['y'] == $height) {
                $result = $c;
                break;
            }
            $d = [
                'x' => $point['x'] - $i,
                'y' => $point['y'] - $i

            ];
            if (imagecolorat($resource, $d['x'], $d['y']) == 0
                || $d['x'] == 0
                || $d['y'] == 0
                || $d['x'] == $width
                || $d['y'] == $height) {
                $result = $d;
                break;
            }
            for ($j = 1; $j < $i * 2; $j++) {
                if (imagecolorat($resource, $a['x'] - $j, $a['y']) == 0) {
                    $result = [
                        'x' => $a['x'] - $j,
                        'y' => $a['y']
                    ];
                    break;
                }
                if (imagecolorat($resource, $a['x'], $a['y'] - $j) == 0) {
                    $result = [
                        'x' => $a['x'],
                        'y' => $a['y'] - $j
                    ];
                    break;
                }
                if (imagecolorat($resource, $b['x'] - $j, $b['y']) == 0) {
                    $result = [
                        'x' => $b['x'] - $j,
                        'y' => $b['y']
                    ];
                    break;
                }
                if (imagecolorat($resource, $c['x'], $c['y'] - $j) == 0) {
                    $result = [
                        'x' => $b['x'] - $j,
                        'y' => $b['y']
                    ];
                    break;
                }
            }
            if ($result) break;
            $i++;
        }
        dd($i);
    }
}
