


function EventListenters(){

    modals()
    rangeValue()
    requestHours()
    activateUpdates()

    changeForm()
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


document.addEventListener("DOMContentLoaded",e=>{
    EventListenters()
})