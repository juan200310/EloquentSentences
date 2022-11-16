<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "category_id",
        "title",
        "slug",
        "likes",
        "dislikes",
        "content",
    ];

    protected $appends = [
      "title_with_author"
    ];

    protected $casts = [
        "created_at" => "datetime:Y-m-d H:i",
    ];
    /*
     * Cargar una consulta desde el modelo
        protected $with = [
            "user:id,name,email",
        ];
    */

    protected static function booted()
    {
        static::addGlobalScope("currentMonth",function (Builder $builder){
            $builder->whereMonth("created_at",now()->month);
        });
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class)->withDefault();
    }

    public function category():BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function tags():BelongsToMany{
        return $this->belongsToMany(Tag::class);
    }

    public function sortedTags():BelongsToMany{
        return $this->belongsToMany(Tag::class)
            ->orderBy("tag");
    }

    public function setTitleAttribute(string $title)
    {
        $this->attributes["title"] = $title;
        $this->attributes["slug"] = Str::slug($title);
    }

    public function  getTitleWithAuthorAttribute():string{
        return sprintf("%s - %s", $this->title, $this->user->name);
    }

    public  function scopeWhereHasTagsWithTags(Builder $builder):Builder{
        return $builder
            ->select(["id", "title"])
            ->with("tags:id,tag")
            ->whereHas("tags");
    }

    public  function scopeWhereDoesntHaveTagsWithTags(Builder $builder):Builder{
        return $builder
            ->select(["id", "title"])
            ->with("tags:id,tag")
            ->whereDoesntHave("tags");
    }
}
