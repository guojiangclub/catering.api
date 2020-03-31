<?php

namespace GuoJiangClub\Catering\Component\Favorite\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        $this->setTable($prefix . 'favorites');
    }

    public function favoriteable()
    {
        return $this->morphTo();
    }

}