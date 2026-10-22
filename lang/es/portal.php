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

    'nav' => [
        'projects' => 'Proyectos',
        'invoices' => 'Facturas',
        'security' => 'Seguridad',
        'logout' => 'Salir',
        'label' => 'Secciones del portal',
    ],

    'home' => [
        'title' => 'Tus proyectos',
        'greeting' => 'Hola, :name',
        'empty' => 'Todavía no hay proyectos activos.',
        'outstanding' => '{0} Todo entregado|{1} Falta 1 cosa por entregar|[2,*] Faltan :count cosas por entregar',
        'due' => 'Entrega estimada :date',
        'recent_invoices' => 'Facturas recientes',
        'all_invoices' => 'Ver todas',
    ],

    'project' => [
        'back' => 'Tus proyectos',
        'progress' => 'Avance',
        'starts' => 'Inicio',
        'due' => 'Entrega estimada',
        'checklist' => 'Lo que necesitamos de ti',
        'checklist_hint' => 'Súbelo a tu carpeta compartida y márcalo aquí. Lo reviso y te confirmo.',
        'checklist_empty' => 'Por ahora no necesitamos nada más de ti.',
        'all_received' => 'Recibimos todo. Gracias.',
        'mark_uploaded' => 'Ya lo subí',
        'marked_at' => 'Marcado :time · lo estoy revisando',
        'received_at' => 'Recibido :time',
        'due_on' => 'Para el :date',
        'folders' => 'Carpetas compartidas',
        'folders_hint' => 'Aquí es donde subes los recursos del proyecto.',
        'folders_empty' => 'Aún no hay carpetas. Comparte una conmigo y la agrego aquí.',
        'open_folder' => 'Abrir',
    ],

    'invoices' => [
        'title' => 'Facturas',
        'empty' => 'Todavía no hay facturas.',
        'download' => 'Descargar ZIP',
        'unavailable' => 'Archivo no disponible',
        'issued' => 'Emitida el :date',
    ],

    'security' => [
        'title' => 'Seguridad',
    ],

    'reminder' => [
        'subject' => '{1} Falta 1 cosa para avanzar con tu proyecto|[2,*] Faltan :count cosas para avanzar con tu proyecto',
        'greeting' => 'Hola,',
        'body' => 'Para seguir avanzando necesitamos lo siguiente. Súbelo a tu carpeta compartida y márcalo como "Ya lo subí" en tu portal.',
        'action' => 'Ir a mi portal',
        'footnote' => 'Te escribimos cada pocos días mientras haya pendientes. Si ya lo enviaste, márcalo en el portal y dejamos de recordártelo.',
    ],

];
