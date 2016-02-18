<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Messages extends Model
{
    protected $table = 'messages';

    protected $fillable = ['user_id', 'title', 'text', 'is_read', 'send_by'];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'send_by');
    }

    public static function getAllWithUser($id)
    {
        return self::with('user')->where('user_id', $id)->orderBy('id', 'desc')->get();
    }

    public static function getByIdWithUser($id)
    {
        return self::find($id)->with('user')->where('id', $id)->first();
    }

    public static function markAsRead($message_id)
    {
        $notification = Messages::find($message_id);
        $notification->is_read = 1;
        $notification->save();
    }

    public static function countUnseen()
    {
        return self::where('user_id', Auth::user()->id)->where('is_read', 0)->count();
    }

    public static function topUnseen($top)
    {
        return self::with('user')->where('user_id', Auth::user()->id)->where('is_read', 0)->orderBy('id', 'desc')->limit($top)->get();
    }
}
