


function EventListenters(){

    modals()
    rangeValue()
    requestHours()
    activateUpdates()
    calculate()
    changeForm()
    confirmForm()
    dragNdrop()
    pages()
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

    const ranges = document.querySelectorAll("input[type='range']")
    const numbers = document.querySelectorAll("input[type='number']")

    if(ranges.length >0 && numbers.length>0){
        ranges.forEach(range=>{
            range.addEventListener("input",e=>{
                const number = document.querySelector(`#${range.id}_value`)
                number.value = e.target.value
            })
        })

        numbers.forEach(number=>{
            number.addEventListener("input",e=>{
                const range = document.querySelector(`#${number.id.replace("_value","")}`)
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


function calculate() {
    

    const inicio = document.querySelector("#inicio")
    const final = document.querySelector("#final")
    const radioAlm = document.querySelectorAll(".radio-input")
    const diurnasOrdinariasSlider = document.querySelector("#diurnas_ordinarias")
    const diurnasOrdinariasValue = document.querySelector("#diurnas_ordinarias_value")
    const nocturnasOrdinariasSlider = document.querySelector("#nocturnas_ordinarias")
    const nocturnasOrdinariasValue = document.querySelector("#nocturnas_ordinarias_value")
    const diurnasExtrasSlider = document.querySelector("#diurnas_extras")
    const diurnasExtrasValue = document.querySelector("#diurnas_extras_value")
    const nocturnasExtrasSlider = document.querySelector("#nocturnas_extras")
    const nocturnasExtrasValue = document.querySelector("#nocturnas_extras_value")

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
        
        diurnasOrdinariasSlider.value = valorOrdinarias ? valorOrdinarias:0
        diurnasOrdinariasValue.value = valorOrdinarias ? valorOrdinarias:0
        diurnasExtrasSlider.value = extrasDiurnas ? extrasDiurnas : 0
        diurnasExtrasValue.value = extrasDiurnas ? extrasDiurnas : 0
        nocturnasExtrasSlider.value= extrasNocturnas ? extrasNocturnas : 0
        nocturnasExtrasValue.value= extrasNocturnas ? extrasNocturnas : 0
    
    
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

    const indexContainer= document.querySelector("#index-page")

    if (indexContainer) {
        
        const addRemove = indexContainer.querySelector(".add-remove")
        const addButton = addRemove.querySelector(".page-add")
        const removeButton = addRemove.querySelector(".page-remove")


        const pagesIndex = indexContainer.querySelector(".pages")
        const pages = pagesIndex.querySelectorAll(".pages-number")


        addButton.addEventListener("click",e=>{
            let pageCount = pages.length


            const newPage = document.createElement("A")
            newPage.href= `#entrada-${pageCount+1}`
            newPage.textContent=pageCount+1
            console.log(newPage)
            pagesIndex.appendChild(newPage)

        })





    }



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




document.addEventListener("DOMContentLoaded",e=>{
    EventListenters()
})