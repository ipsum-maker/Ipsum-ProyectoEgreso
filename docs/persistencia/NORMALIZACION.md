# Proceso de Normalización de Base de Datos (hasta 3FN)

**Proyecto:** Ipsum - Sistema de Gestión de Reportes e Incidencias  
**Ubicación:** `docs/persistencia/NORMALIZACION.md`  
**Fidelidad:** 100% fiel al sistema `Ipsum-ProyectoEgreso` y a la estructura de la base de datos `ipsum_reportes`.

---

## 1. Introducción y Objetivos de la Normalización

La **normalización** es una técnica formal de diseño de bases de datos relacionales cuyo propósito es organizar las tablas de manera que se minimice la **redundancia de datos** y se eliminen las **anomalías de actualización, inserción y borrado**.

En el sistema **Ipsum-ProyectoEgreso**, la normalización garantiza que:
1. No se duplique la información de los usuarios (profesores/coordinadores) cada vez que llenan un reporte.
2. La lista dinámica de computadoras asignadas a alumnos en un grupo no genere filas nulas ni campos multivaluados.
3. El catálogo de aulas y laboratorios se mantenga centralizado e independiente de los partes de clase e incidencias.

---

## 2. Forma No Normalizada (0FN / UNF - Unnormalized Form)

En la **0FN**, toda la información recopilada por el sistema se concibe como una única estructura de datos plana proveniente de la captura directa de formularios (como el reporte diario o el reporte de incidencias).

### 2.1. Tabla Plana No Normalizada (0FN)

| id_registro | cedula_profesor | nombre_profesor | rol | contrasena | fecha | hora | aula | pcs_alumnos (Lista Repetitiva) | asignatura | tipo_incidencia | descr_incidencia |
| :---: | :---: | :--- | :---: | :--- | :---: | :---: | :---: | :--- | :--- | :--- | :--- |
| 1 | 12345678 | Elvis | admin | $2y$12$... | 2026-08-18 | 08:00 | Taller 1 | `[PC1: Juan, PC2: Maria, PC3: Pedro]` | Base de Datos | - | - |
| 2 | 23456789 | Pepita | coordinador | $2y$12$... | 2026-08-18 | 10:30 | Lab 1 | - | Redes | falla-equipo | PC3 no enciende pantalla |
| 3 | 34567890 | Pepito | profesor | $2y$12$... | 2026-08-18 | 14:00 | Taller 1 | `[PC1: Ana, PC2: Carlos]` | - | - | - |

### 2.2. Anomalías Presentes en 0FN
* **Grupo Repetitivo / Datos No Atómicos:** El campo `pcs_alumnos` contiene una colección de pares (equipo, alumno) dentro de una sola celda.
* **Redundancia Masiva:** Cada vez que el profesor Elvis realiza un reporte, se vuelven a escribir su nombre, contraseña hash y rol.
* **Anomalía de Modificación:** Si el profesor cambia de nombre o rol, habría que actualizar múltiples registros en la base de datos.
* **Anomalía de Inserción:** No se puede dar de alta un aula o un usuario nuevo en el sistema a menos que tenga al menos un reporte registrado.
* **Anomalía de Borrado:** Si se elimina un reporte diario, se pierde la información del usuario y del aula si era la única clase registrada.

---

## 3. Primera Forma Normal (1FN)

### 3.1. Reglas de la 1FN
1. **Atomicidad:** Cada campo debe contener un único valor indivisible (no arreglos, listas ni colecciones).
2. **Ausencia de grupos repetitivos:** Los conjuntos de datos repetidos deben ser extraídos a filas individuales o tablas secundarias.
3. **Clave Primaria:** Cada fila debe ser identificada unívocamente mediante una clave primaria.

### 3.2. Transformación a 1FN
Se aplanan los datos multivaluados de `pcs_alumnos`, descomponiéndolos en filas individuales compuestas por `numero_pc` y `nombre_alumno`.

#### Tabla Aplanada 1FN (`REPORTE_PLANO_1FN`)
* **Clave Primaria Compuesta:** (`id_reporte`, `numero_pc`)

