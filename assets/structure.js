// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';
import './styles/structure.scss';

// start the Stimulus application
import './bootstrap';


const strModal = document.getElementById("confirm-str-modal");
const cancelBtn = document.querySelector('.cancel-btn');
const cancelModify = document.querySelector('.cancel-mod');
const openStrModal = document.getElementById('open-str-modal');

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
            openStrModal.setAttribute("disabled", "")
        }
    })
}


// Get the modal
openStrModal.onclick = function (){
    strModal.style.display = "block";
};

const closing = document.querySelector(".closing-svg");
// When the user clicks on <span> (x), close the modal
closing.onclick = function() {
    strModal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
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

// affichage modal de confirmation désactivation et activation des comptes partenaires
statusModal.onclick = function (){
    activateModal.style.display = "block";
}

//Fermer modal pour l'activation et la désactivation des comptes partenaires
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