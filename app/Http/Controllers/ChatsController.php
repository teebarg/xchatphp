<?php

namespace App\Http\Controllers;

use App\Events\MessageSentEvent;
use App\Events\NewPrivateMessage;
use App\Helpers\ResponseHelper;
use App\Http\Resources\MessagesResource;
use App\Http\Resources\PrivateMessageResource;
use App\Message;
use App\Repositries\MessageRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatsController extends Controller
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * ChatsController constructor.
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->middleware(['jwt.verify']);
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param User $user
     * @return
     */
    public function getMessages(User $user){
        $messages = $this->messageRepository->getMessages($user->id);
        return MessagesResource::collection($messages)
            ->additional(ResponseHelper::additionalInfo());
    }

    /**
     * @return MessagesResource
     * @throws ValidationException
     */
    public function sendMessage(){
        $data = $this->validate(request(), [
            'to' => 'required|exists:users,id',
            'message' => 'required|max:256',
        ]);
        $message = $this->messageRepository->sendMessage(array_merge($data, ['status' => 'unread']));
        return new MessagesResource($message);
    }
}
