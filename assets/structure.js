// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';

// start the Stimulus application
import './bootstrap';


const strModal = document.getElementById("confirm-str-modal");
const cancelBtn = document.querySelector('.cancel-btn');
const cancelModify = document.querySelector('.cancel-mod');
const openStrModal = document.getElementById('open-str-modal');

window.onload = ()=>{
    const checkBoxes = document.querySelectorAll('.my-checkbox');
    const status = document.querySelector('.status');

    // Ajout des classes CSS pour les boutons switch
    checkBoxes.forEach(checkBox => {
        const parent = checkBox.parentNode;
        parent.classList.add("form-check")
        parent.classList.add("form-switch")
        parent.classList.add("col-10")
        parent.classList.add("col-md-6")

        if(status.textContent === "désactivé"){
            checkBox.setAttribute("disabled", "")
            openStrModal.setAttribute("disabled", "")
        }
    })
}



openStrModal.onclick = function (){
    strModal.style.display = "block";
};

const closing = document.querySelector(".closing-svg");
closing.onclick = function() {
    strModal.style.display = "none";
}


window.onclick = function(event) {
    if (event.target == strModal) {
        strModal.style.display = "none";
    }
}

cancelModify.onclick = function() {
    strModal.style.display = "none";
}

const statusModal = document.getElementById("status-modal");
const activateModal = document.getElementById("activate-modal");

// affichage modal de confirmation désactivation et activation
statusModal.onclick = function (){
    activateModal.style.display = "block";
}

//Fermer modal
const acSpan = document.querySelector(".ac-closing");

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

const permissionsStrForm = document.getElementById('permissions-modification');

const confirmStrBtn = document.getElementById('confirm-str-btn');

confirmStrBtn.onclick = function (){
    document.getElementById('submit-modify').click();
}

const deleteBtn = document.querySelector('.btn-delete')
const body = document.querySelector("body")

deleteBtn.addEventListener('click', ()=>{
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