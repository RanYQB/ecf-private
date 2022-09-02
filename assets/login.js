
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/login.scss';

// start the Stimulus application
import './bootstrap';

window.onload = ()=>{
    const resetInput = document.querySelector('.reset-input');

    const parent = resetInput.parentNode;
        parent.classList.add("form-inputs")
}
