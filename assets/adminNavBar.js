
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/adminNavBar.scss';

// start the Stimulus application
import './bootstrap';

const showMenu = ()=>{
    const menuBtn = document.querySelector('.buttons')
    const menu = document.querySelector('.side-bar')
    const body = document.querySelector('body')

    menuBtn.addEventListener('click', ()=>{
        menu.classList.toggle('menu-clicked')
        body.classList.toggle('open')}
    )
}

showMenu()

const navAccordions = document.querySelectorAll(".nav-accordion");

navAccordions.forEach( navAccordion =>{
    navAccordion.addEventListener("click", function() {

        this.classList.toggle("nav-active");

        const panel = this.nextElementSibling;

        if (panel.style.display === "block") {
            panel.style.display = "none";

        } else {
            panel.style.display = "block";

        }
    })
})


