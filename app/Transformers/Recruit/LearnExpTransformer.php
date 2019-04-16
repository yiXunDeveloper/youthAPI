<?php

namespace App\Transformers\Recruit;

use Dingo\Api\Http\Request;
use Dingo\Api\Transformer\Binding;
use Dingo\Api\Contract\Transformer\Adapter;

class LearnExpTransformer implements Adapter
{
    public function transform($response, $transformer, Binding $binding, Request $request)
    {
        return 1;
    }
}