<?php


namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Classes\Email;

class LoginController {

    public static function login(Router $router){
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();
            
            if (empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);
                if (!$usuario && !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                } else {
                    // El Usuario existe
                    if (password_verify($_POST['password'], $usuario->password)) {
                        // Iniciar sesion
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionar
                        header('Location: /dashboard');
                        //debuguear($_SESSION);
                    } else {
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }
                //debuguear($usuario);
            }
        }
        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('auth/login', [
            'titulo'=> 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }


    public static function logout(){
        session_start();
        $_SESSION = [];
        header('Location: /');
    }


    public static function crear(Router $router){
        $alertas = [];
        $usuario = new Usuario;
        //debuguear($usuario);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();    //debuguear($alertas);
            
            if (empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);   //debuguear($existeUsuario);

                // Compronar si el usuario existe
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();
                    // Elimina el password2 porque no lo necesitamos
                    unset($usuario->password2);
                    // Asignar token
                    $usuario->crearToken();    //debuguear($usuario);
                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();
                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token); //debuguear($email);
                    $email->enviarConfirmacion();   
                    
                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }
        // Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }



    public static function olvide(Router $router){
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email); //debuguear($usuario);

                if ($usuario && $usuario->confirmado) {
                    // Encontré al Usuario
                    // Generara un nuevo token
                    $usuario->crearToken();
                    // Eliminar Password2
                    unset($usuario->password2);
                    // Actualizar el usuario
                    $usuario->guardar();
                    // Enviar el Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Imprimir una Alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email'); 
                    //debuguear($usuario);
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }   
            }
        }
        $alertas = Usuario::getAlertas(); // Mando a llamar las alertas: error o exito
        // Render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Recuperar Password',
            'alertas' => $alertas
        ]);
    }


    public static function reestablecer(Router $router){
        $token = s($_GET['token']);
        $mostrar = true;
        if (!$token) header('Location: /');

        // Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);
        if (empty($usuario)) {
            Usuario::setAlerta('error', 'Token no Válido');
            $mostrar = false;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir un nuevo password
            $usuario->sincronizar($_POST);
            // Validar Password
            $alertas = $usuario->validarPassword();
            
            if(empty($alertas)) {
                // Hashear el nuevo password
                $usuario->hashpassword();
                
                // Eliminar el Token
                $usuario->token = null; //debuguear($usuario);
                // Guardar el usuario en la DB
                $resultado = $usuario->guardar();
                // Redireccionar
                if ($resultado) {
                    header('Location: /');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer tu Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);

    }


    public static function mensaje(Router $router){
        $router->render ('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);
    }

    
    public static function confirmar(Router $router){
        $token = s($_GET['token']);

        if (!$token) Header('Location: /');

        // Encontrar al usuario con el token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            // No se encontro un usuario con ese token
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            //debuguear($usuario);

            // Guardar en la base de datos
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }

        $alertas = Usuario::getAlertas();

        //debuguear($usuario);
        $router->render ('auth/confirmar', [
            'titulo' => 'Confirmar tu Cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}