<?php namespace App\Http\Controllers\Admin;

use App\Comment;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;

class CommentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }
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
        if (!Auth::user()->can('observations-add')) {
            abort(403);
        }
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
        if (!Auth::user()->can('observations-add')) {
            abort(403);
        }
        $params = $request->input();
        $comment = new Comment;
        $comment->msg_id = $params['msg_id'];
        $comment->qtype = $params['qtype'];
        $comment->obs_key = $params['obs_key'];
        $comment->description = $params['description'];
        $comment->icon = $params['icon'];
        $comment->category = $params['category'];
        $comment->save();
        return redirect()->route('admin.comments.edit', [$comment->qid])->with('messages', ['successfully added new comment - '.$params['msg_id']])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }
        // display view
        $comment = Comment::find($id);
        return view('admin.comments.show', [ 'comment' => $comment, 'errors' => array(), 'messages' => array() ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->can('observations-edit')) {
            abort(403);
        }
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
        if (!Auth::user()->can('observations-edit')) {
            abort(403);
        }
        $params = $request->input();
        $comment = Comment::find($id);
        $comment->msg_id = $params['msg_id'];
        $comment->qtype = $params['qtype'];
        $comment->obs_key = $params['obs_key'];
        $comment->description = $params['description'];
        $comment->icon = $params['icon'];
        $comment->category = $params['category'];
        $comment->save();
        return redirect()->back()->with('messages', ['successfully updated comment'])->send();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('observations-destroy')) {
            abort(403);
        }
        Comment::destroy($id);
        return redirect()->back()->with('messages', ['successfully removed comment'])->send();
    }
}
