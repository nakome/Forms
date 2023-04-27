<?php

declare (strict_types = 1);

define('DEBUG', true);
define('MINIMUM_PHP', '7.4.0');

// Poner locale de España
setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');

// Cabeceras
header("X-Powered-By: Moncho Varela :)");
header('Strict-Transport-Security: max-age=31536000');
header("Content-Security-Policy: script-src 'self' 'unsafe-inline'");
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Compara la version
if (version_compare($ver = PHP_VERSION, $req = MINIMUM_PHP, '<')) {
    $out = sprintf('Usted esta usando PHP %s, pero AntCMs necesita <strong>PHP %s</strong> para funcionar.', $ver, $req);exit($out);
}

// Comprobar Debug
if (DEBUG == true) {
    @ini_set('error_reporting', (string)E_ALL);@ini_set('display_errors', (string) 1);
} else {
    @ini_set('error_reporting', (string)E_ALL);@ini_set('display_errors', (string) 0);
}

/**
 * Clase Forms: maneja la validación y el envío de formularios de contacto.
 *
 * Primero, se define un array $options que contiene dos claves:
 * 'email': el correo electrónico donde se enviaran los correos.
 * 'allow_urls': un array de URLs permitidas para enviar formularios.
 * 'token': una contraseña para validar el envío del formulario.
 * Luego, se crea un objeto Forms pasando como argumento el array $opts. Si el método de solicitud es POST,
 * se llama al método init de Forms que realiza lo siguiente:
 *
 * Obtiene la URL del formulario y extrae el dominio.
 * Si el dominio está incluido en el array de URLs permitidas, procede a validar la contraseña del formulario.
 * Si la contraseña es válida, se obtienen los valores de los campos del formulario.
 * Se construye un correo electrónico con los valores obtenidos y se intenta enviar utilizando la función mail.
 * Si el correo electrónico se envió correctamente, se redirige a la página de éxito especificada en el formulario.
 * Si hubo algún error al enviar el correo electrónico, se redirige a la página de error especificada en el formulario.
 * Si el correo electrónico no se pudo enviar debido a una contraseña inválida, se muestra un mensaje de error.
 * En caso contrario, si el método de solicitud no es POST, se muestra un mensaje de error indicando que el envío de formularios es necesario tener las credenciales para usarlo.
 *
 *
 * @param array $options Opciones para la clase, incluyendo el token de seguridad y las URL permitidas para el envío de formularios.
 *
 * @return void
 */
class Forms
{
    /**
     * Token de seguridad utilizado para validar el envío del formulario.
     *
     * @var string
     */
    private $__token;

    /**
     * Lista de URLs permitidas para el envío del formulario.
     *
     * @var array
     */
    private $__allow_urls;

    /**
     * Correo electrónico
     *
     * @var string
     */
    private $__email;

    /**
     * Constructor de la clase Forms.
     *
     * @param array $options Opciones para la clase, incluyendo el token de seguridad y las URL permitidas para el envío de formularios.
     *
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->token = $options['token'];
        $this->allow_urls = $options['allow_urls'];
        $this->email = $options['email'];
    }

    /**
     * Obtiene el valor del parámetro especificado de la solicitud POST y lo sanitiza.
     *
     * @param string $key El nombre del parámetro que se desea obtener y sanear.
     * @return string El valor sanado del parámetro especificado.
     */
    public function post(string $key = ""): string
    {
        return $this->__sanitizeRequest("POST", $key);
    }

    /**
     * Obtiene el valor del parámetro especificado de la solicitud GET y lo sanitiza.
     *
     * @param string $key El nombre del parámetro que se desea obtener y sanear.
     * @return string El valor sanado del parámetro especificado.
     */
    public function get(string $key = ""): string
    {
        return $this->__sanitizeRequest("GET", $key);
    }

    /**
     * Sanitiza un valor de solicitud GET o POST según el tipo especificado.
     *
     * @param string $type El tipo de solicitud (GET o POST).
     * @param string $key El nombre del parámetro que se desea sanear.
     * @return string El valor sanado del parámetro especificado.
     */
    private function __sanitizeRequest(string $type = "GET", string $key = ""): string
    {
        $request = ($type == "GET") ? INPUT_GET : INPUT_POST;
        $value = filter_input($request, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK | FILTER_FLAG_NO_ENCODE_QUOTES);
        $value = ($value) ? urldecode($value) : "";
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        $value = trim($value);return $value;
    }

