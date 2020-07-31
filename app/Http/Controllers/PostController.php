<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Post;
use App\PostLike;
use App\PostComment;

class PostController extends Controller
{
    private $loggedUser;
    
    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id) 
    {
        $array = ['error' => ''];

        // Verificar se o post existe
        $postExists = Post::find($id);
        if ($postExists) {
            // verificar se eu ja deu like nesse post
            $isLiked = PostLike::where('id_post', $id)
                ->where('id_user', $this->loggedUser['id'])
                ->count();

            if ($isLiked > 0) {
                // remover like
                $pl = PostLike::where('id_post', $id)
                    ->where('id_user', $this->loggedUser['id'])
                    ->first();
                $pl->delete();

                $array['isLiked'] = false;
            } else {
                //adicionar like
                $newPostLike = new PostLike();
                $newPostLike->id_post = $id;
                $newPostLike->id_user = $this->loggedUser['id'];
                $newPostLike->created_at = date('Y-m-d H:i:s');
                $newPostLike->save();

                $array['isLiked'] = true;
            }
            
            $likeCount = PostLike::where('id_post', $id)->count();
            $array['likeCount'] = $likeCount;
        } else {
            $array['error'] = 'Post não existe!';
            return $array;
        }
        

        return $array;
    }

    public function comment(Request $request, $id)
    {
        $array = ['error' => ''];

        $txt = $request->input('txt');

        $postExists = Post::find($id);
        if ($postExists) {
            if ($txt) {
                
                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $this->loggedUser['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();

            } else {
                $array['error'] = 'Não enviou mensagem.';
                return $array;
            }
            
        } else {
            $array['error'] = 'Post não existe!';
            return $array;
        }

        return $array;
    }

}
