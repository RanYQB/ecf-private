
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/read.scss';

// start the Stimulus application
import './bootstrap';

const structuresAcc = document.querySelectorAll(".str-accordion");

structuresAcc.forEach( structureAcc =>{
    structureAcc.addEventListener("click", function() {

        this.classList.toggle("str-active");

        const arrow = structureAcc.querySelector('.arrow-down');
        const strName = structureAcc.querySelector('.structure-name');

        arrow.classList.toggle("blue-fill");
        strName.classList.toggle('blue-color')

        const panel = this.nextElementSibling;

        if (panel.style.display === "block") {
            panel.style.display = "none";

        } else {
            panel.style.display = "block";

        }
    })
})

