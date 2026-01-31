<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'title',
        'excerpt',
        'body',
        'scheduled_at',
        'expires_at',
        'image_path',
        'slug',
        'is_published',
        'additional_info',
        'category_id',
        'read_time',
        'change_user_id',
        'changelog',
        'created_at',
        'updated_at',
        //SEO
        'meta_title',
        'meta_description',
        'focus_keyword',
        'image_alt',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->post->user();
    }

    public function changeUser()
    {
        return $this->belongsTo(User::class);
    }
}
