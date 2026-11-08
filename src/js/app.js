document.addEventListener("DOMContentLoaded", EventListenters)

function EventListenters() {
    modals();
    rangeValue();
    requestHours();
    activateUpdates();
    dragNdrop();
    selector();
    formularioHoras();
}

function modals() {
    // El modal de confirmacion tiene sus propios botones
    const modal = document.querySelector(".modal:not(.modal-confirmacion)");
    if (!modal) return;

    const button = modal.querySelector("button");
    button.addEventListener("click", () => modal.remove());
}

// Sincroniza cada slider con su casilla numerica (la casilla es la que se envia)
function rangeValue(raiz = document) {
    raiz.querySelectorAll(".range-number-container").forEach((container) => {
        const range = container.querySelector("input[type='range']");
        const number = container.querySelector("input[type='number']");
        if (!range || !number || container.dataset.sincronizado) return;
        container.dataset.sincronizado = "1";

        range.addEventListener("input", () => {
            number.value = range.value;
            number.dispatchEvent(new Event("input", { bubbles: true }));
        });
        number.addEventListener("input", () => {
            range.value = number.value;
        });
    });
}

function requestHours() {
    const radios = document.querySelectorAll("#radio-selector");

    radios.forEach((radio) => {
        radio.addEventListener("input", (e) => {
            location.href = `/horas/ver?table=${e.target.value}`;
        });
    });
}

function activateUpdates() {
    const checkBox = document.querySelector("#actualizar");
    if (!checkBox) return;

    const updateForm = document.querySelector(".container-inputs");

    checkBox.addEventListener("input", (e) => {
        if (updateForm) {
            updateForm.disabled = !e.target.checked;
        }
    });
}

function dragNdrop() {
    const dragable = document.querySelector(".dragable");
    if (!dragable) return;

    let startX = 0,
        startY = 0;

    const mouseMove = (e) => {
        const deltax = startX - e.clientX;
        const deltaY = startY - e.clientY;

        startX = e.clientX;
        startY = e.clientY;

        dragable.style.top = `${dragable.offsetTop - deltaY}px`;
        dragable.style.left = `${dragable.offsetLeft - deltax}px`;
    };

    const mouseUp = () => {
        document.removeEventListener("mousemove", mouseMove);
        document.removeEventListener("mouseup", mouseUp);
    };

    const mouseDown = (e) => {
        // No arrastrar al hacer clic en los botones o numeros de pagina
        if (e.target.closest("button, a")) return;
        startX = e.clientX;
        startY = e.clientY;

        document.addEventListener("mousemove", mouseMove);
        document.addEventListener("mouseup", mouseUp);
    };

    dragable.addEventListener("mousedown", mouseDown);
}

function iniciarSelect2(select, contenedor) {
    if (typeof $ === "undefined" || !$.fn.select2) return;
    $(select).select2({
        width: "100%",
        placeholder: "Seleccione una opcion",
        dropdownParent: contenedor ? $(contenedor) : undefined,
    });
}

function selector() {
    // Formulario de registro: .js-select2 | Pantalla de edicion: #empleado y #cliente
    document.querySelectorAll(".js-select2, .form-actualizaciones #empleado, .form-actualizaciones #cliente").forEach((select) => {
        iniciarSelect2(select, select.closest(".container-page"));
    });
}

// =====================================================================
// FORMULARIO DE REGISTRO DE HORAS
// =====================================================================

const MAX_ENTRADAS = 8;

