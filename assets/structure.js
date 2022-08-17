// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';
import './styles/structure.scss';

// start the Stimulus application
import './bootstrap';


const strModal = document.getElementById("confirm-str-modal");

const openStrModal = document.getElementById('open-str-modal');

window.onload = ()=>{
    const checkBoxes = document.querySelectorAll('.my-checkbox');
    const status = document.querySelector('.status');


    checkBoxes.forEach(checkBox => {
        const parent = checkBox.parentNode;
        parent.classList.add("form-check")
        parent.classList.add("form-switch")
        parent.classList.add("col-6")

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

const span = document.querySelector(".closing");
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

const permissionsStrForm = document.getElementById('permissions-modification');

const confirmStrBtn = document.getElementById('confirm-str-btn');

confirmStrBtn.onclick = function (){
    document.getElementById('submit-modify').click();
}