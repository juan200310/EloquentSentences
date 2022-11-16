<?php

use \Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Billing;

/**
 * BUSCA UN POST POR ID
 */
Route::get("/find/{id}", function(int $id){
    return Post::find($id);
});

/**
 * BUSCA UN POST POR ID O RETORNA UN 404
 */
Route::get("/find-or-fail/{id}", function(int $id){

    try {
        return Post::findOrFail($id);
    }catch (ModelNotFoundException $exception){
        return $exception->getMessage();
    }
});

/**
 * BUSCA UN POST POR ID Y SELECCIONA COLUMNAS O RETORNA UN 404
 */
Route::get("/find-or-fail-with-columns/{id}", function(int $id){
    return Post::findOrFail($id,["id", "title"]);
});

/**
 * BUSCA UN POST POR SU SLUG
 */
Route::get("/find-by-slug/{slug}", function(string $slug){

    // metodo 1
    //return Post::where("slug",$slug)->firstOrFail();

    //metodo 2
    //return Post::whereSlug($slug)->firstOrFail();

    //metodo 3
    return Post::firstWhere("slug",$slug);
});

/**
 * OBTENER MUCHOS POST POR ID'S
 */
Route::get("/find-many", function(){

    return Post::find([1,2,3],["id","title"]);
});

/**
 * POST PAGINADOS CON SELECCION DE COLUMNAS
 */
Route::get("/paginated/{perPage}", function(int $perPage = 10){

    return Post::paginate($perPage,["id","title"]);
});

/**
 * POST PAGINADOS MANUALMENTE
 */
Route::get("/manual-pagination/{perPage}/{offset?}", function(int $perPage, int $offset = 0){

    return Post::offset($offset)->limit($perPage)->get();
});

/**
 * CREAR UN POST
 */
Route::get("/create", function(){

    $user = User::all()->random(1)->first()->id;

    return Post::create([
        "user_id" => $user,
        "category_id" => Category::all()->random(1)->first()->id,
        "title" => "Post para el usuario {$user}",
        "content" => "Post de pruebas",
    ]);
});

/**
 * RETORNAR POST O CREAR SI NO EXISTE
 */
Route::get("/first-or-create/{id}", function(int $id){

    $user = User::all()->random(1)->first()->id;

    return Post::firstOrCreate(
        ["title" => "Post para el usuario {$id}"],
        [
            "user_id" => $user,
            "category_id" => Category::all()->random(1)->first()->id,
            "title" => "Nuevo Post para el usuario {$user}",
            "content" => "Nuevo Post de test",
        ]
    );
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR Y TAGS CON TODA LA INFORMACION
 */
Route::get("/with-relations/{id}", function(int $id) {
    return Post::with("user","category","tags")->find($id);
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR Y TAGS CON SELECCION DE COLUMNAS
 */
Route::get("/with-relations-and-columns/{id}", function(int $id) {

    return Post::select(["id","user_id","category_id","title"])
        ->with([

            "user:id,name,email",
            "user.billing:id,user_id,credit_card_number",
            "tags:id,tag",
            "category:id,name",

        ])->find($id);
});

/**
 * BUSCA UN USUARIO Y CARGA EL NUMERO DE POST QUE TIENE
 */
Route::get("/with-count-posts/{id}", function(int $id) {

    return User::select(["id","name","email"])
            ->withCount("posts")
            ->findOrFail($id);
});

/**
 * BUSCA UN POST, PERO SI EXISTE LO ACTUALIZA
 */
Route::get("/update/{id}", function(int $id) {

    return Post::findOrFail($id)->update([
        "title" => "Post actualizado prueba...",
    ]);
});

/**
 * ACTUALIZA SI EXISTE O CREA NUEVO REGISTRO
 */
Route::get("/update-or-create/{slug}", function(string $slug) {

    return Post::updateOrCreate(
      [
          "slug" => $slug],
      [
          "user_id" => User::all()->random(1)->first()->id,
          "category_id" => Category::all()->random(1)->first()->id,
          "title" => "Post de pruebas",
          "content" => "Nuevo contenido del post actualizado..."
      ],
    );
});

/**
 * ELIMINAR POST O TAGS RELACIONADOS
 */
Route::get("/delete-with-tags/{id}", function(int $id) {

    try {

        DB::beginTransaction();

        $post = Post::findOrFail($id);
        $post->tags()->detach();
        $post->delete();

        DB::commit();

        return $post;

    }catch (Exception $exception){

        DB::rollBack();
        return  $exception->getMessage();
    }
});

/**
 * AUMENTAR LIKES A UN POST
 */
Route::get("/like/{id}", function(int $id) {

    return Post::findOrFail($id)->increment("likes");
});

/**
 * CREA UN USUARIO Y SU INFORMACIÓN DE PAGO
 * SI EXISTE EL USUARIO LO UTILIZA
 * SI EXISTE EL MÉTODO DE PAGO LO ACTUALIZA
 */
Route::get("/create-with-relation", function () {
    try {
        DB::beginTransaction();
        $user = User::firstOrCreate(
            ["name" => "cursosdesarrolloweb"],
            [
                "name" => "cursosdesarrolloweb",
                "age" => 40,
                "email" => "eloquent@cursosdesarrolloweb.es",
                "password" => bcrypt("password"),
            ]
        );
        Billing::updateOrCreate(
            ["user_id" => $user->id],
            [
                "user_id" => $user->id,
                "credit_card_number" => "123456789"
            ]
        );
        DB::commit();
        return $user
            ->load("billing:id,user_id,credit_card_number");
    } catch (Exception $exception) {
        DB::rollBack();
        return $exception->getMessage();
    }
});
