


function EventListenters(){

    modals()
    rangeValue()
    requestHours()
    activateUpdates()
    changeForm()
    confirmForm()
    dragNdrop()
    pages()
    detectandExec()
    scrollToPage()
}


function modals(){


    
    const modal = document.querySelector(".modal")

    if(modal){
        const button = document.querySelector(".modal button")
        button.addEventListener("click",e=>{
            console.log(modal)
            modal.remove()
        })


    }



}

function changeForm(){

    const dateSearch = document.querySelector("#date-search")
    const dateSearchInput = document.querySelectorAll("#date-search-input")
    const textSearch = document.querySelector("#text-search")

    const filterSelector = document.querySelector("#searchBy")

    if (filterSelector) {
        filterSelector.addEventListener("input",e=>{


            if(e.target.value === "fecha"){
                textSearch.style.display = "none"
                textSearch.children[1].disabled = true
                dateSearch.style.display = "block"

                dateSearchInput.forEach(date =>{
                    date.disabled = false
                })


            }else if(e.target.value !== "fecha"){
                textSearch.style.display = "block"
                textSearch.children[1].disabled = false

                dateSearch.style.display = "none"
                dateSearchInput.forEach(date =>{
                    date.disabled = true
                })

                




            }


        })
    }



}



function rangeValue(){

    const containers = document.querySelectorAll(".range-number-container")

    if(containers){
        containers.forEach(container=>{


            const range = container.querySelector("input[type='range']")
            const number = container.querySelector("input[type='number']")
            
            range.addEventListener("input",e=>{


                number.value = e.target.value

            })
            number.addEventListener("input",e=>{


                range.value = e.target.value

            })


        })


    }

}

function requestHours(){

    const radios = document.querySelectorAll("#radio-selector")



    if(radios){
        radios.forEach(radio =>{
            

            radio.addEventListener("input",e=>{

                location.href = `/horas/ver?table=${e.target.value}`


            })

        })
    }

}

function activateUpdates(){
    const checkBox = document.querySelector("#actualizar")




    if(checkBox){
        checkBox.addEventListener("input",e=>{

            console.log(e.target.value)
            const updateForm =document.querySelector(".container-inputs")
            if (e.target.checked) {
                updateForm.disabled = false
            } 
            if (!e.target.checked) {
                updateForm.disabled = true
            }

        })
    }
}




