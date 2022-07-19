<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageReceived;
use App\Helpers\StripeHelper;
use App\Http\Controllers\Controller;
use App\Model\Media;
use App\Models\Message;
use App\Models\User;
use App\Notifications\OnboardingRequired;
use App\Traits\InteractWithUpload;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use InteractWithUpload;

    public function detail()
    {
        return \Auth::user()->withNotifications();
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function upload(Request $request)
    {
        DB::transaction(function () use ($request) {
            $uploadData = $this->uploadImage($request, Auth::user());
            /// todo handle it in Interact with upload making a method which remove the old one and create new
            $user = \Auth::user();

            $hasPreviousImage = Auth::user()->getRawOriginal('profile_url');

            if (!empty($hasPreviousImage)) {
                $previous_media = Auth::user()->media()->where('type', User::MEDIA_UPLOAD)->first();

                Storage::delete('public/' . $hasPreviousImage);
                $previous_media->delete();
            }

            $user->fill(['profile_url' => $uploadData['absolute_path']]);
            $user->save();
        });

    }

    public function conversations()
    {
        $userId = Auth::user()->id;
        return DB::select("SELECT messages.*,
                     CASE
                     WHEN sender_id!=$userId  THEN (select name from users where id = sender_id)
                      WHEN recipient_id!=$userId THEN (select name from users where id = recipient_id)
		            END as recipient_name
		          FROM
            (SELECT MAX(id) AS id
         FROM messages
         WHERE $userId IN (sender_id,recipient_id)
         GROUP BY CASE WHEN  $userId = sender_id THEN recipient_id ELSE sender_id END
         ) AS latest
        LEFT JOIN messages USING(id)
        	ORDER BY messages.updated_at desc");
    }

    public function messages(User $user)
    {
        return Message::whereIn('sender_id', [Auth::user()->id, $user->id])
            ->whereIn('recipient_id', [Auth::user()->id, $user->id])
            ->orderBy('created_at', 'desc')
            ->with(['sender' => function (BelongsTo $belongsTo) {
                $belongsTo->select(['id', 'name']);
            }])
            ->paginate();
    }

    public function sendMessage(User $user, Request $request)
    {
        $message = new Message();
        $message->sender_id = Auth::user()->id;
        $message->recipient_id = $user->id;
        $message->data = $request->get('message');
        $message->save();

        MessageReceived::trigger($user);

        return $this->genericResponse(true, 'Message sent successfully.');
    }

    //@todo Request handling
    public function update(Request $request)
    {
        if (Auth::check()) {
            // $user = User::where('id', Auth::user()->id);

            User::where('id', Auth::user()->id)->update($request->all());

            return $this->genericResponse(true, "Profile Updated");
        }

    }

    public function refreshOnboardingUrl(User $user)
    {
        $accountLink = StripeHelper::createAccountLink($user);
        $user->notifications()->where('data', 'LIKE', "%$user->stripe_account_id%")->delete();
        $user->notify(new OnboardingRequired($accountLink));

        return $accountLink->url;
    }
}
