<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageReceived;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        // validation on recipient id

        $message = new Message();
        $message->sender_id = \Auth::user()->id;
        $message->recipient_id = $request->get('recipient_id');
        $message->data = $request->get('message');
        $message->notifiable_type = Product::class;
        $message->notifiable_id = $request->get('notifiable_id');
        $message->save();

        MessageReceived::trigger($user);

        return $this->genericResponse(true, 'Message sent successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }

    public function conversations()
    {
        $authenticatedUserId = \Auth::user()->id;

        return Message::selectRaw("notifiable_id,
	                notifiable_type ,
	                max(id) as id,
	                max(recipient_id) as recipient_id,
	                max(data),
	                max(sender_id) as sender_id,
	                bool_and(sender_id = " . $authenticatedUserId . ") as is_sender")
            ->where(function (Builder $builder) use ($authenticatedUserId) {
                $builder->orWhere("recipient_id", $authenticatedUserId)
                    ->orWhere("sender_id", $authenticatedUserId);
            })->groupBy(['notifiable_type', 'notifiable_id'])
            ->paginate($this->pageSize);
    }
}