function calculate(page){
        const inicio = page.querySelector("#inicio")
        const final = page.querySelector("#final")
        const radioAlm = page.querySelectorAll(".radio-input")
        const diurnasOrdinariasSlider = page.querySelector("#diurnas_ordinarias")
        const diurnasOrdinariasValue = page.querySelector("#diurnas_ordinarias_value")
        const nocturnasOrdinariasSlider = page.querySelector("#nocturnas_ordinarias")
        const nocturnasOrdinariasValue = page.querySelector("#nocturnas_ordinarias_value")
        const diurnasExtrasSlider = page.querySelector("#diurnas_extras")
        const diurnasExtrasValue = page.querySelector("#diurnas_extras_value")
        const nocturnasExtrasSlider = page.querySelector("#nocturnas_extras")
        const nocturnasExtrasValue = page.querySelector("#nocturnas_extras_value")

        if(inicio){
            let dateTimeInicio = new Date(inicio.value)
            let dateTimeFinal = new Date(final.value)
        
            let alm = 2
        
            let valorOrdinarias = ((dateTimeFinal.getTime() - dateTimeInicio.getTime())/(3600000)) - alm
            let extrasDiurnas
            let extrasNocturnas
                
            const rangoNocturnas = [21,22,23,0,1,2,3,4,5,6]
        
        
            radioAlm.forEach(radio => {
                radio.addEventListener("input",e=>{
                        
                    alm = e.target.value
        
        
        
                    dateTimeInicio = new Date(inicio.value)
                    dateTimeFinal = new Date(final.value)
                    
                    valorOrdinarias = ((dateTimeFinal.getTime() - dateTimeInicio.getTime())/(3600000)) - alm
            
            
                    if(dateTimeFinal.getHours()<=21 && dateTimeFinal.getHours() >6){
                        extrasDiurnas = valorOrdinarias - 8
                    }
            
            
                    if(dateTimeFinal.getHours()>21 || (dateTimeFinal.getHours() <= 6 && dateTimeFinal.getDay() === dateTimeInicio.getDay() + 1)){
                        
            
                        extrasDiurnas = 5 - alm
            
                        extrasNocturnas = valorOrdinarias - 11
            
            
            
            
                    }  
            
                    if(valorOrdinarias >8){
                        diurnasOrdinariasSlider.value = 8
                        diurnasOrdinariasValue.value = 8
                    }else{
                        diurnasOrdinariasSlider.value = valorOrdinarias ? valorOrdinarias:0
                        diurnasOrdinariasValue.value = valorOrdinarias ? valorOrdinarias:0
        
                    }
        
        
        
                    if (extrasDiurnas < 0) {
                        diurnasExtrasSlider.value = 0
                        diurnasExtrasValue.value =  0
                        
                    }else{
                        diurnasExtrasSlider.value = extrasDiurnas ? extrasDiurnas : 0
                        diurnasExtrasValue.value = extrasDiurnas ? extrasDiurnas : 0
        
                    }
            
            
            
            
            
                    nocturnasExtrasSlider.value= extrasNocturnas ? extrasNocturnas : 0
                    nocturnasExtrasValue.value= extrasNocturnas ? extrasNocturnas : 0
                    
            
            
            
            
            
            
            
            
            
        
        
        
        
        
        
        
        
                })
            })
        
        
            inicio.addEventListener("input",e=>{
                
                console.log(inicio)
        
                dateTimeInicio = new Date(inicio.value)
                dateTimeFinal = new Date(final.value)
                
                valorOrdinarias = ((dateTimeFinal.getTime() - dateTimeInicio.getTime())/(3600000)) - alm
        
        
                if(dateTimeFinal.getHours()<=21 && dateTimeFinal.getHours() >6){
                    extrasDiurnas = valorOrdinarias - 8
                }
        
        
                if(dateTimeFinal.getHours()>21 || (dateTimeFinal.getHours() <= 6 && dateTimeFinal.getDay() === dateTimeInicio.getDay() + 1)){
                    
        
                    extrasDiurnas = 5 - alm
        
                    extrasNocturnas = valorOrdinarias - 11
        
        
        
        
                }  
        
                if(valorOrdinarias >8){
                    diurnasOrdinariasSlider.value = 8
                    diurnasOrdinariasValue.value = 8
                }else{
                    diurnasOrdinariasSlider.value = valorOrdinarias ? valorOrdinarias:0
                    diurnasOrdinariasValue.value = valorOrdinarias ? valorOrdinarias:0
        
                }
        
        
        
                if (extrasDiurnas < 0) {
                    diurnasExtrasSlider.value = 0
                    diurnasExtrasValue.value =  0
                    
                }else{
                    diurnasExtrasSlider.value = extrasDiurnas ? extrasDiurnas : 0
                    diurnasExtrasValue.value = extrasDiurnas ? extrasDiurnas : 0
        
                }
        
        
        
        
        
                nocturnasExtrasSlider.value= extrasNocturnas ? extrasNocturnas : 0
                nocturnasExtrasValue.value= extrasNocturnas ? extrasNocturnas : 0
                
        
            })
        
        
            
        
            final.addEventListener("input",e=>{
                
        
        
                dateTimeInicio = new Date(inicio.value)
                dateTimeFinal = new Date(final.value)
                
                valorOrdinarias = ((dateTimeFinal.getTime() - dateTimeInicio.getTime())/(3600000)) - alm
        
        
                if(dateTimeFinal.getHours()<=21 && dateTimeFinal.getHours() >6){
                    extrasDiurnas = valorOrdinarias - 8
                }
        
        
                if(dateTimeFinal.getHours()>21 || (dateTimeFinal.getHours() <= 6 && dateTimeFinal.getDay() === dateTimeInicio.getDay() + 1)){
                    
        
                    extrasDiurnas = 5 - alm
        
                    extrasNocturnas = valorOrdinarias - 11
        
        
        
        
                }  
        
                if(valorOrdinarias >8){
                    diurnasOrdinariasSlider.value = 8
                    diurnasOrdinariasValue.value = 8
                }else{
                    diurnasOrdinariasSlider.value = valorOrdinarias ? valorOrdinarias:0
                    diurnasOrdinariasValue.value = valorOrdinarias ? valorOrdinarias:0
        
                }
        
        
        
                if (extrasDiurnas < 0) {
                    diurnasExtrasSlider.value = 0
                    diurnasExtrasValue.value =  0
                    
                }else{
                    diurnasExtrasSlider.value = extrasDiurnas ? extrasDiurnas : 0
                    diurnasExtrasValue.value = extrasDiurnas ? extrasDiurnas : 0
        
                }
        
        
        
        
        
                nocturnasExtrasSlider.value= extrasNocturnas ? extrasNocturnas : 0
                nocturnasExtrasValue.value= extrasNocturnas ? extrasNocturnas : 0
                
            })
            
        
        
        }
        

    
 
    

}

