<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory;
    use LogsActivity;
    use Searchable;

    protected $fillable = ['name', 'slug', 'backgroundColor', 'textColor'];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $eventName)
            ->useLogName('categories');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
