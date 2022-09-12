
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';
// start the Stimulus application
import './bootstrap';



const flashElSuccess = document.querySelectorAll('.flash-el-success')
const flashElDanger = document.querySelectorAll('.flash-el-danger')


//flashMessageDanger.addEventListener('click', remove)
//flashMessage.addEventListener('click', remove)

flashElSuccess.forEach(flashElement =>{
        flashElement.addEventListener('click', ()=>{
            const flashMessage = document.querySelector('.flash-messages')
            flashMessage.style.display = "none"
        })
})

flashElDanger.forEach(flashElement =>{
    flashElement.addEventListener('click', ()=>{
        const flashMessageDanger = document.querySelector('.flash-danger-messages')
        flashMessageDanger.style.display = "none"
    })
})

