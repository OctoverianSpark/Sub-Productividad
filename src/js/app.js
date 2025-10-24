
document.addEventListener("DOMContentLoaded", EventListenters)

function EventListenters() {
    modals();
    rangeValue();
    requestHours();
    activateUpdates();
    changeForm();
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

function changeForm() {
    const filterSelector = document.querySelector("#searchBy");
    if (!filterSelector) return;

    const dateSearch = document.querySelector("#date-search");
    const dateSearchInput = document.querySelectorAll("#date-search-input");
    const textSearch = document.querySelector("#text-search");

    filterSelector.addEventListener("input", (e) => {
        const isFecha = e.target.value === "fecha";

        dateSearch.style.display = isFecha ? "flex" : "none";
        textSearch.style.display = isFecha ? "none" : "flex";

        textSearch.children[1].disabled = isFecha;
        dateSearchInput.forEach((input) => (input.disabled = !isFecha));
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

        removeButton.addEventListener("click", () => {
            if (pageCount <= 1) return;
            document.querySelector(`#pagina-${pageCount}`).remove();
            pageCount;
            updateButtons();
        });
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

    newPage.id = `entrada-${pageNumber}`;
    newPage.querySelector(".subtitle").textContent = "Entrada " + pageNumber;

    const index = pageNumber - 1;

    const setName = (selector, field) => {
        const el = newPage.querySelector(selector);
        if (el) el.setAttribute("name", `horas[${index}][${field}]`);
    };

    setName("#empleado", "empleado");
    setName("#cliente", "cliente");
    setName("#inicio", "inicio");
    setName("#almuerzo-0", "almuerzo");
    setName("#almuerzo-1", "almuerzo");
    setName("almuerzo-2", "almuerezo");
    setName("#final", "final");

    const horasfield = [
        "diurnas_ordinarias",
        "nocturnas_ordinarias",
        "diurnas_extras",
        "nocturnas_extras",
    ];
    horasfield.forEach((field) => {
        setName(`#${field}`, field);
        setName(`#${field}_value`, field);
    });

    setName("#cena", "cena");
    setName("#taxi", "taxi");
    setName("#comantarios", "comentarios");

    multiPage.appendChild(newPage);
    detectandExec();
    scrollToPage();
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