function formularioHoras() {
    const form = document.querySelector(".form-times");
    if (!form) return;

    const cfg = {
        inicioNocturno: aMinutos(form.dataset.inicioNocturno || "19:00"),
        finNocturno: aMinutos(form.dataset.finNocturno || "06:00"),
        horasDia: parseFloat(form.dataset.horasDia) || 8,
        maxHoras: parseFloat(form.dataset.maxHoras) || 24,
    };

    const contenedor = form.querySelector(".container-times");
    const indice = form.querySelector(".pages");
    const botonAgregar = form.querySelector(".page-add");
    const botonQuitar = form.querySelector(".page-remove");

    const paginas = () => [...contenedor.querySelectorAll(".container-page")];

    const actualizarBotones = () => {
        botonAgregar.disabled = paginas().length >= MAX_ENTRADAS;
        botonQuitar.disabled = paginas().length <= 1;
    };

    // Entradas nuevas se calculan; las que vuelven con errores conservan lo que escribio el usuario
    paginas().forEach((pagina) => prepararPagina(pagina, cfg, form.dataset.nuevo === "1"));
    actualizarBotones();

    // Navegacion entre entradas (un solo listener para todos los numeros)
    indice.addEventListener("click", (e) => {
        const enlace = e.target.closest(".page-number");
        if (!enlace) return;
        e.preventDefault();
        const destino = document.querySelector(enlace.getAttribute("href"));
        if (destino) destino.scrollIntoView({ behavior: "smooth" });
    });

    botonAgregar.addEventListener("click", () => {
        if (paginas().length >= MAX_ENTRADAS) return;
        const nueva = clonarPagina(ultima(paginas()), paginas().length + 1);
        contenedor.appendChild(nueva);

        const enlace = document.createElement("A");
        enlace.href = `#${nueva.id}`;
        enlace.id = `pagina-${paginas().length}`;
        enlace.className = "page-number";
        enlace.textContent = paginas().length;
        indice.appendChild(enlace);

        prepararPagina(nueva, cfg, true);
        actualizarBotones();
        nueva.scrollIntoView({ behavior: "smooth" });
    });

    botonQuitar.addEventListener("click", () => {
        const lista = paginas();
        if (lista.length <= 1) return;
        ultima(lista).remove();
        const enlace = indice.querySelector(`#pagina-${lista.length}`);
        if (enlace) enlace.remove();
        actualizarBotones();
    });

    confirmarRegistro(form, paginas, cfg);
}

// Engancha los eventos de una entrada. "recalcular" llena las horas desde el inicio/final.
function prepararPagina(pagina, cfg, recalcular) {
    const campo = (nombre) => pagina.querySelector(`[data-campo="${nombre}"]`);
    const inicio = campo("inicio");
    const final = campo("final");

    const calcular = () => {
        const r = calcularJornada(inicio.value, final.value, almuerzoDe(pagina), cfg);
        if (!r) return resumir(pagina, cfg);
        ["diurnas_ordinarias", "nocturnas_ordinarias", "diurnas_extras", "nocturnas_extras"].forEach((c) => {
            const input = campo(c);
            input.value = r[c];
            const contenedorRango = input.closest(".range-number-container");
            const rango = contenedorRango && contenedorRango.querySelector("input[type='range']");
            if (rango) rango.value = r[c];
        });
        resumir(pagina, cfg);
    };

    inicio.addEventListener("change", () => {
        // Si el final quedo antes del inicio, se mueve al mismo dia
        if (final.value && inicio.value && final.value <= inicio.value) {
            final.value = inicio.value.slice(0, 10) + final.value.slice(10);
            if (final.value <= inicio.value) final.value = sumarHoras(inicio.value, cfg.horasDia + almuerzoDe(pagina));
        }
        marcarFestivo(pagina, true);
        calcular();
    });
    // "input" recalcula mientras se escribe la hora; "change" cubre el selector de fecha
    final.addEventListener("input", calcular);
    final.addEventListener("change", calcular);
    inicio.addEventListener("input", () => {
        marcarFestivo(pagina, true);
        calcular();
    });
    pagina.querySelectorAll('[data-campo="almuerzo"]').forEach((r) => r.addEventListener("change", calcular));

    // Correcciones manuales: solo se actualiza el resumen
    pagina.querySelectorAll("input.calcTime").forEach((i) => i.addEventListener("input", () => resumir(pagina, cfg)));

    rangeValue(pagina);
    marcarFestivo(pagina, recalcular);
    if (recalcular) calcular();
    else resumir(pagina, cfg);
}

