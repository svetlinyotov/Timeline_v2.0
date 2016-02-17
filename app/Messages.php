<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = 'messages';

    protected $fillable = ['user_id', 'title', 'text', 'is_read', 'send_by'];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'send_by');
    }

    public static function getWithUser($id)
    {
        return self::with('user')->where('id', $id)->get();
    }

    public static function markAsRead($message_id)
    {
        $notification = Messages::find($message_id);
        $notification->is_read = 1;
        $notification->save();
    }
}