| id_reporte (PK) | numero_pc (PK) | cedula_prof | nombre_prof | rol | fecha | hora | aula | nombre_alumno | asignatura | tipo_incidencia | descr_incidencia |
| :---: | :---: | :---: | :--- | :--- | :---: | :---: | :---: | :--- | :--- | :--- | :--- |
| 1 | PC1 | 12345678 | Elvis | admin | 2026-08-18 | 08:00 | Taller 1 | Juan | Base de Datos | NULL | NULL |
| 1 | PC2 | 12345678 | Elvis | admin | 2026-08-18 | 08:00 | Taller 1 | Maria | Base de Datos | NULL | NULL |
| 1 | PC3 | 12345678 | Elvis | admin | 2026-08-18 | 08:00 | Taller 1 | Pedro | Base de Datos | NULL | NULL |
| 2 | PC3 | 23456789 | Pepita | coord | 2026-08-18 | 10:30 | Lab 1 | NULL | Redes | falla-equipo | PC3 no enciende |
| 3 | PC1 | 34567890 | Pepito | profe | 2026-08-18 | 14:00 | Taller 1 | Ana | NULL | NULL | NULL |
| 3 | PC2 | 34567890 | Pepito | profe | 2026-08-18 | 14:00 | Taller 1 | Carlos | NULL | NULL | NULL |

### 3.3. Diagnóstico tras la 1FN
Se resolvió la atomicidad, pero surgieron problemas de dependencias parciales: los atributos del profesor (`nombre_prof`, `rol`) dependen únicamente de `cedula_prof`, no de la clave compuesta (`id_reporte`, `numero_pc`).

---

## 4. Segunda Forma Normal (2FN)

### 4.1. Reglas de la 2FN
1. La tabla debe encontrarse en **1FN**.
2. **Dependencia Funcional Completa:** Todo atributo no clave debe depender de la **totalidad** de la Clave Primaria y no de una parte de ella (se eliminan dependencias parciales).

### 4.2. Análisis de Dependencias Funcionales en 1FN
1. `cedula_prof` $\rightarrow$ `nombre_prof`, `contrasena_hash`, `rol`, `activo` *(Dependencia independiente del reporte)*.
2. `id_reporte` $\rightarrow$ `cedula_prof`, `fecha`, `hora`, `aula` *(Dependencia de la cabecera del reporte)*.
3. (`id_reporte`, `numero_pc`) $\rightarrow$ `nombre_alumno` *(Dependencia completa del detalle)*.
4. `id_incidencia` $\rightarrow$ `cedula_prof`, `aula`, `asignatura`, `fecha_incidencia`, `hora_apertura`, `tipo_solicitud`, `descripcion`, `estado` *(Dependencia de la incidencia)*.

### 4.3. Transformación a 2FN (Separación de Entidades)

Al eliminar las dependencias parciales, la tabla 1FN se descompone en 4 entidades distintas:

#### 1. Tabla `USUARIOS` (2FN)
* **PK:** `id` (o `cedula`)

$$\text{USUARIOS}(\underline{\text{id}}, \text{cedula}, \text{nombre}, \text{contrasena\_hash}, \text{rol}, \text{activo}, \text{es\_inicial})$$

#### 2. Tabla `REPORTES_DIARIOS` (2FN)
* **PK:** `id`
* **FK:** `usuario_id` $\rightarrow$ `USUARIOS(id)`

$$\text{REPORTES\_DIARIOS}(\underline{\text{id}}, \text{usuario\_id}, \text{aula}, \text{fecha}, \text{hora})$$

#### 3. Tabla `ASIGNACIONES_EQUIPOS` (2FN)
* **PK:** `id`
* **FK:** `reporte_diario_id` $\rightarrow$ `REPORTES_DIARIOS(id)`

$$\text{ASIGNACIONES\_EQUIPOS}(\underline{\text{id}}, \text{reporte\_diario\_id}, \text{numero\_pc}, \text{nombre\_alumno})$$

#### 4. Tabla `REPORTES_INCIDENCIAS` (2FN)
* **PK:** `id`
* **FK:** `usuario_id` $\rightarrow$ `USUARIOS(id)`

$$\text{REPORTES\_INCIDENCIAS}(\underline{\text{id}}, \text{usuario\_id}, \text{aula}, \text{asignatura}, \text{fecha\_incidencia}, \text{hora\_apertura}, \text{tipo\_solicitud}, \text{otro\_tipo}, \text{descripcion}, \text{estado})$$

---

## 5. Tercera Forma Normal (3FN)

### 5.1. Reglas de la 3FN
1. La estructura debe encontrarse en **2FN**.
2. **Sin Dependencias Transitivas:** Ningún atributo no clave debe depender de otro atributo no clave. En términos formales, para toda dependencia $X \rightarrow Y$, $X$ debe ser una superclave o $Y$ debe ser parte de una clave.

### 5.2. Análisis de Dependencias Transitivas
Al revisar la estructura obtenida en 2FN, observamos la presencia del atributo `aula` en texto plano tanto en `REPORTES_DIARIOS` como en `REPORTES_INCIDENCIAS`.

