<?php

return [

    'project_status' => [
        'brief' => 'Brief',
        'design' => 'Diseño',
        'development' => 'Desarrollo',
        'review' => 'Revisión',
        'delivered' => 'Entregado',
        'paused' => 'En pausa',
    ],

    'requirement_status' => [
        'pending' => 'Pendiente',
        'client_marked' => 'Por revisar',
        'received' => 'Recibido',
    ],

    'invitation' => [
        'mail_subject' => ':client te invitó a su espacio en noehassiel',
        'mail_greeting' => 'Hola,',
        'mail_body' => 'Te damos acceso al espacio de :client, donde vas a poder ver el avance de sus proyectos, lo que aún falta entregar y sus facturas.',
        'mail_action' => 'Crear mi acceso',
        'mail_expiry' => 'El enlace es válido por :days días. Si no esperabas este correo, puedes ignorarlo.',
        'title' => 'Crea tu acceso',
        'description' => 'Te invitaron al espacio de :client. Define tu nombre y una contraseña para entrar.',
        'name' => 'Tu nombre',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirma tu contraseña',
        'submit' => 'Crear acceso',
        'invalid' => 'Esta invitación ya se usó o expiró. Pide una nueva a Hassiel.',
        'email_taken' => 'Ya existe una cuenta con este correo. Inicia sesión con ella.',
    ],

    'home' => [
        'title' => 'Tus proyectos',
        'greeting' => 'Hola, :name',
        'empty' => 'Todavía no hay proyectos activos.',
        'outstanding' => '{0} Todo entregado|{1} Falta 1 cosa por entregar|[2,*] Faltan :count cosas por entregar',
    ],

];