function confirmForm(){



    const modal = document.querySelector(".modal-confirmacion")

    const confirm = document.querySelector(".confirm-button")
    const deny = document.querySelector(".deny-button")

    const form = document.querySelector("form")
    const buttonCharge  = document.querySelector("#button-charge")


    if(buttonCharge){


        buttonCharge.addEventListener("click",e=>{
            modal.style.display = "flex"
        })

    }



    if(modal){
        
        confirm.addEventListener("click",e=>{

            form.submit()

        })
        
        deny.addEventListener("click",e=>{
            modal.style.display="none"
        })
    }

}
function autoManual(){

}

function pages(){

    const indexContainer= document.querySelector(".index-page")
    
    if (indexContainer) {
        



        const addRemove = indexContainer.querySelector(".add-remove")
        const addButton = addRemove.querySelector(".page-add")
        const removeButton = addRemove.querySelector(".page-remove")


        const pagesIndex = indexContainer.querySelector(".pages")
        const pages = pagesIndex.querySelectorAll(".page-number")


        let pageCount = pages.length


        addButton.addEventListener("click",e=>{



            pageCount ++

            const newIndex = document.createElement("A")
            newIndex.href= `#entrada-${pageCount}`
            newIndex.textContent=pageCount
            newIndex.classList.add("page-number")
            newIndex.id = `pagina-${pageCount}`
            pagesIndex.appendChild(newIndex)


            createPage(pageCount)
            rangeValue()

            
            if(pageCount === 7){
                addButton.disabled = true
            }

            if (pageCount >1) {
                removeButton.disabled = false
            }






        })

        removeButton.addEventListener("click",e=>{



            const pageIndex = document.querySelector("#pagina-"+pageCount)

            pageIndex.remove()
            deletePage(pageCount)

            pageCount--

            if(pageCount < 7){
                addButton.disabled = false
            }

            if (pageCount <2) {
                removeButton.disabled = true
            }



        })





    }



}



