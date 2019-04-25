<?php

namespace App\Http\Controllers\Api\Recruit;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\ImageRequest;
use App\Models\Recruit\Image;
use App\Transformers\Recruit\ImageTransformer;
use App\User;
use Dingo\Api\Auth\Auth;
use Illuminate\Http\Request;


class ImagesController extends Controller
{
    public function store(ImageRequest $request,ImageUploadHandler $uploader,Image $image)
    {
        $user =\Auth::guard('recruit')->user();

        $size = $request->type == 'avatar'?1024:768;
        $result = $uploader->save($request->image,str_plural($request->type),$user->id,$size);
        $image->path = $result['path'];
        $image->type = $request->type;
        $image->user_id = $user->id;
        $image->save();

        return $this->response->item($image, new ImageTransformer())->setStatusCode(201);
    }
}
