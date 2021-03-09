<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Like;
use App\Models\Image as ImageModels;
use App\Models\Follow;
use App\Http\Requests\API\User\CreateUserRequest;
use App\Http\Requests\API\User\UpdateUserRequest;
use App\Http\Requests\API\User\UploadAvatarRequest;
use App\Http\Requests\API\User\FollowUserRequest;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    public function create(CreateUserRequest $request)
    {
        $device = $request->header('Device');
        $token = $request->token;
        $exits  = User::where('token', $token)->first();
        if (!$exits){
            $exits  = User::whereDeviceId($device)->first();
        }

        if( $exits )
        {
            return response()->json(['data' => $exits, 'alert' => 'User exits!'], 200);
        }else {
            $user = User::create([
                'device_id' => $device,
                'name'   => 'Pixel Art '.\Str::random(3)
            ]);

            return response()->json(['data' => $user, 'alert' => 'User created!'], 200);
        }
        // php artisan make:request /API/User/CreateUserRequest
    }

    public function imagesLiked($id = null, Request $request)
    {
        // $user = User::with('likeds','followings')->findOrFail($request->user->id);
        if($id)
        {
            $posts = Like::with('image')->where('user_id',(int)$id)->orderBy('created_at','DESC')->paginate(100);
        }else {
            $posts = Like::with('image')->where('user_id',$request->user->id)->orderBy('created_at','DESC')->paginate(100);
        }

        return response()->json(['data' => $posts], 200);
    }

    public function info(Request $request)
    {
        $user = User::with('likeds','followings')->withCount('images','followers','followings','likes','likeds')->findOrFail($request->user->id);
        return response()->json(['data' => $user], 200);
    }

    public function find($id)
    {
        $user = User::with('likeds','followings')->withCount('images','followers','followings','likes','likeds')->find($id);
        return response()->json(['data' => $user], 200);
    }

    public function update(Request $request)
    {
        $request->user->update(['name' => trim(strip_tags($request->name))]);
        return response()->json(['data' => $request->user, 'alert' => 'User updated!'], 200);
    }

    public function follow(FollowUserRequest $request)
    {
        $userFollow = User::findOrFail((int)$request->user_id);

        $follow     = Follow::firstOrCreate([
            'follower' => $request->user->id,
            'user_id'  => $request->user_id
        ]);

        return response()->json(['data' => $follow, 'alert' => 'Followed!'], 200);
    }

    public function images($id)
    {
        $user = User::findOrFail((int)$id);

        $posts      = ImageModels::withCount('likes','comments')->whereUserId($user->id)->orderBy('created_at','DESC')->paginate(100);

        return response()->json(['data' => $posts], 200);
    }

    public function followers(Request $request)
    {
        $follows      = Follow::with('userFollow')->whereUserId($request->user->id)->orderBy('created_at','DESC')->paginate(100);

        return response()->json(['data' => $follows], 200);
    }

    public function followings(Request $request)
    {
        $follows        = Follow::whereFollower($request->user->id)->get();
        $follows        = $follows->pluck('user_id');

        $posts = ImageModels::withCount('likes','comments')->with('user')->whereIn('user_id',$follows)->orderBy('created_at','DESC')->paginate(100);

        return response()->json(['data' => $posts], 200);
    }

    public function topUser()
    {
        $users = User::withCount('likes','images','followers','followings')->orderBy('likes_count','DESC')->first();

        return response()->json(['data' => $users], 200);
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $file = $request->file('file');
        $extends = $file->getClientOriginalExtension();

        //Move Uploaded File
        $destinationPath = 'avatars/'.date('Y/m/d');
        $name   = \Str::slug($request->user->name).'.'.$extends;

        $path = public_path($destinationPath);
        \File::isDirectory($path) or \File::makeDirectory($path, 0777, true, true);

        $request->file('file')->move($path, $name);
        // configure with favored image driver (gd by default)
        Image::configure(array('driver' => 'imagick'));
        // open an image file
        $img = Image::make($path.'/'.$name);
        // resize image instance
        $img->resize(400, 400);
        $img->save($path.'/'.$name);
        $request->user->update(['avatar' => $destinationPath.'/'.$name]);

        return response()->json(['avatar' => url($destinationPath.'/'.$name), 'alert' => 'Upload success!'], 200);
    }

}