    /**
     * Redirige al usuario a la URL especificada utilizando el código de estado y tiempo de espera opcionales.
     *
     * @param string $url La URL a la que se debe redirigir al usuario.
     * @param int $st El código de estado HTTP a enviar con la redirección (301 o 302).
     * @param int $wait El tiempo de espera en segundos antes de que se produzca la redirección.
     * @return void
     */
    public function redirect($url, $st = 200, $wait = 0)
    {
        $url = (string)$url;
        $st = (int)$st;
        $msg = [301 => '301 Movido permanentemente', 302 => '302 Encontrado'];
        header('HTTP/1.1 ' . $st . ' ' . ($msg[$st] ?? '302 Found'));
        if ($wait > 0) {
            sleep($wait);
        }
        header("Location: {$url}");
        exit(0);
    }

    /**
     * Inicializa la validación y el envío del formulario.
     *
     * @return void
     */
    public function init(): void
    {
        // Obtiene la URL del formulario y verifica si está en la lista de URLs permitidas.
        $urlFormulario = $_SERVER["HTTP_REFERER"];
        $partesUrl = parse_url($urlFormulario);
        $dominioFormulario = $partesUrl["host"];
        if (in_array($dominioFormulario, $this->allow_urls)) {
            // Valida el token de seguridad del formulario.
            if (password_verify($this->post('token'), $this->token)) {
                // Obtiene los datos del formulario y los prepara para su envío por correo electrónico.
                $redirectSuccess = $this->post('success_page');
                $redirectError = $this->post('error_page');
                $name = $this->post('name');
                $from = $this->post('email_from');
                $to = $this->email;
                $subject = $this->post('subject');
                $options = $this->post('department');
                $message = $this->post('message');
                $headers = ['Reply-To' => '<reply.to.' . $from . '>', 'From' => '<from.' . $from . '>', 'X-Mailer' => sprintf("PHP %s", phpversion()), 'MIME-Version' => '1.0', 'Content-type' => 'text/html; charset=UTF-8'];
                $message_text = "<!Doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"></head><body><main><header><h1>{$name}</h1><p><small><strong>Para: </strong>{$options}</small> - <small><strong>De: </strong>{$from}</small></p></header><section><h3>{$subject}</h3><p>{$message}</p></section></main></body></html>";
                // Envía el correo electrónico si la dirección de correo es válida, de lo contrario redirige a la página de error.
                if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
                    if (mail($to, $subject, $message_text, $headers)) {
                        $this->redirect($redirectSuccess . '?msg=' . urlencode('Gracias 😀, en breve contactaremos con usted.'));
                    } else { $this->redirect($redirectError . '?msg=' . urlencode('Lo sentimos 😪, el correo no se ha enviado, vuelva a intentarlo dentro de unos minutos.'));}
                } else { $this->redirect($redirectError . '?msg=' . urlencode('Lo sentimos 😪, el correo no se ha enviado parece no ser valido, pruebe con otro.'));}
            } else { die('Lo sentimos 😪, el correo no se ha enviado por que no tienes los permisos suficientes.');}
        } else { die('😪 No estás autorizado para enviar este formulario desde esta URL.');}
    }
}

$opts = [
    // Correo electrónico al que se enviara el mensaje
    'email' => 'demo@gmail.com',
    // Solo permite estas urls
    'allow_urls' => ['localhost'],
    // Token para comprobar como si fuera una contraseña no como un bearer token
    // password_hash('demo123',PASSWORD_BCRYPT)
    'token' => '$2y$10$n5xO5I4XTPt.WZaSGI0x5OEZQoDoBU2dDYrAq8yLXBsb512KfnP2G', // por defecto es demo123
];
// Iniciamos la clase con las opciones
$form = new Forms($opts);
// Si el metodo es post llamamos la función
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form->init();
} else {
    die('Envio de formularios, es necesario tener las credenciales para usarlo.');
}
