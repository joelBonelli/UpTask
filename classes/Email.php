<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    
    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token){
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['DB_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['DB_PORT'];
        $mail->Username = $_ENV['DB_USER'];
        $mail->Password = $_ENV['DB_PASS'];

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Confirma tu Cuenta';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has creado tu cuenta en UpTask, solo debes confirmalar en el siguiente enlace:</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL'] . "/confirmar?token=". $this->token ."'> Confirmar Cuenta </a></p>";
        $contenido .= "<p>Si tu no creaste ésta cuenta, puedes ignorar éste mensaje.</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;
        
        // Enviar el mail
        $mail->send();
    }

    public function enviarInstrucciones(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['DB_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['DB_PORT'];
        $mail->Username = $_ENV['DB_USER'];
        $mail->Password = $_ENV['DB_PASS'];

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Reestablece tu password';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Parece que has olvidado tu password, sigue el siguiente enlace para recuperarlo:</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL'] . "/reestablecer?token=". $this->token ."'> Reestablecer Password </a></p>";
        $contenido .= "<p>Si tu no creaste ésta cuenta, puedes ignorar éste mensaje.</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;
        
        // Enviar el mail
        $mail->send();
    }
    
}