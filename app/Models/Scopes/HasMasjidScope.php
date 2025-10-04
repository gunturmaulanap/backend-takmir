<?php
// app/Models/Scopes/HasMasjidScope.php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HasMasjidScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        // Beri pengecualian untuk superadmin
        if ($user->roles->contains('name', 'superadmin')) {
            return;
        }

        // Ambil profile masjid berdasarkan role user
        $profileMasjid = $user->getMasjidProfile();
        if ($profileMasjid) {
            $builder->where($model->getTable() . '.profile_masjid_id', $profileMasjid->id);
        }
    }
}
