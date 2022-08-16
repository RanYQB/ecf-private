// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';

// start the Stimulus application
import './bootstrap';


window.onload = ()=>{
    const checkBoxes = document.querySelectorAll('.my-checkbox');

    checkBoxes.forEach(checkBox => {
        const parent = checkBox.parentNode;
        parent.classList.add("form-check")
        parent.classList.add("form-switch")
        parent.classList.add("col-6")
    })
}

// Get the modal
const modal = document.getElementById("confirm-modal");

const openModal = document.getElementById('open-modal');

openModal.onclick = function (){
    modal.style.display = "block";
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

const permissionsForm = document.getElementById('permissions-modify');

const confirmBtn = document.getElementById('confirm-btn');

confirmBtn.onclick = function (){
    document.getElementById('submit-form').click();
}

