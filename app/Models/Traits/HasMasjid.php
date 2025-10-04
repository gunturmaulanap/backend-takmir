<?php
// app/Models/Traits/HasMasjid.php
namespace App\Models\Traits;

use App\Models\Scopes\HasMasjidScope;

trait HasMasjid
{
    protected static function bootHasMasjid()
    {
        static::addGlobalScope(new HasMasjidScope());
    }
}
