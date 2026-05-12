<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Owner extends Authenticatable
{
    protected $table = 'owners';
    protected $primaryKey = 'owner_id';

    protected $guarded = [];

    protected $hidden = ['password'];

    

    public function umkms()
    {
        return $this->hasMany(Umkm::class, 'owner_id', 'owner_id');
    }
}