En el dominio del sistema:
$$\text{id\_reporte} \rightarrow \text{codigo\_aula} \rightarrow (\text{nombre\_aula}, \text{tipo\_aula})$$

Existe una **dependencia transitiva**: `nombre_aula` y `tipo_aula` no dependen de la clave de la incidencia o reporte diario, sino del código o identificador del aula. Si el nombre del "Taller 1" cambia, o si se desea validar la existencia de un aula, el texto duplicado viola la 3FN.

### 5.3. Transformación a 3FN (Extracción del Catálogo `AULAS_LABORATORIOS`)

Para eliminar la dependencia transitiva, creamos la tabla catálogo `AULAS_LABORATORIOS` y sustituimos el nombre/código directo en los reportes por su respectiva clave foránea (`aula_id`).

---

### 5.4. Esquema Final Normalizado en 3FN

#### 1. Tabla `usuarios`
$$\text{usuarios}(\underline{\text{id}}, \text{nombre}, \text{cedula}^{\text{UK}}, \text{contrasena\_hash}, \text{rol}, \text{activo}, \text{es\_inicial}, \text{creado\_en}, \text{actualizado\_en})$$
* **Determinante:** `id` (PK) determina funcionalmente todos los demás campos.
* **Cumplimiento 3FN:** $\checkmark$ Todos los atributos dependen únicamente de la clave primaria.

#### 2. Tabla `aulas_laboratorios`
$$\text{aulas\_laboratorios}(\underline{\text{id}}, \text{codigo}^{\text{UK}}, \text{nombre}, \text{tipo})$$
* **Determinante:** `id` (PK) determina `codigo`, `nombre` y `tipo`.
* **Cumplimiento 3FN:** $\checkmark$ No existen dependencias transitivas.

#### 3. Tabla `reportes_diarios`
$$\text{reportes\_diarios}(\underline{\text{id}}, \text{usuario\_id}^{\text{FK}}, \text{aula\_id}^{\text{FK}}, \text{fecha}, \text{hora}, \text{creado\_en})$$
* **Determinante:** `id` (PK) determina la fecha, hora, el usuario emisor y el aula asignada.
* **Cumplimiento 3FN:** $\checkmark$ Las referencias a usuario y aula se gestionan mediante claves foráneas puras.

#### 4. Tabla `asignaciones_equipos`
$$\text{asignaciones\_equipos}(\underline{\text{id}}, \text{reporte\_diario\_id}^{\text{FK}}, \text{numero\_pc}, \text{nombre\_alumno})$$
* **Determinante:** `id` (PK) determina la asignación concreta del equipo y el alumno en el contexto de un reporte.
* **Cumplimiento 3FN:** $\checkmark$ Dependencia estricta de la clave primaria.

#### 5. Tabla `reportes_incidencias`
$$\text{reportes\_incidencias}(\underline{\text{id}}, \text{usuario\_id}^{\text{FK}}, \text{aula\_id}^{\text{FK}}, \text{asignatura}, \text{fecha\_incidencia}, \text{hora\_apertura}, \text{tipo\_solicitud}, \text{otro\_tipo}, \text{descripcion}, \text{estado}, \text{creado\_en})$$
* **Determinante:** `id` (PK) determina todos los datos del incidente.
* **Cumplimiento 3FN:** $\checkmark$ Sin dependencias transitivas ni parciales.

---

## 6. Cuadro Comparativo de Prevención de Anomalías

| Operación | Estado No Normalizado (0FN) | Estado Normalizado en 3FN |
| :--- | :--- | :--- |
| **Inserción de Usuario** | Imposible crear un nuevo usuario si no ha registrado aún un reporte diario o incidencia. | **Resuelto:** Se inserta directamente en la tabla `usuarios` independientemente de si posee reportes. |
| **Inserción de Aula** | Imposible registrar un nuevo laboratorio sin haber tenido clases registradas allí. | **Resuelto:** Se registra de forma independiente en la catálogo `aulas_laboratorios`. |
| **Modificación de Nombre** | Cambiar el nombre de un profesor exige actualizar cientos de filas repetidas en reportes pasados. | **Resuelto:** Se actualiza un solo registro en `usuarios.nombre`. |
| **Borrado de Reporte** | Eliminar un reporte implica borrar potencialmente el registro histórico del usuario y del aula. | **Resuelto:** El borrado en `reportes_diarios` elimina en cascada únicamente las `asignaciones_equipos`, preservando los usuarios y las aulas intactos. |
