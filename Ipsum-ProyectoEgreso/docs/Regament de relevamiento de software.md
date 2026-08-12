Reglamento de organización de software y Estándares de Codificación de Ipsum:

    • Introducción y Alcance:
	El presente documento establece las normas, convencionales y buenas prácticas obligatorias para el desarrollo de proyectos web en Ipsum. Para garantizar un ecosistema de equipo técnico.
	Este reglamento aplica de forma estricta a todos los archivos HTML, CSS y JavaScript. 
    • Estructura del proyecto y arquitectura de carpetas:
    El proyecto se organiza en carpetas según el tipo de archivo con el fin de mantener el orden, facilitar el mantenimiento y permitir la reutilización del código.
	Estructura de carpetas:
	/proyecto
		/assets
			/images
			/code
                /css
                    ligin.css
                    panel-profesores.css
                    reporte-diario.css
                /html
                    login.html
                    panel-profesores.html
                    reporte-dirio.html
                    reporte-incidencia.html
                /js
                    LOGIN.js
                    PANEL_PROFESORES.js
                    REPORTE_DIARIO.js
                /php	
	    /docs
            Regamento de relevamiento de software.md

		
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