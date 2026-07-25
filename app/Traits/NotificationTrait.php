<?php
namespace App\Traits;

use App\Models\Notification;
use App\Models\NotificationUser;
use Carbon\Carbon;
use Auth;

trait NotificationTrait
{
    public function insertNotification($data)
    {
        if (!$notification = Notification::find($data['notification_id'])) {
            $notification = new Notification();
            $notification->id = $data['notification_id'];
            $notification->notification_type = $data['notification_type'];
            $notification->action_type = $data['action_type'];
            $notification->title = $data['title'];
            $notification->description = $data['description'];
            $notification->url = $data['url'];
            $notification->data = $data['data'];
            $notification->save();
        }

        $existingPivot = NotificationUser::where('notification_id', $data['notification_id'])
            ->where('user_id', $data['user_id'])
            ->exists();

        if (!$existingPivot) {
            $notificationUser = new NotificationUser();
            $notificationUser->notification_id = $data['notification_id'];
            $notificationUser->user_id = $data['user_id'];
            $notificationUser->save();
        }
    }

    public function readNotification($notification){
        $user = Auth::user();
        if($user){
            $userUnreadNotification = NotificationUser::where('notification_id',$notification)->where('user_id',$user->id)->first();
            if($userUnreadNotification) {  
               if (is_null($userUnreadNotification->read_at)) {
                   $userUnreadNotification->read_at = Carbon::now();
                   $userUnreadNotification->save();
               }
            }
        }
    }

}