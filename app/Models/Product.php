<?php

namespace App\Models;

use App\Scopes\IsActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

//    protected $table = 'jury';
    
    protected static function booted()
    {
        static::addGlobalScope(new IsActiveScope());
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
