<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? null;
        $this->password_actual = $args['password_actual'] ?? null;
        $this->password_nuevo = $args['password_nuevo'] ?? null;
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // Validar el login del usuario
    public function validarLogin() {
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }

        return self::$alertas;
    }

    // Validacion para cuentas nuevas
    public function validarNuevaCuenta(){
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }

        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }

        if ($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Los password son diferentes';
        }

        return self::$alertas;
    }

    // Valida el password
    public function validarPassword(){
        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }

        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Valida un Email
    public function validarEmail(){
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }

    // Validar el perfil del usuario
    public function validar_perfil() {
        if (!$this->nombre) {
            self::$alertas['error'][] = "El Nombre es Obligatorio";
        }
        if (!$this->email) {
            self::$alertas['error'][] = "El Email es Obligatorio";
        }
        return self::$alertas;
    }


    public function nuevo_password() : array {
        if (!$this->password_actual) {
            self::$alertas['error'][] = "El Password Actual no puedo ir vacio";
        }
        if (!$this->password_nuevo) {
            self::$alertas['error'][] = "El Password Nuevo no puedo ir vacio";
        }
        if (strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = "El Password Nuevo debe contener al menos 6 caracteres";
        }
        return self::$alertas; 
    }   

    // Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->password);
    }


    //  Hashea el password
    public function hashPassword() : void{
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }


    // Generar un Token
    public function crearToken() : void {
        $this->token = uniqid();
        // otra manera: con md5, pero retorna 32 caracteres, se debe modificar la base de datos
        // $this->token = md5( uniqid() );
    }
}