<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Like;
use App\Models\Comment;
use App\Http\Requests\API\Post\UploadImageRequest;
use App\Http\Requests\API\Post\CreatePostRequest;
use App\Http\Requests\API\Post\CommentPostRequest;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic;

class ImageController extends Controller
{
    // public function update()
    // {
    //     $images = Image::get();

    //     foreach ($images as $img)
    //     {
    //         $img->likes_count = $img->likes->count();
    //         $img->comments_count = $img->comments->count();
    //         $img->save();
    //     }
    // }

    public function popular(Request $request)
    {
        $today = date('Y-m-d H:i:s');

        $end_date = $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : $today;
        $start_date = $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : date('Y-m-d', strtotime($today . ' - 30 days'));
        $posts = Image::with('user')->whereBetween('created_at', [$start_date, $end_date])->orderBy('likes_count', 'DESC')->paginate(100);
        return response()->json(['data' => $posts], 200);
    }

    public function news()
    {
        $posts = Image::with('user')->orderBy('created_at', 'DESC')->paginate(100);
        return response()->json(['data' => $posts], 200);
    }

    public function info($id)
    {
        $post = Image::selectRaw('images.*, IFNULL(lk.likes_count,0) likes_count, IFNULL(comments_count, 0 ) comments_count')
            ->leftjoin(DB::Raw('(select image_id,count(*) likes_count from `likes` group by image_id) lk'), 'lk.image_id', '=', 'images.id')
            ->leftjoin(DB::Raw('(select image_id,count(*) comments_count from `comments` group by image_id) cm'), 'cm.image_id', '=', 'images.id')
            ->findOrFail((int)$id);
//        $post = Image::withCount('likes', 'comments')->findOrFail((int)$id);
        return response()->json(['data' => $post], 200);
    }

    public function create(CreatePostRequest $request)
    {
        $post = Image::firstOrCreate([
            'file' => $request->file
        ], [
            'user_id' => $request->user->id,
            'name' => $request->name != "" ? strip_tags(trim($request->name)) : \Str::random(10),
            'note' => trim($request->note),
            'content' => trim($request->content),
        ]);

        return response()->json(['data' => $post, 'alert' => 'Post created!'], 200);
    }

    public function listUsersLiked($id)
    {
        $users = Like::with('user', 'image')->where('image_id', (int)$id)->orderBy('created_at', 'DESC')->paginate(100);
        return response()->json(['data' => $users], 200);
    }

    public function listComment($id)
    {
        $comments = Comment::with('user')->where('image_id', (int)$id)->orderBy('created_at', 'DESC')->paginate(100);
        return response()->json(['data' => $comments], 200);
    }

    public function comment($id, CommentPostRequest $request)
    {
        $post = Image::findOrFail((int)$id);

        \DB::beginTransaction();
        try {

            $comment = Comment::create([
                'image_id' => $id,
                'user' => $post->user_id,
                'user_id' => $request->user->id,
                'body' => trim($request->body)
            ]);

            $post->increment('comments_count', 1);

            \DB::commit();
            return response()->json(['data' => $comment, 'alert' => 'Comment created!'], 200);
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json(['data' => null, 'alert' => 'Comment not created!'], 200);
        }


    }

    public function likePost($id, Request $request)
    {
        $post = Image::findOrFail((int)$id);

        \DB::beginTransaction();
        try {

            $like = Like::firstOrCreate([
                'image_id' => (int)$id,
                'user_id' => $request->user->id,
            ], [
                'user' => $post->user_id,
            ]);

            $post->increment('likes_count', 1);

            \DB::commit();
            return response()->json(['data' => $post, 'alert' => 'Post liked!'], 200);
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json(['data' => null, 'alert' => 'Post not like!'], 200);
        }

    }

    public function upload(UploadImageRequest $request)
    {
        $file = $request->file('file');
        $name = \Str::slug($file->getClientOriginalName() . '-' . time());
        $extends = $file->getClientOriginalExtension();

        //Move Uploaded File
        $destinationPath = 'posts/' . date('Y/m/d');
        $name = $name . '.' . $extends;

        $path = public_path($destinationPath);
        \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

        $request->file('file')->move($path, $name);

        if ($file->getClientOriginalExtension() != 'gif') {
            // copy($file->getRealPath(), $destination);
            ImageManagerStatic::configure(array('driver' => 'imagick'));
            // open an image file
            $img = ImageManagerStatic::make($path . '/' . $name);
            // resize image instance
            $img->resize(600, 600);
            $img->save($path . '/' . $name);
        }
        // configure with favored image driver (gd by default)
        return response()->json(['file' => $destinationPath . '/' . $name, 'path' => url($destinationPath . '/' . $name)], 200);
    }

    public function listByCategory($id)
    {
        $list = Pic::orderByRaw('ISNULL(position), position ASC')
            ->orderBy('created_at', 'DESC')
            ->whereHas('categories', function ($query) use ($id) {
                $query->where('category_id', $id)
                    ->where('categories.type', 1);
            })
            ->get();

        foreach ($list as $key => $item) {
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
            unset($list[$key]->categories);
        }

        return response()->json(['data'  => $list], 200);

    }
    public function listByTheme($id)
    {
        $list = Pic::orderByRaw('ISNULL(position), position ASC')
            ->orderBy('created_at', 'DESC')
            ->whereHas('categories', function ($query) use ($id) {
                $query->where('category_id', $id)
                    ->where('categories.type', 2);
            })
            ->get();

        foreach ($list as $key => $item) {
            $item->category = '';
            $item->theme = '';

            foreach ($item->categories as $category) {
                if ($category->pivot->type == 1) {
                    $item->category = $category->name;
                }
                if ($category->pivot->type == 2) {
                    $item->theme = $category->name;
                    $item->avatar = $category->avatar;
                    $item->cover = $category->cover;
                }
            }
            unset($list[$key]->categories);
        }

        return response()->json(['data'  => $list], 200);

    }
}
