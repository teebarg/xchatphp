<?php


namespace App\Repositries;

use App\Events\MessageSentEvent;
use App\Helpers\ResponseHelper;
use App\Http\Resources\PrivateMessageResource;
use App\Message;

class MessageRepository extends Repository
{
    /**
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        return Message::class;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMessages($id) {
        $message = Message::where(function ($query) use ($id) {
            $query->where([['user_id', '=', auth()->user()->id], ['to', '=', $id]])
                ->orWhere([['user_id', '=', $id], ['to', '=', auth()->user()->id]]);
        })->orderBy('created_at', 'desc')->paginate(20);
//        dd($message);
        return $message;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function sendMessage($data) {
        $message = auth()->user()->messages()->create($data);
        broadcast(new MessageSentEvent($message))->toOthers();
        return $message;
    }
}
