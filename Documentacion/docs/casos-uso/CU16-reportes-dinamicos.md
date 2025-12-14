# CU16 - Generar Reportes Dinámicos

| Campo                                        | Descripción                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| -------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Caso de uso**                              | CU16 - Generar Reportes Dinámicos                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Propósito**                                | Permite a Administradores y Coordinadores generar reportes personalizados seleccionando cualquier tabla del sistema, eligiendo campos específicos, aplicando filtros y exportando en múltiples formatos.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| **Actores**                                  | • Administrador<br>• Coordinador                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **Iniciador**                                | • Administrador<br>• Coordinador                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **Precondición**                             | El usuario debe haber iniciado sesión en el sistema (CU01).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| **Flujo Principal**                          | • El usuario accede al módulo de "Reportes" → "Dynamic Reports".<br>• El sistema muestra la interfaz de generación de reportes con:<br>&nbsp;&nbsp;- Lista de tablas disponibles<br>&nbsp;&nbsp;- Área para seleccionar campos<br>&nbsp;&nbsp;- Área para definir filtros<br>&nbsp;&nbsp;- Opciones de exportación<br>• El usuario selecciona una tabla del sistema.<br>• El sistema carga los campos disponibles de la tabla seleccionada.<br>• El usuario selecciona los campos que desea incluir en el reporte.<br>• El usuario puede aplicar filtros opcionales:<br>&nbsp;&nbsp;- Campo a filtrar<br>&nbsp;&nbsp;- Operador (=, !=, \u003e, \u003c, LIKE, etc.)<br>&nbsp;&nbsp;- Valor del filtro<br>• El usuario hace clic en "Generar Reporte".<br>• El sistema ejecuta la consulta con los parámetros seleccionados.<br>• El sistema muestra los resultados en pantalla con paginación.<br>• El usuario puede exportar el reporte en formato PDF, Excel o HTML. |
| **Flujo Alternativo - Gestionar Plantillas** | • El usuario puede guardar la configuración actual como plantilla:<br>&nbsp;&nbsp;- Nombre de la plantilla<br>&nbsp;&nbsp;- Descripción<br>&nbsp;&nbsp;- Visibilidad (privada/pública)<br>• El sistema guarda la plantilla con:<br>&nbsp;&nbsp;- Tabla seleccionada<br>&nbsp;&nbsp;- Campos seleccionados<br>&nbsp;&nbsp;- Filtros aplicados<br>• El usuario puede cargar plantillas guardadas previamente.<br>• El usuario puede editar o eliminar sus propias plantillas.<br>• El usuario puede ver plantillas públicas creadas por otros usuarios.                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **Postcondición**                            | El reporte se genera exitosamente y puede ser visualizado o exportado. Las plantillas quedan guardadas para uso futuro.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| **Excepción**                                | • No se selecciona ningún campo.<br>• La tabla seleccionada no existe.<br>• Los filtros tienen valores inválidos.<br>• Error en la consulta SQL.<br>• Error al generar el archivo de exportación.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |

## Tablas Disponibles

El sistema permite generar reportes de las siguientes tablas:

-   Usuarios (users)
-   Materias (subjects)
-   Grupos (groups)
-   Aulas (classrooms)
-   Asignaciones (assignments)
-   Carreras Universitarias (university_careers)
-   Horarios (schedules)
-   Días (days)
-   Reservas Especiales (special_reservations)
-   Y más...

## Tipos de Filtros

| Operador    | Descripción   | Ejemplo                         |
| ----------- | ------------- | ------------------------------- |
| **=**       | Igual a       | name = 'Juan'                   |
| **!=**      | Diferente de  | status != 'inactivo'            |
| **\u003e**  | Mayor que     | duration_years \u003e 4         |
| **\u003c**  | Menor que     | code \u003c 1000                |
| **\u003e=** | Mayor o igual | created_at \u003e= '2024-01-01' |
| **\u003c=** | Menor o igual | end_time \u003c= '18:00'        |
| **LIKE**    | Contiene      | name LIKE '%Admin%'             |

## Formatos de Exportación

1. **PDF**: Documento imprimible con formato profesional
2. **Excel**: Hoja de cálculo editable (.xlsx)
3. **HTML**: Página web descargable

## Características de Plantillas

| Atributo            | Descripción                   |
| ------------------- | ----------------------------- |
| **name**            | Nombre de la plantilla        |
| **description**     | Descripción del propósito     |
| **table_name**      | Tabla del reporte             |
| **selected_fields** | Campos incluidos (JSON)       |
| **filters**         | Filtros aplicados (JSON)      |
| **is_public**       | Visibilidad (privada/pública) |
| **user_id**         | Creador de la plantilla       |

## Validaciones Implementadas

1. **Campos Obligatorios**: Debe seleccionar al menos un campo.
2. **Tabla Válida**: La tabla debe existir en el sistema.
3. **Filtros Válidos**: Los valores de filtros deben ser del tipo correcto.
4. **Permisos**: Solo el creador puede editar/eliminar plantillas privadas.

## Diagrama de Caso de Uso

![Diagrama de Caso de Uso](file:///home/evert/university/subjects/information_systems/gestor-de-horarios-acad-micos/gestion_asignacion_aulas/docs/casos-uso/CU16-reportes-dinamicos.png)
