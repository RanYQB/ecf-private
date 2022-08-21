// any CSS you import will output into a single css file (app.scss in this case)
import './styles/partner.scss';

// start the Stimulus application
import './bootstrap';

const modal = document.getElementById("confirm-modal");

const openModal = document.getElementById('open-modal');

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

const span = document.querySelector(".closing");
span.onclick = function() {
    modal.style.display = "none";
}

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
