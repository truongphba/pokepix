<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\API\User\CreateUserRequest;
use App\Http\Requests\API\User\UpdateUserRequest;
use App\Http\Requests\API\User\UploadAvatarRequest;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class CommentController extends Controller
{

    public function info(Request $request)
    {
        
    }

    
    public function create(CreateUserRequest $request)
    {
        
    }

    public function update(Request $request)
    {
        
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        
    }

}