function createPage(pageNumber){



    const multiPage = document.querySelector(".container-times")

    const registerPage = multiPage.querySelector(".container-page")


    const newPage = registerPage.cloneNode(true)

    newPage.id = "entrada-" + pageNumber


    const infoPersonas = newPage.querySelector("#info-personas")
    const infoJornada = newPage.querySelector("#info-jornada")
    const registroHoras = newPage.querySelector("#registro-horas")
    const logistica = newPage.querySelector("#logistica")
    const comentarios = newPage.querySelector("#registro-comentarios")





    newPage.querySelector(".subtitle").textContent = "Entrada " + (pageNumber)


    infoPersonas.querySelector("#empleado").setAttribute("name",`horas[${pageNumber-1}][empleado]`)
    
    infoPersonas.querySelector("#cliente").setAttribute("name",`horas[${pageNumber-1}][cliente]`)




    infoJornada.querySelector("#inicio").setAttribute("name",`horas[${pageNumber-1}][inicio]`)
    infoJornada.querySelector("#almuerzo-0").setAttribute("name",`horas[${pageNumber-1}][almuerzo]`)
    infoJornada.querySelector("#almuerzo-1").setAttribute("name",`horas[${pageNumber-1}][almuerzo]`)
    infoJornada.querySelector("#almuerzo-2").setAttribute("name",`horas[${pageNumber-1}][almuerzo]`)
    infoJornada.querySelector("#final").setAttribute("name",`horas[${pageNumber-1}][final]`)


    registroHoras.querySelector("#diurnas_ordinarias").setAttribute("name",`horas[${pageNumber-1}][diurnas_ordinarias]`)
    registroHoras.querySelector("#diurnas_ordinarias_value").setAttribute("name",`horas[${pageNumber-1}][diurnas_ordinarias]`)
    registroHoras.querySelector("#nocturnas_ordinarias").setAttribute("name",`horas[${pageNumber-1}][nocturnas_ordinarias]`)
    registroHoras.querySelector("#nocturnas_ordinarias_value").setAttribute("name",`horas[${pageNumber-1}][nocturnas_ordinarias]`)
    registroHoras.querySelector("#diurnas_extras").setAttribute("name",`horas[${pageNumber-1}][diurnas_extras]`)
    registroHoras.querySelector("#diurnas_extras_value").setAttribute("name",`horas[${pageNumber-1}][diurnas_extras]`)
    registroHoras.querySelector("#nocturnas_extras").setAttribute("name",`horas[${pageNumber-1}][nocturnas_extras]`)
    registroHoras.querySelector("#nocturnas_extras_value").setAttribute("name",`horas[${pageNumber-1}][nocturnas_extras]`)

    logistica.querySelector("#cena").setAttribute("name",`horas[${pageNumber-1}][cena]`)
    logistica.querySelector("#taxi").setAttribute("name",`horas[${pageNumber-1}][taxi]`)
    comentarios.querySelector("#comentarios").setAttribute("name",`horas[${pageNumber-1}][comentarios]`)



    multiPage.appendChild(newPage)
    

    detectandExec()
    scrollToPage()
}



function deletePage(pageNumber){
    
    const multiPage = document.querySelector(".container-times")

    const registerPage = multiPage.querySelector("#entrada-"+pageNumber)


    registerPage.remove()
}



function scrollToPage(){

    const indexes = document.querySelectorAll(".page-number")



    indexes.forEach(index=>{
        index.addEventListener("click",e=>{
            e.preventDefault()


            const pageScroll = e.target.getAttribute("href")



            const page = document.querySelector(pageScroll)

            page.scrollIntoView({behavior: "smooth"})

            
        })
    })


}

function dragNdrop(){

    const dragable = document.querySelector(".dragable")

    let startX = 0
    let startY = 0
    let newX = 0
    let newY = 0

    if(dragable){
        dragable.addEventListener("mousedown",mouseDown)

    }


    function mouseDown(e){
        startX = e.clientX
        startY = e.clientY
    
        
        document.addEventListener("mousemove",mouseMove)
        document.addEventListener("mouseup",mouseUp)
    
    }
    
    
    function mouseMove(e){
        
        newX = startX - e.clientX
        newY = startY - e.clientY
    
    
        startX = e.clientX
        startY = e.clientY
    
        dragable.style.top = (dragable.offsetTop - newY) + "px"
        dragable.style.left = (dragable.offsetLeft - newX) + "px"
    
    }


    function mouseUp(e){
        document.removeEventListener("mousemove",mouseMove)
    }

}
function detectandExec() {
    
    const pages = []
    let i =1
    let actualPage

    while(true){
        actualPage = document.querySelector("#entrada-" + i)

        if(actualPage == null){
            break
        }

        calculate(actualPage)


        i++

    }




}




document.addEventListener("DOMContentLoaded",e=>{
    EventListenters()
})