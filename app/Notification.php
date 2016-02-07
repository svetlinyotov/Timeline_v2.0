<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'type', 'is_read', 'more'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function add($user_id, $type, $more = null)
    {
        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->type = $type;
        if($more != null)
            $notification->more = json_encode($more);
        $notification->save();
    }

    public static function count($user_id, $unread = true)
    {
        if($unread === true)
            $count = Notification::where('user_id', $user_id)->where('is_read', 0)->count();
        else
            $count = Notification::where('user_id', $user_id)->count();

        return $count;
    }

    public static function read($user_id, $unread = true, int $limit = 500, $paginate = false)
    {
        if($unread === true)
            $notifications = Notification::where('user_id', $user_id)->where('is_read', 0)->orderBy('id', 'desc');
        else
            $notifications = Notification::where('user_id', $user_id)->orderBy('id', 'desc');

        if(is_numeric($paginate) && $paginate > 0) $notifications = $notifications->paginate($paginate); else $notifications = $notifications->limit($limit)->get();

        return $notifications;
    }

    public static function format($obj)
    {
        $data = [];
        foreach ($obj as $notification) {
            $text = "";
            $link = "";
            $icon = "";
            $more = json_decode($notification->more);

            switch($notification->type) {
                case 'USER_UPDATE_BY_ADMIN': $text = "Your personal details are changed by " . User::find($more->admin_id)->info()->pluck('names') . " (administrator)"; $icon = 'user'; $link = asset('/profile'); break;
                case 'CREATE_EVENT': $text = "You have new event from" . User::find($more->admin_id)->info()->pluck('names') . " (administrator). <br><b>Start:</b> $more->start <br><b>End:</b> $more->end <br> Please specify if you accept it."; $icon = 'calendar'; $link = asset('/rosters'); break;
                case 'UPDATE_EVENT': $text = "You have updated event <i>$more->title</i> from" . User::find($more->admin_id)->info()->pluck('names') . " (administrator). <br><b>Start:</b> $more->start <br><b>End:</b> $more->end "; $icon = 'calendar'; $link = asset('/rosters'); break;

                default: $text=$notification->type; break;
            }

            array_push($data, [
                'text' => $text,
                'icon' => $icon,
                'date' => Common::timeAgo($notification->created_at),
                'id' => $notification->id,
                'is_read' => $notification->is_read,
                'link' => $link
            ]);
        }

        return $data;
    }

    public static function markAsRead($notification_id)
    {
        $notification = Notification::find($notification_id);
        $notification->is_read = 1;
        $notification->save();
    }
}
