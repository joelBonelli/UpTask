<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Proyecto;
use Classes\Email;

class DashboardController {

    public static function index(Router $router) {
        session_start();  // duran determinados minutos en el servidos 24minutos aprox. se cambia desde el init
        isAuth();  // Protejer la ruta

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);
        //debuguear($proyectos);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }


    public static function crear_proyecto(Router $router) {
        //Creamos la session y comprobamos si esta autenticado
        session_start();
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            // Validacion
            $alertas = $proyecto->validarProyecto();

            if (empty($alertas)) {
                // Generar una URL UNICA
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                // Almacenar el creadro del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                // Guardar el proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
                //debuguear($proyecto);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }


    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        $token = $_GET['id'];
        if(!$token) header('Location: /dashboard');

        // Revisar que la persona que visita el proyecto, sea quien lo creo
        $proyecto = Proyecto::where('url', $token);      //debuguear($proyecto);
        if ($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }



    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);
        //debuguear($usuario);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            //debuguear($usuario);
            $alertas = $usuario->validar_perfil();

            if (empty($alertas)) {
                // Varificar que el email no exista por otro usuario
                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Email no válido, ya pertenece a otra cuenta');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Guardar el usuario
                    $usuario->guardar();
    
                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                    $alertas = Usuario::getAlertas();
                    // Asignamos el nuevo nombre a la session, para q se refleje en la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }


    public static function cambiar_password(Router $router) {
        session_start();
        isAuth();
        $alertas = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevo_password();

            if (empty($alertas)) {
                $resultado = $usuario->comprobar_password();
                if ($resultado) {
                    $usuario->password = $usuario->password_nuevo;

                    // Eliminar las propiedades no necesarioas
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    // Hashear el nuevo password
                    $usuario->hashPassword();

                    // Actualizar
                    $respuesta = $usuario->guardar();
                    if ($respuesta) {
                        Usuario::setAlerta('exito', 'Password Guardado Correctamente');
                        $alertas = $usuario->getAlertas();
                    }
                } else {
                    Usuario::setAlerta('error', 'Password Incorrecto');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}