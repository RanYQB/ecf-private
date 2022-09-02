
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/login.scss';

// start the Stimulus application
import './bootstrap';



window.onload = ()=>{
    const inputs = document.querySelectorAll('.form-control');
    inputs[0].setAttribute("placeholder", "nouveau mot de passe");
    inputs[1].setAttribute("placeholder", "confirmation du mot de passe");
    inputs.forEach(input =>{
        input.style.marginBottom = ".4rem"
        const parent = input.parentNode;
        parent.style.width = "100%"
    })

}
