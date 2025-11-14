
document.addEventListener("DOMContentLoaded", EventListenters)

function EventListenters() {
    modals();
    rangeValue();
    requestHours();
    activateUpdates();
    confirmForm();
    dragNdrop();
    pages();
    detectandExec();
    scrollToPage();
    selector();
}

function modals() {
    const modal = document.querySelector(".modal");
    if (!modal) return;

    const button = modal.querySelector("button");
    button.addEventListener("click", () => modal.remove());
}

function confirmForm() {
    const modal = document.querySelector(".modal-confirmacion");
    const buttonCharge = document.querySelector("#button-charge");

    if (!modal || !buttonCharge) return;

    const confirm = document.querySelector(".confirm-button");
    const deny = document.querySelector(".deny-button");
    const form = document.querySelector("form");

    buttonCharge.addEventListener("click", (e) => {
        modal.style.display = "flex";
    });

    confirm.addEventListener("click", () => form.submit());
    deny.addEventListener("click", () => {
        modal.style.display = "none";
    });
}


function rangeValue() {
    const containers = document.querySelectorAll(".range-number-container");

    containers.forEach((container) => {
        const range = container.querySelector("input[type='range']");
        const number = container.querySelector("input[type='number']");

        if (!range || !number) return;

        const syncValue = (source, target) => {
            target.value = source.value;
        };

        range.addEventListener("input", () => {
            syncValue(range, number);
        });
        number.addEventListener("input", () => {
            syncValue(number, range);
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

function calculate(page) {
    const elements = {
        inicio: page.querySelector("#inicio"),
        final: page.querySelector("#final"),
        radioAlm: page.querySelectorAll(".radio-input"),
        diurnasOrd: {
            slider: page.querySelector("#diurnas_ordinarias"),
            value: page.querySelector("#diurnas_ordinarias_value"),
        },
        nocturnasOrd: {
            slider: page.querySelector("#nocturnas_ordinarias"),
            value: page.querySelector("#nocturnas_ordinarias_value"),
        },
        diurnasExt: {
            slider: page.querySelector("#diurnas_extras"),
            value: page.querySelector("#diurnas_extras_value"),
        },
        nocturnasExt: {
            slider: page.querySelector("#nocturnas_extras"),
            value: page.querySelector("#nocturnas_extras_value"),
        },
    };

    if (!elements.inicio || !elements.final) return;

    let almuerzo = 2;

    const calcularHoras = (esDesdeRadio = false) => {
        let inicio = new Date(elements.inicio.value);
        let final = new Date(elements.final.value);

        if (!inicio.getDate() || !final.getDate()) return;

        const totalHoras = (final - inicio) / 3600000;
        let ordinarias = totalHoras - almuerzo;
        let extrasDiurnas = 0;
        let extrasNocturnas = 0;
        
        const horasFinal = final.getHours();
        const esNocturno =
            horasFinal > 21 ||
            (horasFinal <= 6 && final.getDay() === inicio.getDay() + 1);

        if (esNocturno) {
            ordinarias = totalHoras;
            extrasDiurnas = 5 - almuerzo;
            extrasNocturnas = totalHoras - (esDesdeRadio ? 13 : 11);
        } else if (horasFinal <= 21 && horasFinal > 6) {
            extrasDiurnas = ordinarias - 8;
        }

        actualizarCampo(elements.diurnasOrd, Math.min(ordinarias, 8));
        actualizarCampo(elements.diurnasExt, Math.max(extrasDiurnas, 0));
        actualizarCampo(elements.nocturnasExt, Math.max(extrasNocturnas, 0));
    };

    const actualizarCampo = (campo, valor) => {
        const valorFinal = valor ?? 0;
        if (campo.slider) campo.slider.value = valorFinal;
        if (campo.value) campo.value.value = valorFinal;
    };

    elements.radioAlm.forEach((radio) => {
        radio.addEventListener("input", (e) => {
            almuerzo = parseFloat(e.target.value);
            calcularHoras(true);
        });
    });

    elements.inicio.addEventListener("input", () => {calcularHoras(false);});
    elements.final.addEventListener("input", () => {calcularHoras(false)});
} 


function pages() {
    const indexContainer = document.querySelector(".index-page");
    if (!indexContainer) return;

    const addButton = indexContainer.querySelector(".page-add");
    const removeButton = indexContainer.querySelector(".page-remove");
    const pagesIndex = indexContainer.querySelector(".pages");

    let pageCount = pagesIndex.querySelectorAll(".page-number").length;

    const updateButtons = () => {
        addButton.disabled = pageCount >= 7;
        removeButton.disabled = pageCount <= 1;
    };

    addButton.addEventListener("click", () => {
        if (pageCount >= 8) return;

        pageCount++;

        const newIndex = document.createElement("A");
        newIndex.href = `#entrada-${pageCount}`;
        newIndex.textContent = pageCount;
        newIndex.classList.add("page-number");
        newIndex.id = `pagina-${pageCount}`;
        pagesIndex.appendChild(newIndex);
        createPage(pageCount);
        rangeValue();
        updateButtons();

    });

    removeButton.addEventListener("click", () => {
        if (pageCount <= 1) return;

        document.querySelector("#pagina-" + pageCount).remove(),
            deletePage(pageCount);
        pageCount--;
        updateButtons();
    });
}

function createPage(pageNumber) {
    const multiPage = document.querySelector(".container-times");
    const registerPage = multiPage.querySelector(".container-page");
    const newPage = registerPage.cloneNode(true);
  
    // 🔹 Asignar ID y título del nuevo bloque
    newPage.id = `entrada-${pageNumber}`;
    const subtitle = newPage.querySelector(".subtitle");
    if (subtitle) subtitle.textContent = "Entrada " + pageNumber;
  
    // 🔹 Generar nuevos IDs únicos y actualizar labels
    const idMap = new Map();
    newPage.querySelectorAll("[id]").forEach((el) => {
      const oldId = el.id;
      const newId = `${oldId}-${crypto.randomUUID()}`;
      idMap.set(oldId, newId);
      el.id = newId;
    });
  
    newPage.querySelectorAll("label[for]").forEach((label) => {
      const oldFor = label.getAttribute("for");
      if (idMap.has(oldFor)) label.setAttribute("for", idMap.get(oldFor));
    });
  
    // 🔹 Actualizar names y limpiar valores
    newPage.querySelectorAll("input, select, textarea").forEach((el) => {
      const baseField = el.getAttribute("data-field") || el.name || el.id;
  
      if (baseField) {
        // Reemplaza el índice en horas[0] → horas[nuevo]
        el.name = baseField.replace(/horas\[\d+\]/, `horas[${pageNumber - 1}]`);
      }
  
      // 🔹 Limpieza según tipo de elemento
      if (el.type === "radio") {
        el.checked = el.value == 2
      } else if (el.type === "checkbox") {
        el.checked = false; // checkboxes desmarcados
      } else if (el.tagName === "SELECT") {
        el.selectedIndex = 0; // select reseteado
      }
    });
  
    // 🔹 Agregar el nuevo bloque al contenedor
    multiPage.appendChild(newPage);
  
    // 🔹 Reinicializar Select2
    newPage.querySelectorAll("select").forEach((select) => {
      const next = select.nextElementSibling;
      if (next && next.classList.contains("select2")) next.remove();
  
      select.classList.remove("select2-hidden-accessible");
      select.removeAttribute("data-select2-id");
      select.removeAttribute("aria-hidden");  

        select.querySelectorAll("option").forEach(option => {
            option.removeAttribute("data-select2-id");
        })

      $(select).select2({
        width: "100%",
        placeholder: "Seleccione una opción",
        allowClear: false,
        dropdownParent: $(newPage),
    });
    });
  
    // 🔹 Ejecutar funciones adicionales si existen
    if (typeof detectandExec === "function") detectandExec();
    if (typeof scrollToPage === "function") scrollToPage(newPage);
  }
  
  

function deletePage(pageNumber) {
    document.querySelector(`#entrada-${pageNumber}`).remove();
}

function scrollToPage() {
    const indexes = document.querySelectorAll(".page-number");

    indexes.forEach((index) => {
        index.addEventListener("click", (e) => {
            e.preventDefault();
            const pageScroll = e.target.getAttribute("href");
            const page = document.querySelector(pageScroll);
            page.scrollIntoView({ behavior: "smooth" });
        });
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
        startX = e.clientX;
        startY = e.clientY;

        document.addEventListener("mousemove", mouseMove);
        document.addEventListener("mouseup", mouseUp);
    };

    dragable.addEventListener("mousedown", mouseDown);
}


function detectandExec() {
  let i = 1;
  let page;
  while ((page = document.querySelector(`#entrada-${i}`))) {
    calculate(page);
    i++;
  }
}


function selector() {
  $(document).ready(function () {
    $("#empleado").select2();
    $("#cliente").select2();
  });
}