// Copia la ultima entrada: conserva fechas, almuerzo y cliente; limpia empleado, logistica y comentarios
function clonarPagina(origen, numero) {
    const nueva = origen.cloneNode(true);
    const indiceAnterior = Number(origen.id.replace("entrada-", ""));
    nueva.id = `entrada-${numero}`;
    nueva.querySelector(".subtitle").textContent = `Entrada ${numero}`;

    // Select2 deja su propio HTML: se quita y se vuelve a crear
    nueva.querySelectorAll(".select2").forEach((el) => el.remove());
    nueva.querySelectorAll(".range-number-container").forEach((c) => delete c.dataset.sincronizado);

    nueva.querySelectorAll("[name]").forEach((el) => {
        el.name = el.name.replace(/^horas\[\d+\]/, `horas[${numero - 1}]`);
    });
    nueva.querySelectorAll("[id]").forEach((el) => {
        el.id = el.id.replace(new RegExp(`-${indiceAnterior}(?=$|-)`), `-${numero}`);
    });
    nueva.querySelectorAll("label[for]").forEach((label) => {
        label.htmlFor = label.htmlFor.replace(new RegExp(`-${indiceAnterior}(?=$|-)`), `-${numero}`);
    });

    // Valores que cloneNode no copia de forma fiable
    origen.querySelectorAll("select").forEach((sel, i) => {
        nueva.querySelectorAll("select")[i].value = sel.value;
    });
    origen.querySelectorAll('[data-campo="almuerzo"]').forEach((r, i) => {
        nueva.querySelectorAll('[data-campo="almuerzo"]')[i].checked = r.checked;
    });

    nueva.querySelector('[data-campo="empleado"]').value = "";
    ["cena", "taxi"].forEach((c) => (nueva.querySelector(`[data-campo="${c}"]`).checked = false));
    nueva.querySelector('[data-campo="comentarios"]').value = "";

    nueva.querySelectorAll("select").forEach((sel) => {
        sel.classList.remove("select2-hidden-accessible");
        sel.removeAttribute("data-select2-id");
        sel.removeAttribute("aria-hidden");
        sel.removeAttribute("tabindex");
        sel.querySelectorAll("option").forEach((o) => o.removeAttribute("data-select2-id"));
    });
    // Se inicializa despues de insertarlo en el documento
    setTimeout(() => nueva.querySelectorAll("select").forEach((sel) => iniciarSelect2(sel, nueva)));

    return nueva;
}

/**
 * Reparte el tiempo entre inicio y final en diurnas/nocturnas y ordinarias/extras.
 * - Las primeras (horas ordinarias por dia + almuerzo) horas transcurridas son la jornada ordinaria.
 * - El almuerzo se descuenta de las diurnas ordinarias (y si no alcanza, de las nocturnas).
 * - Cada minuto es nocturno si cae dentro del horario nocturno configurado.
 */
function calcularJornada(inicioTxt, finalTxt, almuerzoHoras, cfg) {
    const inicio = new Date(inicioTxt);
    const final = new Date(finalTxt);
    if (isNaN(inicio) || isNaN(final)) return null;

    const total = Math.round((final - inicio) / 60000);
    if (total <= 0 || total > cfg.maxHoras * 60) return null;

    let almuerzo = Math.round(almuerzoHoras * 60);
    const limiteOrdinario = Math.round(cfg.horasDia * 60) + almuerzo;
    const minutoInicial = inicio.getHours() * 60 + inicio.getMinutes();
    const m = { dOrd: 0, nOrd: 0, dExt: 0, nExt: 0 };

    for (let i = 0; i < total; i++) {
        const minutoDelDia = (minutoInicial + i) % 1440;
        const nocturno = esNocturno(minutoDelDia, cfg);
        if (i < limiteOrdinario) nocturno ? m.nOrd++ : m.dOrd++;
        else nocturno ? m.nExt++ : m.dExt++;
    }

    for (const clave of ["dOrd", "nOrd", "dExt", "nExt"]) {
        const descontar = Math.min(almuerzo, m[clave]);
        m[clave] -= descontar;
        almuerzo -= descontar;
    }

    const h = (min) => Math.round((min / 60) * 100) / 100;
    return {
        diurnas_ordinarias: h(m.dOrd),
        nocturnas_ordinarias: h(m.nOrd),
        diurnas_extras: h(m.dExt),
        nocturnas_extras: h(m.nExt),
    };
}

