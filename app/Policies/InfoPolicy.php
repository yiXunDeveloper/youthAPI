<?php

namespace App\Policies;

use App\Models\Recruit\BenPosition;
use App\Models\Recruit\Education;
use App\Models\Recruit\Honour;
use App\Models\Recruit\Information;
use App\Models\Recruit\LearnExp;
use App\Models\Recruit\Work;
use App\Models\Recruit\YanPosition;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;

class InfoPolicy extends Policy
{

    public function infoUpdate(User $user,Information $information)
    {
        return $information->user_id == $user->id;
    }

    public function infoDestroy(User $user, Information $information)
    {
        return $information->user_id == $user->id;
    }
    public function learnUpdate(User $user,LearnExp $learnExp)
    {
        return $learnExp->user_id == $user->id;
    }

    public function learnDestroy(User $user, LearnExp $learnExp)
    {
        return $learnExp->user_id == $user->id;
    }
    public function workUpdate(User $user,Work $work)
    {
        return $work->user_id == $user->id;
    }

    public function workDestroy(User $user, Work $work)
    {
        return $work->user_id == $user->id;
    }
    public function educationUpdate(User $user,Education $education)
    {
        return $education->user_id == $user->id;
    }

    public function educationDestroy(User $user, Education $education)
    {
        return $education->user_id == $user->id;
    }
    public function benPositionUpdate(User $user,BenPosition $benPosition)
    {
        return $benPosition->user_id == $user->id;
    }

    public function benPositionDestroy(User $user, BenPosition $benPosition)
    {
        return $benPosition->user_id == $user->id;
    }
    public function honourUpdate(User $user,Honour $honour)
    {
        return $honour->user_id == $user->id;
    }

    public function honourDestroy(User $user, Honour $honour)
    {
        return $honour->user_id == $user->id;
    }
    public function yanPositionUpdate(User $user,YanPosition $yanPosition)
    {
        return $yanPosition->user_id == $user->id;
    }

    public function yanPositionDestroy(User $user, YanPosition $yanPosition)
    {
        return $yanPosition->user_id == $user->id;
    }
}
