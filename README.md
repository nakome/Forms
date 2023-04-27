# Forms
Archivo para enviar correos básico con la función mail


## Configuración

    $opts = [
        // Correo donde se mandarán los emails.
        'email' => 'demo@gmail.com',
        // Solo permite estas urls
        'allow_urls' => ['localhost', 'example.com'], // Urls sin http o https
        // Token para comprobar como si fuera una contraseña no como un bearer token por defecto es demo123
        // Puedes usar password_hash('tu_password',PASSWORD_BCRYPT)
        'token' => '$2y$10$n5xO5I4XTPt.WZaSGI0x5OEZQoDoBU2dDYrAq8yLXBsb512KfnP2G',
    ];


## Plantilla de ejemplo

puedes ver 2 ejemplos en la carpeta example.