function esNocturno(minuto, cfg) {
    const { inicioNocturno: ini, finNocturno: fin } = cfg;
    return ini > fin ? minuto >= ini || minuto < fin : minuto >= ini && minuto < fin;
}

// Muestra el total de la jornada frente a lo registrado y marca errores
function resumir(pagina, cfg) {
    const resumen = pagina.querySelector(".resumen-horas");
    const datos = datosPagina(pagina, cfg);
    resumen.textContent = datos.texto;
    resumen.classList.toggle("resumen-error", datos.errores.length > 0);
    if (datos.errores.length) resumen.textContent += " — " + datos.errores.join(" ");
}

function datosPagina(pagina, cfg) {
    const valor = (c) => {
        const el = pagina.querySelector(`[data-campo="${c}"]`);
        return el ? el.value : "";
    };
    const errores = [];
    const almuerzo = almuerzoDe(pagina);
    const inicio = new Date(valor("inicio"));
    const final = new Date(valor("final"));
    const transcurridas = (final - inicio) / 3600000;
    const registradas = ["diurnas_ordinarias", "nocturnas_ordinarias", "diurnas_extras", "nocturnas_extras"]
        .reduce((suma, c) => suma + (parseFloat(valor(c)) || 0), 0);

    if (!valor("empleado")) errores.push("Falta seleccionar el empleado.");
    if (isNaN(inicio) || isNaN(final)) {
        errores.push("Completa el inicio y el final.");
        return { errores, texto: "", registradas };
    }
    if (transcurridas <= 0) errores.push("El final debe ser despues del inicio.");
    else if (transcurridas > cfg.maxHoras) errores.push(`La jornada dura mas de ${cfg.maxHoras} horas: revisa las fechas.`);
    else if (registradas > transcurridas - almuerzo + 0.05) errores.push("Registraste mas horas de las que hay en la jornada.");
    else if (registradas <= 0) errores.push("Registra al menos una hora.");

    const texto = transcurridas > 0
        ? `Jornada ${redondear(transcurridas)} h − ${almuerzo} h almuerzo = ${redondear(transcurridas - almuerzo)} h · Registradas: ${redondear(registradas)} h`
        : "";
    return { errores, texto, registradas };
}

// Marca "festivo" automaticamente si la fecha es festivo en Colombia (se puede desmarcar)
function marcarFestivo(pagina, cambiarCasilla) {
    const inicio = pagina.querySelector('[data-campo="inicio"]').value;
    const casilla = pagina.querySelector('[data-campo="festivo"]');
    const aviso = pagina.querySelector(".festivo-aviso");
    if (!inicio || !casilla || !aviso) return;

    const fecha = inicio.slice(0, 10);
    const festivo = festivosColombia(Number(fecha.slice(0, 4))).get(fecha);
    const domingo = new Date(fecha + "T12:00").getDay() === 0;

    if (cambiarCasilla) {
        if (festivo) {
            casilla.checked = true;
            casilla.dataset.auto = "1";
        } else if (casilla.dataset.auto) {
            casilla.checked = false;
            delete casilla.dataset.auto;
        }
    }
    aviso.textContent = festivo
        ? `Festivo en Colombia: ${festivo}`
        : domingo ? "Domingo: se paga como dominical" : "";
}

function confirmarRegistro(form, paginas, cfg) {
    const modal = form.querySelector(".modal-confirmacion");
    const boton = form.querySelector("#button-charge");
    if (!modal || !boton) return;

    const resumen = modal.querySelector(".confirm-resumen");
    const si = modal.querySelector(".confirm-button");
    const no = modal.querySelector(".deny-button");

    boton.addEventListener("click", () => {
        resumen.innerHTML = "";
        let hayErrores = false;

        paginas().forEach((pagina, i) => {
            const datos = datosPagina(pagina, cfg);
            const empleado = pagina.querySelector('[data-campo="empleado"]');
            const opcion = empleado.selectedOptions[0];
            const nombre = (opcion && opcion.value && opcion.textContent.trim()) || "(sin empleado)";
            const linea = document.createElement("P");
            linea.textContent = `${i + 1}. ${nombre} · ${redondear(datos.registradas)} h` +
                (datos.errores.length ? ` — ${datos.errores.join(" ")}` : "");
            if (datos.errores.length) {
                linea.classList.add("resumen-error");
                hayErrores = true;
            }
            resumen.appendChild(linea);
        });

        si.disabled = hayErrores;
        si.title = hayErrores ? "Corrige las entradas marcadas en rojo" : "";
        modal.style.display = "flex";
    });

    si.addEventListener("click", () => {
        si.disabled = true; // evita doble envio
        form.submit();
    });
    no.addEventListener("click", () => {
        modal.style.display = "none";
    });
}

