// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';

// start the Stimulus application
import './bootstrap';

const modal = document.getElementById("confirm-modal");
const activateModal = document.getElementById("activate-modal");
const openModal = document.getElementById('open-modal');
const statusModal = document.getElementById("status-modal");

window.onload = ()=>{
    const checkBoxes = document.querySelectorAll('.my-checkbox');
    const status = document.querySelector('.status');


    checkBoxes.forEach(checkBox => {
        const parent = checkBox.parentNode;
        parent.classList.add("form-check")
        parent.classList.add("form-switch")
        parent.classList.add("col-10")
        parent.classList.add("col-md-6")

        if(status.textContent === "désactivé"){
            checkBox.setAttribute("disabled", "")
            openModal.setAttribute("disabled", "")
        }
    })
}



openModal.onclick = function (){
    modal.style.display = "block";
};

const span = document.querySelector(".md-closing");
const cancelModify = document.querySelector(".cancel-mod");
span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

cancelModify.onclick = function() {
    modal.style.display = "none";
}

// affichage modal de confirmation désactivation et activation des comptes partenaires
statusModal.onclick = function (){
    activateModal.style.display = "block";
}

//Fermer modal pour l'activation et la désactivation des comptes partenaires
const acSpan = document.querySelector(".ac-closing");
const cancelBtn = document.querySelector('.cancel-btn')
acSpan.onclick = function() {
    activateModal.style.display = "none";
}

cancelBtn.onclick = function() {
    activateModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == activateModal) {
        activateModal.style.display = "none";
    }
}



const permissionsForm = document.getElementById('permissions-modify');

const confirmBtn = document.getElementById('confirm-btn');

confirmBtn.onclick = function (){
    document.getElementById('submit-form').click();
}

const acc = document.querySelector(".my-accordion");

acc.addEventListener("click", function() {
        this.classList.toggle("active");
        const panel = this.nextElementSibling;
        if (panel.style.display === "block") {
            panel.style.display = "none";
        } else {
            panel.style.display = "block";
        }
    })


const deleteButton = document.querySelector('.btn-delete')
const body = document.querySelector("body")

deleteButton.addEventListener('click', ()=>{
    const msgDiv = document.createElement("div")
    msgDiv.classList.add('flash-danger-messages')
    msgDiv.classList.add('flash-el-danger')
    msgDiv.style.textAlign = "center"

    const contentDiv = document.createElement("p")
    contentDiv.innerText = "Pour conserver les données, cette fonction n'est pas disponible."
    contentDiv.classList.add("flash-danger-content")
    contentDiv.style.width = "100%"
    msgDiv.classList.add('flash-el-danger')

    msgDiv.appendChild(contentDiv)
    body.appendChild(msgDiv)

    msgDiv.onclick = function (){
        msgDiv.style.display = "none"
    }

})