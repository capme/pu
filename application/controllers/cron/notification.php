<?php

/**
 * Class Notification
 * @property Users_m $users_m
 * @property Notification_m $notification_m
 *
 */
class Notification extends CI_Controller {

    public function digestEmail(){
        $this->load->model(array('notification_m','users_m'));

        log_message('debug','[cron/notification.digestEmail]: START');

        $notifications = $this->notification_m->getUnsendNotification();

        foreach( $notifications as $notification ){
            $sender = $this->users_m->getUser($notification['sender_id'])->row_array();

            $subject = 'Something new just happened';
            $message = '<a href="'.base_url('notification/read?id='.$notification['id'].'&url='.urlencode($notification['url'])).'">'.$notification['message'].'</>';

            $users = $this->users_m->userListByGroup($notification['group_ids']);
            foreach( $users as $user ){
                //if( $user['pkUserId'] < 3) { continue; }
                $_send = $this->notification_m->sendEmail($sender, $user, $subject, $message);
                log_message('debug','[cron/notification.digestEmail]: from('.$sender['fullname'].', '.$sender['email'].') to('.$user['email'].') subject('.$subject.') send['.$_send.']');
            }

            $this->notification_m->setAsSent($notification['id']);
        }

        log_message('debug','[cron/notification.digestEmail]: END');

    }

}