// ----- utilidades -----

function almuerzoDe(pagina) {
    const marcado = pagina.querySelector('[data-campo="almuerzo"]:checked');
    return marcado ? parseFloat(marcado.value) || 0 : 0;
}

function ultima(lista) {
    return lista[lista.length - 1];
}

function aMinutos(hhmm) {
    const [h, m] = hhmm.split(":").map(Number);
    return (h || 0) * 60 + (m || 0);
}

function redondear(n) {
    return Math.round(n * 100) / 100;
}

function sumarHoras(fechaTxt, horas) {
    const d = new Date(new Date(fechaTxt).getTime() + horas * 3600000);
    const p = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

// Festivos de Colombia (Ley 51 de 1983 / Ley Emiliani): fijos, trasladables a lunes y los que dependen de Semana Santa
const cacheFestivos = new Map();
function festivosColombia(anio) {
    if (cacheFestivos.has(anio)) return cacheFestivos.get(anio);

    const p = (n) => String(n).padStart(2, "0");
    const clave = (d) => `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
    const fecha = (mes, dia) => new Date(anio, mes - 1, dia, 12);
    const alLunes = (d) => {
        const r = new Date(d);
        while (r.getDay() !== 1) r.setDate(r.getDate() + 1);
        return r;
    };
    const masDias = (d, n) => {
        const r = new Date(d);
        r.setDate(r.getDate() + n);
        return r;
    };

    // Domingo de Pascua (algoritmo de Meeus/Jones/Butcher)
    const a = anio % 19, b = Math.floor(anio / 100), c = anio % 100;
    const d = Math.floor(b / 4), e = b % 4, f = Math.floor((b + 8) / 25);
    const g = Math.floor((b - f + 1) / 3), h = (19 * a + b - d - g + 15) % 30;
    const i = Math.floor(c / 4), k = c % 4, l = (32 + 2 * e + 2 * i - h - k) % 7;
    const m = Math.floor((a + 11 * h + 22 * l) / 451);
    const pascua = fecha(Math.floor((h + l - 7 * m + 114) / 31), ((h + l - 7 * m + 114) % 31) + 1);

    const lista = [
        [fecha(1, 1), "Año Nuevo"],
        [alLunes(fecha(1, 6)), "Reyes Magos"],
        [alLunes(fecha(3, 19)), "San Jose"],
        [masDias(pascua, -3), "Jueves Santo"],
        [masDias(pascua, -2), "Viernes Santo"],
        [fecha(5, 1), "Dia del Trabajo"],
        [masDias(pascua, 43), "Ascension del Señor"],
        [masDias(pascua, 64), "Corpus Christi"],
        [masDias(pascua, 71), "Sagrado Corazon"],
        [alLunes(fecha(6, 29)), "San Pedro y San Pablo"],
        [fecha(7, 20), "Dia de la Independencia"],
        [fecha(8, 7), "Batalla de Boyaca"],
        [alLunes(fecha(8, 15)), "Asuncion de la Virgen"],
        [alLunes(fecha(10, 12)), "Dia de la Raza"],
        [alLunes(fecha(11, 1)), "Todos los Santos"],
        [alLunes(fecha(11, 11)), "Independencia de Cartagena"],
        [fecha(12, 8), "Inmaculada Concepcion"],
        [fecha(12, 25), "Navidad"],
    ];

    const mapa = new Map(lista.map(([d, nombre]) => [clave(d), nombre]));
    cacheFestivos.set(anio, mapa);
    return mapa;
}
