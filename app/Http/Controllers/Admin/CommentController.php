<?php namespace App\Http\Controllers\Admin;

use App\Comment;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        // display view
        $comments = Comment::OrderBy('id', 'desc')->limit('100')->paginate(10);
        return view('admin.comments.index', [ 'comments' => $comments ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        // display view
        return view('admin.comments.create', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params = $request->input();
        $comment = new Comment;
        $comment->msg_id = $params['msg_id'];
        $comment->qtype = $params['qtype'];
        $comment->obs_key = $params['obs_key'];
        $comment->description = $params['description'];
        $comment->icon = $params['icon'];
        $comment->category = $params['category'];
        $comment->save();

        return redirect()->route('admin.comments.edit', [$comment->qid])->with(
            'messages',
            ['successfully added new comment - ' . $params['msg_id']]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

        // display view
        $comment = Comment::find($id);
        return view('admin.comments.show', [ 'comment' => $comment, 'errors' => [], 'messages' => [] ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $comment = Comment::find($id);
        return view('admin.comments.edit', [ 'comment' => $comment, 'messages' => \Session::get('messages') ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $params = $request->input();
        $comment = Comment::find($id);
        $comment->msg_id = $params['msg_id'];
        $comment->qtype = $params['qtype'];
        $comment->obs_key = $params['obs_key'];
        $comment->description = $params['description'];
        $comment->icon = $params['icon'];
        $comment->category = $params['category'];
        $comment->save();

        return redirect()->back()->with('messages', ['successfully updated comment']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        Comment::destroy($id);

        return redirect()->back()->with('messages', ['successfully removed comment']);
    }
}
