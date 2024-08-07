<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'directory_id'];

    protected $visible = ['id', 'name', 'directory_id'];

    public function directory(): BelongsTo
    {
        return $this->belongsTo(Directory::class, 'directory_id');
    }
}
