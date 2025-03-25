// Inmediatly Invoque funcion expression IIFE

(function(){

    obtenerTareas();
    let tareas = [];
    let filtradas = [];

    let todasCheck = document.querySelector('#todas');
    let completadasCheck = document.querySelector('#completadas');
    let pendientesCheck = document.querySelector('#pendientes');

    // Boton para mostrar el Modal de agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', function () {
        mostrarFormulario();
    });

    // Filtros de busqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');
    //console.log(filtros);
    // filtros.forEach( radio => {
    //     radio.addEventListener('input', filtrarTareas);
    // } )

    // << USUARIO
    filtros.forEach(radio => {
        radio.addEventListener('input', mostrarTareas );
    })
    // USUARIO >>

    // function filtrarTareas(e) {
    //     const filtro = e.target.value;
        
    //     if (filtro !== '') {
    //         filtradas = tareas.filter(tarea => tarea.estado === filtro);
    //     } else {
    //         filtradas = [];
    //     }
    //     mostrarTareas();           
    // }


    async function obtenerTareas() {
        try {
            //En el id guardaremmos el proyecto que hemos obtenido. URL guardaremos la direccion mas el id del proyecto que hemos obtenido
            const id = obtenerProyecto();
            const url = `api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            
            tareas = resultado.tareas;

            // << USUARIO
            actualizarCompletadasPendientes();
            // USUARIO >>

            mostrarTareas();
            
        } catch (error) {
            console.log(error);
            
        }
    }

    //Creamos la funcion para mostrar las tareas
    function mostrarTareas() {
        limpiarTareas();
        //totalPendientes();
        //totalCompletas();
        //const arrayTareas = filtradas.length ? filtradas : tareas;

        // <<USUARIO
        let arrayTareas = [];
        if (todasCheck.checked) {
            arrayTareas = tareas;
        } else if (completadasCheck.checked) {
            arrayTareas = completadas;
        } else {
            arrayTareas = pendientes;
        }

        if (arrayTareas.length === 0) {
            const contenedorTareas = document.querySelector('#listado-tareas');
            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No Hay Tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }
        
        const estados = {
            0: "Pendiente",
            1: "Completa"
        };

        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.ondblclick = function () {
                mostrarFormulario(editar = true, {...tarea});
            }

            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            // Botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado;
            btnEstadoTarea.ondblclick = function() {
                cambiarEstadoTarea({...tarea});
            }

            const btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';
            btnEliminarTarea.ondblclick = function () {
                confirmarEliminarTarea({...tarea});
                
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);
            
            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);
        });
    }

    // function totalPendientes() {
    //     const totalPendientes = tareas.filter(tarea => tarea.estado === "0");
    //     const radioPendientes = document.querySelector('#pendientes');

    //     if (totalPendientes.length === 0) {
    //         radioPendientes.disabled = true;
    //     } else {
    //         radioPendientes.disabled = false;
    //     }
    // }

    // function totalCompletas() {
    //     const totalCompletas = tareas.filter(tarea => tarea.estado === "1");
    //     const radioCompletas = document.querySelector('#completadas');

    //     if (totalCompletas.length === 0) {
    //         radioCompletas.disabled = true;
    //     } else {
    //         radioCompletas.disabled = false;
    //     }
    // }

    function mostrarFormulario(editar = false, tarea = {}) {
        //console.log(editar);
        //console.log(tarea);
        
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input
                        type="text"
                        name="tarea"
                        placeholder="${tarea.nombre ? 'Editar la tarea' : 'Añadir Tarea al Proyecto Actual'}"
                        id="tarea"
                        value="${tarea.nombre ? tarea.nombre : ''}"
                    />    
                </div>
                <div class="opciones">
                    <input 
                        type="submit" 
                        class="submit-nueva-tarea" 
                        value="${tarea.nombre ? 'Guardar cambios' : 'Añadir Tarea'}"
                    />
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 0);

        // Delegation --> como generamos html con Js, no podemos asociar acciones a cosas que todavia no existen.
        // Por ejemplo, usando addEventListener a clases que fueron declaradas en el innerHTML.
        modal.addEventListener('click', function (e) {
            e.preventDefault();
            if(e.target.classList.contains('cerrar-modal')){
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');
                setTimeout(() => {
                    modal.remove();
                }, 400);
            } 
            if(e.target.classList.contains('submit-nueva-tarea')){
                const nombreTarea = document.querySelector('#tarea').value.trim();
                if (nombreTarea === '') {
                    // Mostrar una alerta de error
                    mostrarAlerta('El nombre de la tarea es Obligatorio', 'error', document.querySelector('.formulario legend'));            
                    return;
                }       
                if (editar) {
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                } else {
                    agregarTarea(nombreTarea);
                    
                }
            }
        });
        document.querySelector('.dashboard').appendChild(modal);
    }



    // Muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        // Previene la cracion de multiples ALERTAs
        const alertaPrevia = document.querySelector('.alerta');
        if (alertaPrevia) {
            alertaPrevia.remove();
        }

        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;
        //referencia.appendChild(alerta);
        //referencia.insertBefore()
        //console.log(referencia);
        
        // Inserta la alerta abajo del legend. No dentro de legend (appendChild).
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

        // Eliminar la alerta dps de 5 segundos
        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }

    // Consultar el Servidor para añadir una nueva tarea al proyecto actual
    async function agregarTarea(tarea) {
        // Construir la peticion
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());      
          
        try {
            const url = 'http://localhost:3000/api/tarea';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();
            console.log(resultado);
            mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.formulario legend')); 

            if (resultado.tipo === 'exito') {
                const modal = document.querySelector('.modal');
                setTimeout(() => {
                    modal.remove();
                }, 3000);

                // Agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id),
                    nombre: tarea,
                    estado: "0",
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj];
                console.log(tareas);
                // << USUARIO
                actualizarCompletadasPendientes();
                // USUARIO >>
                mostrarTareas();
            }
            
        } catch (error) {
            console.log(error);
            
        }
    }

    function cambiarEstadoTarea(tarea) {
        //console.log(tarea);
        
        const nuevoEstado = tarea.estado === "1" ? "0" : "1";
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);
    }

    async function actualizarTarea(tarea) {
        // console.log(tarea);
        // return;
        const { estado, id, nombre } = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        // for( let valor of datos.values() ){
        //     console.log(valor);
        // }

        try {
            const url = 'http://localhost:3000/api/tarea/actualizar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();
            
            if (resultado.respuesta.tipo === 'exito') {
                // mostrarAlerta(
                //     resultado.respuesta.mensaje,
                //     resultado.respuesta.tipo,
                //     document.querySelector('.contenedor-nueva-tarea')
                // );
                Swal.fire(
                    resultado.respuesta.mensaje,
                    resultado.respuesta.mensaje,
                    'success'
                );

                const modal = document.querySelector('.modal');
                if (modal) {
                    modal.remove();
                }

                tareas = tareas.map(tareaMemoria => {
                    if (tareaMemoria.id === id) {
                        tareaMemoria.estado = estado;   
                        tareaMemoria.nombre = nombre;   
                    }
                    return tareaMemoria;
                });
                // << USUARIO
                actualizarCompletadasPendientes();
                // USUARIO >>
                mostrarTareas();
                
            }
        } catch (error) {
            console.log(error);
        }
    }

    function confirmarEliminarTarea(tarea) {
        Swal.fire({
            title: "¿Eliminar Tarea?",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: `No`
          }).then((result) => {
            if (result.isConfirmed) {
              eliminarTarea(tarea);
              
            } 
          });
    }

    async function eliminarTarea(tarea) {
        const { id, nombre, estado } = tarea;
        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();
            //console.log(resultado);
            if (resultado.resultado) {
                // mostrarAlerta(
                //     resultado.mensaje,
                //     resultado.tipo,
                //     document.querySelector('.contenedor-nueva-tarea')
                // );
                Swal.fire('Eliminado!', resultado.mensaje, 'success');

                tareas = tareas.filter( tareaMemoria => tareaMemoria.id !== tarea.id);
                // << USUARIO
                actualizarCompletadasPendientes();
                // USUARIO >>
                mostrarTareas();
            }
        } catch (error) {
            
        }
    }

    function obtenerProyecto() {
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries());
        return proyecto.id;
    }

    // Limpiar tareas para que no se repitan
    function limpiarTareas() {
        const listadoTareas = document.querySelector('#listado-tareas');

        while (listadoTareas.firstChild) {
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }

    function actualizarCompletadasPendientes() {
        completadas = tareas.filter(tarea => tarea.estado === "1");
            if (!completadas.length) {
                completadasCheck.disabled = true;
            } else {
                completadasCheck.disabled = false;
            }

            pendientes = tareas.filter(tarea => tarea.estado === "0");
            if (!pendientes.length) {
                pendientesCheck.disabled = true;
            } else {
                pendientesCheck.disabled = false;
            }
    }
})();