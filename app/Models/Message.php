<?php

namespace App;

use App\Core\Base;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $sender_id
 * @property integer $recipient_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $read_at
 * @property string $guid
 * @property string $data
 * @property string $created_at
 * @property string $updated_at
 */
class Message extends Base
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['sender_id', 'recipient_id', 'created_by', 'updated_by', 'read_at', 'guid', 'data', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Move this to  Base model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
//    public function user()
//    {
//        return $this->belongsTo('App\User', 'created_by');
//    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
//     */
//    public function user()
//    {
//        return $this->belongsTo('App\User', 'updated_by');
//    }
}
