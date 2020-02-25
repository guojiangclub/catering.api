<?php

namespace ElementVip\Server\Http\Controllers;


use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use ElementVip\Component\User\Models\User;
use Cmgmyr\Messenger\Traits\Messagable;
use ElementVip\Server\Transformers\MessagesTransformer;
use Carbon\Carbon;
use Validator;

class MessagesController extends Controller
{
    public function getMessagesByUser()
    {
        $massages= Thread::whereHas('participants',function($query){
            $query->where('user_id', request()->user()->id);
        })->with('messages')->latest('updated_at')->paginate(15);
        return $this->response()->paginator($massages, new MessagesTransformer());
    }
    
    public function createMessagesByUser()
    {

        $input = Input::all();

        $validator = Validator::make($input, [
            'subject' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->api('',false,400,'缺少参数');
        }

        $thread = Thread::create(
            [
                'subject' => $input['subject'],
            ]
        );
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => request()->user()->id,
                'body'      => $input['message'],
            ]
        );
        Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => request()->user()->id,
                'last_read' => new Carbon,
            ]
        );

        if (Input::has('recipients')) {
            $thread->addParticipant($input['recipients']);
        }

        return $this->api();

    }

    public function getMassgesById($id)
    {
        try {
            $massges = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->api('',false,400,'没有找到该信息');
        }
        $userId = request()->user()->id;
        $massges->markAsRead($userId);

        return $this->api($massges);

    }

    public function updateMassges()
    {
        $id=Input::get('id');
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->api('',false,400,'没有找到该信息');
        }
        $thread->activateAllParticipants();

        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => request()->user()->id,
                'body'      => Input::get('message'),
            ]
        );
        $participant = Participant::firstOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id'   => request()->user()->id,
            ]
        );
        $participant->last_read = new Carbon;
        $participant->save();
        if (Input::has('recipients')) {
            $thread->addParticipant(Input::get('recipients'));
        }

        return $this->api(['id'=>$thread['id']]);

    }

    public function getNewThreadsCountByUser()
    {
//        $newMessages=request()->user()->threadsWithNewMessages();
        $newMessagesCount=request()->user()->newThreadsCount();
        return $this->api(['count'=>$newMessagesCount]);
    }
}