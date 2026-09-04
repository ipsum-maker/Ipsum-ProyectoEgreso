Reglamento de organización de software y Estándares de Codificación de Ipsum:

    • Introducción y Alcance:
	El presente documento establece las normas, convencionales y buenas prácticas obligatorias para el desarrollo de proyectos web en Ipsum. Para garantizar un ecosistema de equipo técnico.
	Este reglamento aplica de forma estricta a todos los archivos HTML, CSS, JavaScript, PHP y modelos de bases de datos relacionales. 
    • Estructura del proyecto y arquitectura de carpetas:
    El proyecto adopta el patrón Modelo-Vista-Controlador (MVC) desacoplado con aislamiento del Document Root (carpeta pública) para garantizar máxima seguridad y organización:
	Estructura de carpetas:
	/proyecto
		/public
			/assets
				/css
				/js
			index.php (Front Controller / Despachador único)
		/app
			/config (Conexión PDO a base de datos)
			/controlador (Controladores de acción única)
			/modelo (Clases de persistencia y consultas SQL)
			/utilidades (Manejo de sesiones, seguridad CSRF, validaciones)
			/vista (Plantillas de renderizado organizadas por rol)
				/administrador
				/coordinacion
				/profesor
		/database
			ipsum_reportes.sql (Definición DDL normalizada en 3FN)
	    /docs
            Regament de relevamiento de software.md
            /persistencia (Diagramas MER, DER y normalización)

		
    • Convenciones de nomenclartura universal:
        1. Archivos y carpetas: Se utilizará estrictamente kebab-case (Letras minúsculas separadas por guiones). Queda prohibido el uso de mayúsculas, espacios o caracteres especiales. Ejemplo correcto: home-pages.css
        2. Variables y funciones (JavaScript): Se utilizará camelCase. Ejemplo correcto: footPageData().
        3. Clases e instancias (JavaScript): Se utilizará PascalCase.  Ejemplo correcto: SubmitButton.
        4. Constantes (JavaScript): Se utilizará UPPER_SNAKE_CASE. Ejemplo correcto: MAX_LOGIN_ATTEMPTS.
    • Etsándares de codificación HTML 5:
        1. Semántica y estructura:
Queda estrictamente prohibido el uso excesivo e injustificado de etiquetas <div>. Todo documento debe estructurarse con elementos semánticos nativos:
            ▪ <header> para secciones introductorias o barras de navegación.
            ▪ <nav> exclusivamente para bloques de enlaces de navegación interna o externa.
            ▪ <main> para el contenido principal y único del documento (solo uno por página).
            ▪ <section> para agrupar temáticas genéricas que requieran lógicamente un encabezado.
            ▪ <article> para componentes independientes y autocontenidos (tarjetas de productos, publicaciones, etc.).
            ▪ <footer> para el pie de página de aplicacíon o sección.
        2. Atributos críticos y accesibilidad:
            ▪ La etiqueta raíz debe declarar el idioma del sitio obligatoriamente: <html lang=”es”>.
            ▪ Toda etiqueta <img> debe poseer de forma obligatoria el atributo alt descriptivo. Si la imagen es puramente decorativa, el atributo debe declararse vacío: alt=””.
            ▪ Los elementos interactivos creados artificialmente deben incluir atributos aria-* y propiedades de foco apropiadas.
    • Estándares de codificación CSS y diseño
        1. Para evitar conflictos y con funciones, los estilos, Ipsum adopta la metodología BEM para el nombramiento de clases CSS:
            ▪ /* Bloque: Componente autónomo */
            ▪ .card {}
            ▪ /* Elemento: Parte del bloque que no tiene significado aislado */
            ▪ .card__title {}
            ▪ .card__description {}
            ▪ /* Modificador: Flag que cambia la apariencia o comportamiento */
            ▪ .card--featured {}
            ▪ .card__title--accent {}
        2. Reglas de maquetación y unidades de medida:
            ▪ Tipografía y Espaciados: Se utilizará la unidad relativa rem para tamaños de fuente,
paddings y margins, garantizando la correcta accesibilidad al cambiar las preferencias del
navegador.
            ▪ Dimensiones de Estructuras: Se permite el uso de px o % únicamente para bordes,
layouts específicos o contenedores máximos.
            ▪ Contenedores: Cada sección lógica del sitio debe envolverse en una clase de control de
layout (ej: .container) para asegurar alineación y márgenes consistentes en pantallas de
cualquier tamaño.
    • Modelo de Arquitectura y Estándares de Backend (PHP):
        1. Patrón Arquitectónico (MVC + Front Controller):
            ▪ Front Controller: Todo el flujo de navegación se centraliza a través de public/index.php mediante el parámetro ?ruta=...
            ▪ Desacoplamiento en capas:
                - Modelos (/app/modelo/): Clases con POO que encapsulan la persistencia y consultas SQL mediante PDO.
                - Controladores (/app/controlador/): Controladores de acción única orientados al principio de responsabilidad única (SRP).
                - Vistas (/app/vista/): Plantillas enfocadas exclusivamente en la presentación e interfaz de usuario, organizadas por módulos de rol.
            ▪ Aislamiento del Document Root: La carpeta /public/ es el único punto de acceso web; los archivos de configuración y lógica residen fuera de la raíz pública por seguridad.
        2. Estándares de Codificación PHP:
            ▪ Modo Estricto: Todos los scripts PHP deben iniciar obligatoriamente con declare(strict_types=1);.
            ▪ Tipado Estricto: Todas las funciones, métodos y propiedades deben declarar tipos de datos de entrada y retorno.
            ▪ Seguridad contra Inyecciones (SQLi): Prohibida la concatenación de variables en consultas SQL; uso obligatorio de sentencias preparadas con PDO y parámetros enlazados.
            ▪ Protección contra XSS y CSRF: Toda salida de datos dinámicos en vistas debe ser sanitizada con la función escapar(), y todo formulario POST debe incorporar un token CSRF verificado.
            ▪ Control de Acceso: Las rutas protegidas deben invocar la verificación de sesión y rol (RBAC) previo a cualquier procesamiento.
    • Estándares de Persistencia y Normalización de Base de Datos (3FN):
        1. Tercera Forma Normal (3FN) Obligatoria:
            ▪ Eliminación de Dependencias Parciales (2FN): Todos los atributos no clave deben depender de la clave primaria completa.
            ▪ Eliminación de Dependencias Transitivas (3FN): Queda estrictamente prohibido almacenar datos descriptivos dependientes de claves foráneas en tablas transaccionales (ej: nombres de usuarios o descripciones de salas dentro de reportes de incidencias o partes diarios). Estos datos deben resolverse dinámicamente mediante consultas con INNER JOIN hacia sus entidades maestras.
            ▪ Integridad Referencial: Toda referencia a entidades maestras (como la tabla aulas) debe estar respaldada formalmente por una clave foránea (FOREIGN KEY) con acciones ON DELETE y ON UPDATE debidamente tipadas en el motor transaccional InnoDB.