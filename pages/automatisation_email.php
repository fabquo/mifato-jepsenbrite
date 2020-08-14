<?php

require('./vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
// PDO CONNEXION HERE


    $eventToday = "SELECT * FROM event where date = CURDATE() && DATE_ADD(CURTIME(), INTERVAL +2 HOUR) < hour";
    $eventTodayList = $db->query($eventToday);

    while($eventList = $eventTodayList->fetch()){
        $idevent = $eventList['id'];
        $eventname = $eventList['title'];
        $mailingList = "SELECT * FROM participant, event WHERE participant.Event_id = '$idevent' && event.id = '$idevent'";
        $mailing = $db->query($mailingList);

    while ($participant = $mailing->fetch()) {

        $participantId = $participant['User_id'];
        $mailAddress = "SELECT * FROM user WHERE id = '$participantId'";
        $mAddress = $db->query($mailAddress);

        while ($email = $mAddress->fetch()) {

            $aMail = $email['email'];
            $username = $email['username'];

            $mail = new PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;
            $mail->Host = "smtp.gmail.com";
            $mail->Username = "username@gmail.com";
            $mail->Password = "password";
            $mail->IsHTML(true);
            $mail->addAddress($aMail);
            $mail->setFrom("jepsenbritemifato@gmail.com", "Jepsen-Brite");
            $mail->Subject = 'Event today -'.$eventname .'';
            $content = '<h1>Hi ' . $username . ',</h1><br>
                        It seems you are registered for some event\'s today !
                        <p>Don\'t forget to check : <p>
                        <a href="https://jepsen-brite-mifato.herokuapp.com/pages/event.php?id=' . $idevent . '" > ' . $eventname . ' </a>!<br><br>
                        <p>Mifato team</p>';

            $mail->MsgHTML($content);
            $mail->send();
        }
    }
}
