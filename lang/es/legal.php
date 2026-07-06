<?php

declare(strict_types=1);

return [
    'last_updated' => 'Última actualización: :date',
    'back_home' => 'Volver al inicio',
    'contact' => '¿Dudas? Contáctanos en :email.',

    'terms' => [
        'page_title' => 'Términos de Servicio',
        'title' => 'Términos de Servicio',
        'intro' => 'Estos Términos de Servicio ("Términos") rigen tu acceso y uso de TryPost ("el Servicio", "nosotros"). Al crear una cuenta o usar el Servicio, aceptas estos Términos. Si no estás de acuerdo, no uses el Servicio.',

        'sections' => [
            [
                'heading' => '1. Elegibilidad',
                'body' => 'Debes tener al menos 16 años para usar TryPost. Al usar el Servicio, declaras que cumples este requisito y que tienes autoridad para vincular a cualquier organización en cuyo nombre actúes.',
            ],
            [
                'heading' => '2. Cuenta y seguridad',
                'body' => 'Eres responsable de proteger tus credenciales y de toda actividad en tu cuenta. Notifícanos de inmediato cualquier acceso no autorizado. Podemos suspender o cerrar cuentas que infrinjan estos Términos o la ley aplicable.',
            ],
            [
                'heading' => '3. Conexión de cuentas de redes sociales',
                'body' => 'TryPost permite conectar cuentas de plataformas terceras (TikTok, Meta/Facebook/Instagram/Threads, X, LinkedIn, YouTube, Pinterest, Bluesky, Mastodon, Google Drive, entre otras) vía OAuth. Autorizas a TryPost a actuar en tu nombre solo dentro de los scopes que concedas explícitamente. Puedes desconectar cualquier cuenta en cualquier momento desde la configuración del workspace, lo que revoca los tokens almacenados.',
            ],
            [
                'heading' => '4. Tu contenido',
                'body' => 'Mantienes la plena titularidad del contenido que subas o programes mediante TryPost ("Tu Contenido"). Otorgas a TryPost una licencia limitada y no exclusiva para almacenar, procesar y transmitir Tu Contenido únicamente para operar el Servicio, incluyendo publicarlo en las plataformas terceras que indiques. Eres el único responsable por Tu Contenido y por el cumplimiento de los términos de cada plataforma.',
            ],
            [
                'heading' => '5. Uso aceptable',
                'body' => 'Aceptas no usar TryPost para publicar contenido ilícito, infractor, abusivo, engañoso o que infrinja los términos de cualquier plataforma conectada. Podemos eliminar contenido o cerrar cuentas que infrinjan esta política.',
            ],
            [
                'heading' => '6. Suscripciones y cobros',
                'body' => 'Los planes de pago se cobran mediante Stripe. Las tarifas se cobran por adelantado por el ciclo de facturación seleccionado. Puedes cancelar en cualquier momento; la cancelación surte efecto al final del ciclo actual y no se emiten reembolsos por períodos parciales, salvo que la ley lo exija.',
            ],
            [
                'heading' => '7. Funcionalidades de IA',
                'body' => 'TryPost ofrece funciones asistidas por IA (redacción, refinado, traducción). Las respuestas son generadas por proveedores de IA terceros y pueden contener imprecisiones. Eres responsable de revisar las salidas antes de publicar.',
            ],
            [
                'heading' => '8. Disponibilidad del Servicio',
                'body' => 'Proporcionamos TryPost "tal cual" y "según disponibilidad". No garantizamos operación ininterrumpida. Las plataformas terceras pueden cambiar sus APIs o límites en cualquier momento, lo que puede afectar la publicación.',
            ],
            [
                'heading' => '9. Limitación de responsabilidad',
                'body' => 'En la máxima medida permitida por la ley, TryPost no responde por daños indirectos, incidentales o consecuenciales, ni por pérdida de datos, ganancias o negocios derivados del uso del Servicio.',
            ],
            [
                'heading' => '10. Terminación',
                'body' => 'Puedes eliminar tu cuenta en cualquier momento desde la configuración. Tras la eliminación, borramos o anonimizamos tus datos personales en un plazo de 30 días, salvo cuando la retención sea exigida por ley (p. ej., registros financieros).',
            ],
            [
                'heading' => '11. Cambios en estos Términos',
                'body' => 'Podemos actualizar estos Términos periódicamente. Los cambios materiales se comunicarán por correo electrónico o aviso in-app con al menos 14 días de antelación. El uso continuado tras la fecha efectiva constituye aceptación.',
            ],
            [
                'heading' => '12. Ley aplicable',
                'body' => 'Estos Términos se rigen por las leyes de Brasil. Cualquier disputa se resolverá en el foro del domicilio del usuario cuando aplique la ley del consumidor, y en otro caso en los tribunales de São Paulo, SP, Brasil.',
            ],
        ],
    ],

    'privacy' => [
        'page_title' => 'Política de Privacidad',
        'title' => 'Política de Privacidad',
        'intro' => 'Esta Política de Privacidad explica cómo TryPost ("nosotros") recopila, usa y protege tus datos personales. Cumplimos la Ley General de Protección de Datos de Brasil (LGPD) y el Reglamento General de Protección de Datos de la UE (GDPR) cuando aplica.',

        'sections' => [
            [
                'heading' => '1. Datos que recopilamos',
                'body' => 'Al registrarte recopilamos tu nombre, correo electrónico, contraseña (con hash), idioma y zona horaria. Si inicias sesión con Google o GitHub, recibimos el correo del perfil y datos públicos de esos proveedores. Al conectar una plataforma (TikTok, Meta, X, LinkedIn, YouTube, Pinterest, Bluesky, Mastodon, Threads, Google Drive), almacenamos tokens OAuth de acceso y de refresco cifrados, además del ID de la cuenta y el nombre para mostrar qué cuenta está conectada.',
            ],
            [
                'heading' => '2. Contenido que proporcionas',
                'body' => 'Almacenamos publicaciones, leyendas, archivos multimedia y programaciones que crees. Los medios quedan en nuestro almacenamiento y pueden procesarse (redimensionado, transcodificación) para cumplir los requisitos de cada plataforma. Nunca usamos Tu Contenido para entrenar modelos de IA.',
            ],
            [
                'heading' => '3. Uso de datos de TikTok',
                'body' => 'Al conectar TikTok solicitamos únicamente los scopes que autorices. Usamos tus datos de TikTok exclusivamente para (a) mostrar tu perfil (nombre, avatar) dentro de TryPost, (b) publicar el contenido que programes y (c) mostrar analytics de tus propias publicaciones. No compartimos datos de TikTok con terceros, no los usamos para publicidad y no los retenemos más de lo necesario. Puedes revocar el acceso de TryPost en cualquier momento desde la configuración de TikTok o desde TryPost.',
            ],
            [
                'heading' => '4. Pagos',
                'body' => 'Los pagos los procesa Stripe. No almacenamos datos de tarjeta en nuestros servidores: Stripe los guarda en su infraestructura certificada PCI-DSS. Guardamos solo un identificador de cliente Stripe y el estado de la suscripción.',
            ],
            [
                'heading' => '5. Cookies y analítica',
                'body' => 'Usamos cookies esenciales para autenticación y gestión de sesión. Podemos usar analítica respetuosa con la privacidad (PostHog) para entender el uso agregado del producto. No ejecutamos rastreadores publicitarios de terceros.',
            ],
            [
                'heading' => '6. Correo electrónico',
                'body' => 'Enviamos correos transaccionales (verificación de cuenta, invitaciones, recibos de cobro, alertas de seguridad). No enviamos correos de marketing sin opt-in explícito.',
            ],
            [
                'heading' => '7. Compartición de datos',
                'body' => 'Compartimos datos solo con: (a) las plataformas sociales que conectes, exclusivamente para publicar el contenido que indiques; (b) Stripe, para procesar pagos; (c) proveedores de infraestructura (hosting, correo) actuando como encargados bajo contrato; (d) autoridades cuando la ley lo exija.',
            ],
            [
                'heading' => '8. Retención de datos',
                'body' => 'Conservamos los datos de tu cuenta mientras esté activa. Tras la eliminación de la cuenta, borramos o anonimizamos los datos personales en un plazo de 30 días, salvo cuando la retención sea exigida por ley (p. ej., facturas durante 5 años).',
            ],
            [
                'heading' => '9. Tus derechos',
                'body' => 'Bajo la LGPD y el GDPR tienes derecho a acceder, corregir, exportar, restringir el tratamiento o eliminar tus datos personales. Ejerce estos derechos desde la configuración de la cuenta o contactándonos en el correo de abajo.',
            ],
            [
                'heading' => '10. Seguridad',
                'body' => 'Usamos HTTPS en todo el tráfico, ciframos tokens OAuth y campos sensibles en reposo, y seguimos buenas prácticas de seguridad de aplicaciones. Ningún sistema es perfectamente seguro; usa una contraseña fuerte y única.',
            ],
            [
                'heading' => '11. Transferencias internacionales',
                'body' => 'Nuestros servidores y encargados pueden estar fuera de tu país. Cuando transferimos datos entre fronteras, usamos Cláusulas Contractuales Tipo o salvaguardas equivalentes.',
            ],
            [
                'heading' => '12. Menores',
                'body' => 'TryPost no está destinado a menores de 16 años. No recopilamos intencionalmente datos personales de menores de 16. Si crees que lo hicimos, contáctanos para eliminarlos.',
            ],
            [
                'heading' => '13. Cambios en esta política',
                'body' => 'Podemos actualizar esta Política. Los cambios materiales se comunicarán por correo electrónico o aviso in-app con al menos 14 días de antelación.',
            ],
        ],
    ],
];
