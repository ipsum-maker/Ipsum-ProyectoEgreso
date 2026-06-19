document.addEventListener("DOMContentLoaded", function () {
    // Clave que se usa para guardar y leer los reportes en el almacenamiento del navegador (localStorage)
    const STORAGE_KEY = "reportes";

    // Diccionario de traducción para que los códigos de Aula se vean bonitos y legibles
    const aulasLegibles = {
        "taller1": "Taller 1",
        "taller2": "Taller 2",
        "taller3": "Taller 3",
        "taller4": "Taller 4",
        "taller5": "Taller 5",
        "lab1": "Laboratorio 1",
        "lab2": "Laboratorio 2",
        "lab3": "Laboratorio 3"
    };

    // Diccionario de traducción para que los tipos de incidencia se lean correctamente
    const incidenciasLegibles = {
        "rotura-equipo": "Rotura de equipo",
        "falla-equipo": "Falla de equipo",
        "otro": "Otro"
    };

    // Variable para saber cuál es el reporte que estamos editando actualmente
    let reporteIdBajoEdicion = null;

    // --- ELEMENTOS DE LA PÁGINA (HTML) QUE SE VA A CONTROLAR ---
    const contenedorReportes = document.getElementById("contenedor-reportes");
    const mensajeVacio = document.getElementById("mensaje-vacio");

    // Botones de filtros
    const btnFiltroTodos = document.getElementById("btn-filtro-todos");
    const btnFiltroDiarios = document.getElementById("btn-filtro-diarios");
    const btnFiltroIncidencias = document.getElementById("btn-filtro-incidencias");

    // Elementos donde se muestran las cantidades numéricas de los filtros (Todos (X), etc)
    const cantTodos = document.getElementById("cantidad-todos");
    const cantDiarios = document.getElementById("cantidad-diarios");
    const cantIncidencias = document.getElementById("cantidad-incidencias");

    // Modales (ventanas emergentes)
    const modalDiario = document.getElementById("modal-diario");
    const modalIncidencia = document.getElementById("modal-incidencia");

    // Formulario de edición diario y sus campos
    const formDiario = document.getElementById("form-editar-diario");
    const editDiarioFecha = document.getElementById("edit-diario-fecha");
    const editDiarioNombre = document.getElementById("edit-diario-nombre");
    const editDiarioApellido = document.getElementById("edit-diario-apellido");
    const editDiarioAula = document.getElementById("edit-diario-aula");
    const editDiarioListaPcs = document.getElementById("edit-diario-lista-pcs");
    const editDiarioBtnAgregarPc = document.getElementById("edit-diario-agregar-pc");

    // Formulario de edición de incidencia y sus campos
    const formIncidencia = document.getElementById("form-editar-incidencia");
    const editIncidenciaNombre = document.getElementById("edit-incidencia-nombre");
    const editIncidenciaApellido = document.getElementById("edit-incidencia-apellido");
    const editIncidenciaAsignatura = document.getElementById("edit-incidencia-asignatura");
    const editIncidenciaFecha = document.getElementById("edit-incidencia-fecha");
    const editIncidenciaSolicitud = document.getElementById("edit-incidencia-solicitud");
    const editIncidenciaTaller = document.getElementById("edit-incidencia-taller");
    const editIncidenciaDescripcion = document.getElementById("edit-incidencia-descripcion");

    // Variable para controlar cuál filtro está seleccionado actualmente ("todos", "reporte-diario", "reporte-incidencia")
    let filtroActivo = "todos";

    // ==========================================================================
    // =  FUNCIONES ÚTILES                                                         =
    // ==========================================================================

    // Traduce un código de Aula (ej: "lab1") a un texto legible (ej: "Laboratorio 1")
    function obtenerAulaLegible(codigo) {
        return aulasLegibles[codigo] || codigo || "";
    }

    // Traduce un código de tipo de incidencia (ej: "falla-equipo") a un texto legible (ej: "Falla de equipo")
    function obtenerIncidenciaLegible(codigo) {
        return incidenciasLegibles[codigo] || codigo || "";
    }

    // Convierte una fecha de formato año-mes-día (ej: "2026-06-18") a texto amigable (ej: "18 de junio de 2026")
    function formatearFecha(fechaStr) {
        if (!fechaStr) return "";
        const partes = fechaStr.split("-");
        if (partes.length !== 3) return fechaStr;

        const anio = partes[0];
        // En Javascript los meses van del 0 al 11, por eso restamos 1
        const mesNro = parseInt(partes[1], 10) - 1;
        const dia = parseInt(partes[2], 10);

        const nombresMeses = [
            "enero", "febrero", "marzo", "abril", "mayo", "junio",
            "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
        ];

        return `${dia} de ${nombresMeses[mesNro]} de ${anio}`;
    }

    // Muestra un cartelito flotante en pantalla de color verde (éxito) o rojo (eliminado/error)
    function mostrarAlertaNotificacion(mensaje, tipo = "exito") {
        const contenedor = document.getElementById("alertas-contenedor");
        if (!contenedor) return;

        // Creamos la alerta en el HTML
        const alerta = document.createElement("article");
        alerta.className = `alerta-notificacion alerta-notificacion--${tipo}`;
        alerta.setAttribute("role", "alert");
        alerta.innerHTML = `<p class="alerta-notificacion__mensaje">${mensaje}</p>`;

        // La metemos en la caja de notificaciones
        contenedor.appendChild(alerta);

        // Desvanecer y borrar la notificación solita tras 3.5 segundos
        setTimeout(() => {
            alerta.style.opacity = "0";
            alerta.style.transition = "opacity 0.3s ease";
            setTimeout(() => {
                if (alerta.parentNode === contenedor) {
                    contenedor.removeChild(alerta);
                }
            }, 300);
        }, 3500);
    }

    // ==========================================================================
    // =  LEER Y ACTUALIZAR REPORTES DESDE LOCALSTORAGE                         =
    // ==========================================================================

    // Lee los reportes guardados en el navegador. Si no hay ninguno, devuelve una lista vacía.
    function obtenerReportes() {
        const datos = localStorage.getItem(STORAGE_KEY);
        if (datos) {
            try {
                let lista = JSON.parse(datos);
                // Si algún reporte viejo no tiene un identificador único (ID), se lo creamos en el momento
                let huboCambios = false;
                lista = lista.map(reporte => {
                    if (!reporte.id) {
                        reporte.id = "rep_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
                        huboCambios = true;
                    }
                    // Si es incidencia y no tiene estado, por defecto empieza en "Pendiente"
                    if (reporte.tipo === "reporte-incidencia" && !reporte.estado) {
                        reporte.estado = "Pendiente";
                        huboCambios = true;
                    }
                    return reporte;
                });

                // Si creamos IDs nuevos, actualizamos la base de datos
                if (huboCambios) {
                    guardarReportesEnStorage(lista);
                }
                return lista;
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    // Guarda la lista de reportes actualizada de vuelta en el navegador
    function guardarReportesEnStorage(lista) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(lista));
    }

    // ==========================================================================
    // =  RENDERIZAR O MOSTRAR LOS REPORTES EN PANTALLA                         =
    // ==========================================================================

    // Dibuja en pantalla los reportes correspondientes según el filtro activo
    function mostrarReportesEnPantalla() {
        const todosLosReportes = obtenerReportes();

        // Limpiar las tarjetas viejas que están en pantalla (conservando el mensaje vacío)
        const tarjetasViejas = contenedorReportes.querySelectorAll(".reporte-tarjeta");
        tarjetasViejas.forEach(tarjeta => contenedorReportes.removeChild(tarjeta));

        // Filtrar los reportes que queremos mostrar
        const reportesFiltrados = todosLosReportes.filter(reporte => {
            if (filtroActivo === "todos") return true;
            return reporte.tipo === filtroActivo;
        });

        // Si no hay reportes tras el filtro, mostramos el cartel de "no hay reportes registrados aún"
        if (reportesFiltrados.length === 0) {
            mensajeVacio.style.display = "block";
        } else {
            mensajeVacio.style.display = "none";

            // Recorremos los reportes y creamos una tarjeta para cada uno
            reportesFiltrados.forEach(reporte => {
                const tarjeta = document.createElement("article");

                if (reporte.tipo === "reporte-diario") {
                    tarjeta.className = "reporte-tarjeta reporte-tarjeta--diario";

                    // Contamos las PCs que tiene registradas este reporte diario
                    const cantidadPcs = (reporte.pcs && reporte.pcs.length) || 0;

                    // Nombre completo del docente
                    const nombreCompleto = `${reporte.nombreProfesor} ${reporte.apellidoProfesor}`;
                    const aulaLimpia = obtenerAulaLegible(reporte.aula);
                    const fechaLimpia = formatearFecha(reporte.fecha);

                    tarjeta.innerHTML = `
                        <header class="reporte-tarjeta__header">
                            <h2 class="reporte-tarjeta__titulo">${nombreCompleto}</h2>
                            <div class="reporte-tarjeta__badges">
                                <span class="reporte-tarjeta__badge reporte-tarjeta__badge--diario">Diario</span>
                            </div>
                        </header>
                        <section class="reporte-tarjeta__detalles">
                            <p class="reporte-tarjeta__info reporte-tarjeta__info-fecha">
                                <strong>${fechaLimpia}</strong> &bull; ${aulaLimpia}
                            </p>
                            <p class="reporte-tarjeta__detalle-pc">${cantidadPcs} equipo(s) registrado(s)</p>
                        </section>
                        <footer class="reporte-tarjeta__acciones">
                            <button class="reporte-tarjeta__boton reporte-tarjeta__boton--editar" data-id="${reporte.id}" type="button">Editar</button>
                            <button class="reporte-tarjeta__boton reporte-tarjeta__boton--eliminar" data-id="${reporte.id}" type="button">Eliminar</button>
                        </footer>
                    `;
                } else {
                    tarjeta.className = "reporte-tarjeta reporte-tarjeta--incidencia";

                    const nombreCompleto = `${reporte.nombreProfesor} ${reporte.apellidoProfesor}`;
                    const tallerLimpio = obtenerAulaLegible(reporte.taller);
                    const solicitudLimpia = obtenerIncidenciaLegible(reporte.tipoSolicitud);
                    const fechaLimpia = formatearFecha(reporte.fecha);
                    // Estado manejado por coordinador (Pendiente, En proceso, Resuelto)
                    const estadoReporte = reporte.estado || "Pendiente";

                    tarjeta.innerHTML = `
                        <header class="reporte-tarjeta__header">
                            <h2 class="reporte-tarjeta__titulo">${nombreCompleto}</h2>
                            <div class="reporte-tarjeta__badges">
                                <span class="reporte-tarjeta__badge reporte-tarjeta__badge--estado">${estadoReporte}</span>
                                <span class="reporte-tarjeta__badge reporte-tarjeta__badge--incidencia">Incidencia</span>
                            </div>
                        </header>
                        <section class="reporte-tarjeta__detalles">
                            <p class="reporte-tarjeta__info reporte-tarjeta__info-fecha">
                                <strong>${fechaLimpia}</strong> &bull; ${solicitudLimpia} &bull; ${tallerLimpio}
                            </p>
                        </section>
                        <footer class="reporte-tarjeta__acciones">
                            <button class="reporte-tarjeta__boton reporte-tarjeta__boton--editar" data-id="${reporte.id}" type="button">Editar</button>
                            <button class="reporte-tarjeta__boton reporte-tarjeta__boton--eliminar" data-id="${reporte.id}" type="button">Eliminar</button>
                        </footer>
                    `;
                }

                // Agregamos la tarjeta al contenedor visual
                contenedorReportes.appendChild(tarjeta);
            });
        }

        // Volver a calcular los números de los filtros de arriba
        actualizarContadoresFiltros(todosLosReportes);
    }

    // Calcula cuántos reportes hay en total, cuántos diarios y cuántas incidencias, y actualiza los numeritos arriba
    function actualizarContadoresFiltros(lista) {
        const total = lista.length;
        const diarios = lista.filter(r => r.tipo === "reporte-diario").length;
        const incidencias = lista.filter(r => r.tipo === "reporte-incidencia").length;

        cantTodos.textContent = `(${total})`;
        cantDiarios.textContent = `(${diarios})`;
        cantIncidencias.textContent = `(${incidencias})`;
    }

    // ==========================================================================
    // =  FILTRAR REPORTES AL HACER CLIC EN LOS BOTONES                         =
    // ==========================================================================

    // Cambia el filtro activo de la página al que elijas
    function aplicarFiltro(nuevoFiltro, botonActivo) {
        filtroActivo = nuevoFiltro;

        // Desactivamos la clase visual de todos los botones de filtro
        btnFiltroTodos.classList.remove("historial-filters__button--active");
        btnFiltroDiarios.classList.remove("historial-filters__button--active");
        btnFiltroIncidencias.classList.remove("historial-filters__button--active");

        // Se la agregamos únicamente al botón que presionó el usuario
        botonActivo.classList.add("historial-filters__button--active");

        // Refrescamos las tarjetas mostradas
        mostrarReportesEnPantalla();
    }

    btnFiltroTodos.addEventListener("click", () => aplicarFiltro("todos", btnFiltroTodos));
    btnFiltroDiarios.addEventListener("click", () => aplicarFiltro("reporte-diario", btnFiltroDiarios));
    btnFiltroIncidencias.addEventListener("click", () => aplicarFiltro("reporte-incidencia", btnFiltroIncidencias));


    // ==========================================================================
    // =  ELIMINAR UN REPORTE                                                   =
    // ==========================================================================

    // Captura los clics en los botones de "Eliminar" de las tarjetas
    contenedorReportes.addEventListener("click", function (event) {
        // Buscamos si el elemento clickeado es un botón de eliminar
        const botonEliminar = event.target.closest(".reporte-tarjeta__boton--eliminar");
        if (!botonEliminar) return;

        const idReporte = botonEliminar.dataset.id;

        // Cuadro de confirmación nativo y simple
        const seguro = confirm("¿Estás seguro de que deseas eliminar este reporte permanentemente?");
        if (seguro) {
            let reportes = obtenerReportes();
            // Filtramos la lista para quitar el reporte seleccionado
            reportes = reportes.filter(r => r.id !== idReporte);

            // Guardamos la lista nueva y refrescamos la pantalla
            guardarReportesEnStorage(reportes);
            mostrarReportesEnPantalla();

            // Lanzamos la alerta roja
            mostrarAlertaNotificacion("Se ha eliminado el reporte.", "error");
        }
    });

    // ==========================================================================
    // = INTERACTIVIDAD DE AGREGAR/QUITAR PCS EN LA MODAL DE EDICIÓN DIARIA    =
    // ==========================================================================

    // Dibuja las computadoras asociadas en la lista del formulario de edición diario
    function renderizarPcsEnEdicion(listaPcs) {
        editDiarioListaPcs.innerHTML = "";
        listaPcs.forEach((item, index) => {
            const numeroPc = index + 1;
            const fila = document.createElement("section");
            fila.className = "assignment__card";
            fila.innerHTML = `
                <label class="assignment__pc" for="edit-pc-${numeroPc}">PC${numeroPc}</label>
                <input class="assignment__input pc-nombre" type="text" id="edit-pc-${numeroPc}" value="${item.pc}" required>
                <label class="assignment__label" for="edit-alumno-${numeroPc}">Nombre del Alumno</label>
                <input class="assignment__input pc-alumno" type="text" id="edit-alumno-${numeroPc}" value="${item.alumno}" placeholder="Nombre del alumno" required>
                <button class="assignment__delete" type="button" aria-label="Eliminar PC">&times;</button>
            `;
            editDiarioListaPcs.appendChild(fila);

            // Funcionalidad al botoncito de la cruz (X) para remover esta fila
            fila.querySelector(".assignment__delete").addEventListener("click", function () {
                editDiarioListaPcs.removeChild(fila);
                actualizarNumerosDeLasPcs();
            });
        });
    }

    // Renombra de manera consecutiva (PC1, PC2, PC3...) las computadoras si se elimina alguna
    function actualizarNumbersDeLasPcs() {
        const filas = editDiarioListaPcs.querySelectorAll(".assignment__card");
        filas.forEach((fila, index) => {
            const numeroPc = index + 1;
            const labelPc = fila.querySelector(".assignment__pc");
            const inputPc = fila.querySelector(".pc-nombre");
            const labelAlumno = fila.querySelector(".assignment__label");
            const inputAlumno = fila.querySelector(".pc-alumno");

            if (labelPc) {
                labelPc.textContent = `PC${numeroPc}`;
                labelPc.setAttribute("for", `edit-pc-${numeroPc}`);
            }
            if (inputPc) {
                inputPc.id = `edit-pc-${numeroPc}`;
            }
            if (labelAlumno) {
                labelAlumno.setAttribute("for", `edit-alumno-${numeroPc}`);
            }
            if (inputAlumno) {
                inputAlumno.id = `edit-alumno-${numeroPc}`;
            }
        });
    }

    // Botón para agregar una nueva computadora en el formulario de la modal diario
    editDiarioBtnAgregarPc.addEventListener("click", function () {
        const numeroSiguiente = editDiarioListaPcs.children.length + 1;
        const fila = document.createElement("section");
        fila.className = "assignment__card";
        fila.innerHTML = `
            <label class="assignment__pc" for="edit-pc-${numeroSiguiente}">PC${numeroSiguiente}</label>
            <input class="assignment__input pc-nombre" type="text" id="edit-pc-${numeroSiguiente}" value="PC${numeroSiguiente}" required>
            <label class="assignment__label" for="edit-alumno-${numeroSiguiente}">Nombre del Alumno</label>
            <input class="assignment__input pc-alumno" type="text" id="edit-alumno-${numeroSiguiente}" placeholder="Nombre del alumno" required>
            <button class="assignment__delete" type="button" aria-label="Eliminar PC">&times;</button>
        `;
        editDiarioListaPcs.appendChild(fila);

        fila.querySelector(".assignment__delete").addEventListener("click", function () {
            editDiarioListaPcs.removeChild(fila);
            actualizarNumbersDeLasPcs();
        });
    });

    // ==========================================================================
    // =  EDICIÓN DE UN REPORTE (ABRIR LA MODAL CORRESPONDIENTE)               =
    // ==========================================================================

    // Captura los clics en los botones "Editar" de las tarjetas
    contenedorReportes.addEventListener("click", function (event) {
        const botonEditar = event.target.closest(".reporte-tarjeta__boton--editar");
        if (!botonEditar) return;

        const idReporte = botonEditar.dataset.id;
        const reportes = obtenerReportes();
        const reporte = reportes.find(r => r.id === idReporte);

        if (!reporte) return;

        // Guardamos el ID del reporte que se está editando
        reporteIdBajoEdicion = idReporte;

        if (reporte.tipo === "reporte-diario") {
            // Rellenamos los campos de la modal diario con los datos que ya tenía guardados
            editDiarioFecha.value = reporte.fecha;
            editDiarioNombre.value = reporte.nombreProfesor;
            editDiarioApellido.value = reporte.apellidoProfesor;
            editDiarioAula.value = reporte.aula;

            // Dibujamos las computadoras del reporte en la modal
            renderizarPcsEnEdicion(reporte.pcs || []);

            // Abrimos la modal usando la función nativa showModal() de HTML5
            modalDiario.showModal();
        } else {
            // Rellenamos los campos de la modal de incidencias
            editIncidenciaNombre.value = reporte.nombreProfesor;
            editIncidenciaApellido.value = reporte.apellidoProfesor;
            editIncidenciaAsignatura.value = reporte.asignatura;
            editIncidenciaFecha.value = reporte.fecha;
            editIncidenciaSolicitud.value = reporte.tipoSolicitud;
            editIncidenciaTaller.value = reporte.taller;
            editIncidenciaDescripcion.value = reporte.descripcion;

            // Abrimos la modal de incidencias
            modalIncidencia.showModal();
        }
    });

    // ==========================================================================
    // =  CERRAR LAS MODALES (BOTONES CANCELAR Y X DE LA ESQUINA)             =    
    // ==========================================================================

    // Cerrar modal diario
    document.getElementById("btn-cerrar-diario").addEventListener("click", () => modalDiario.close());
    document.getElementById("btn-cancelar-diario").addEventListener("click", () => modalDiario.close());

    // Cerrar modal incidencia
    document.getElementById("btn-cerrar-incidencia").addEventListener("click", () => modalIncidencia.close());
    document.getElementById("btn-cancelar-incidencia").addEventListener("click", () => modalIncidencia.close());

    // ==========================================================================
    // =  GUARDAR CAMBIOS (ENVIAR LOS FORMULARIOS DE LAS MODALES)              =    
    // ==========================================================================

    // Al guardar la edición del Reporte Diario
    formDiario.addEventListener("submit", function (event) {
        event.preventDefault(); // Evitamos que la página se reinicie

        const reportes = obtenerReportes();
        const indice = reportes.findIndex(r => r.id === reporteIdBajoEdicion);

        if (indice === -1) return;

        // Recogemos la lista de PCs asignadas que editó el profesor
        const pcsEditadas = [];
        const bloquesPc = editDiarioListaPcs.querySelectorAll(".assignment__card");

        bloquesPc.forEach(bloque => {
            const pcVal = bloque.querySelector(".pc-nombre").value;
            const alumnoVal = bloque.querySelector(".pc-alumno").value;
            pcsEditadas.push({
                pc: pcVal,
                alumno: alumnoVal
            });
        });

        // Validamos que se asigne al menos una PC
        if (pcsEditadas.length === 0) {
            alert("Debe asignar al menos una PC en el reporte");
            return;
        }

        // Actualizamos los datos del reporte en nuestro array
        reportes[indice].nombreProfesor = editDiarioNombre.value.trim();
        reportes[indice].apellidoProfesor = editDiarioApellido.value.trim();
        reportes[indice].aula = editDiarioAula.value;
        reportes[indice].pcs = pcsEditadas;

        // Guardamos todo y cerramos la ventana emergente
        guardarReportesEnStorage(reportes);
        modalDiario.close();
        mostrarReportesEnPantalla();

        // Lanzamos la notificación verde
        mostrarAlertaNotificacion("Reporte editado con éxito.", "exito");
    });

    // Al guardar la edición del Reporte de Incidencias
    formIncidencia.addEventListener("submit", function (event) {
        event.preventDefault(); // Evitamos que la página se reinicie

        const reportes = obtenerReportes();
        const indice = reportes.findIndex(r => r.id === reporteIdBajoEdicion);

        if (indice === -1) return;

        // Actualizamos todos los datos de la incidencia en nuestro array
        reportes[indice].nombreProfesor = editIncidenciaNombre.value.trim();
        reportes[indice].apellidoProfesor = editIncidenciaApellido.value.trim();
        reportes[indice].asignatura = editIncidenciaAsignatura.value.trim();
        reportes[indice].tipoSolicitud = editIncidenciaSolicitud.value;
        reportes[indice].taller = editIncidenciaTaller.value;
        reportes[indice].descripcion = editIncidenciaDescripcion.value.trim();

        // Guardamos todo y cerramos la ventana emergente
        guardarReportesEnStorage(reportes);
        modalIncidencia.close();
        mostrarReportesEnPantalla();

        // Lanzamos la notificación verde
        mostrarAlertaNotificacion("Reporte editado con éxito.", "exito");
    });

    // ==========================================================================
    // =  ARRANQUE INICIAL AL CARGAR LA PÁGINA                                  =    
    // ==========================================================================

    // Mostramos los reportes cargados en pantalla apenas se abre la página
    mostrarReportesEnPantalla();
});
