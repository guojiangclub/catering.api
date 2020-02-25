<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/22
 * Time: 15:18
 */

namespace ElementVip\Server\Http\Controllers;


use ElementVip\Component\Order\Models\Comment;
use ElementVip\Server\Transformers\CommentTransformer;

class CommentsController extends Controller
{
    public function index()
    {
        $user = request()->user();

        $comments = Comment::where('user_id', $user->id)
            ->with('goods')->with('orderItem')->orderBy('created_at', 'desc')->paginate(10);

        return $this->response()->paginator($comments, new CommentTransformer());

    }
}