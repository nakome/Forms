# Forms
Archivo para enviar correos básico con la función mail


## Configuración

    $opts = [
        // Solo permite estas urls
        'allow_urls' => ['localhost', 'example.com'], // Urls sin http o https
        // Token para comprobar como si fuera una contraseña no como un bearer token por defecto es demo123
        'token' => '$2y$10$n5xO5I4XTPt.WZaSGI0x5OEZQoDoBU2dDYrAq8yLXBsb512KfnP2G',
    ];


## Plantilla de ejemplo

    <form action="[Url donde se encuentra el archivo por ejemplo http://localhost/forms/]" method="post">
     <input type="hidden" name="email_to" value="[Su correo electronico]"/>
     <input type="hidden" name="token" value="[clave token]"/>
     <input type="hidden" name="success_page" value="[Página de redirecion de correo enviado]"/>
     <input type="hidden" name="error_page" value="[Página de error del correo]"/>
     <input type="hidden" name="subject" id="f-subject" value="Asunto">
     <fieldset>
       <label for="f-name">Nombre Completo</label>
       <input type="text" name="name" id="f-name" placeholder="Su nombre" required="">
       <label for="f-email">Correo electrónico</label>
       <input type="email" name="email_from" id="f-email" placeholder="email@demo.com" required="">
       <label for="_department">Departamento</label>
       <select name="department" id="f-select" required="">
         <option value="" selected="" disabled="">Selecionar</option>
         <option value="Departamento 1">Departamento 1</option>
         <option value="Departamento 2">Departamento 2</option>
         <option value="Departamento 3">Departamento 3</option>
         <option value="Departamento 4">Departamento 4</option>
       </select>
       <label for="message">Message</label>
       <textarea rows="5" name="message" id="f-message" placeholder="Puede preguntar lo que necesite, estaremos encantados de responderle" required=""></textarea>
     </fieldset>
     <input type="submit" value="Enviar correo">
    </form>

