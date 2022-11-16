<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected  $model = Tag::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition():array
    {
        return [
            "tag" => $this->faker->text(15)
        ];
    }
}
