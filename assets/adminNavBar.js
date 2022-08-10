
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
