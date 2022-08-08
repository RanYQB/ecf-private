
// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

window.onload = () => {
    // const filters = document.querySelector('#filters');
    const filtersList = document.querySelector('#filter-select');

    filtersList.addEventListener('change', (event)=>{

        //const form = new FormData();
        //form.set('filtre', event.target.value );

        const parameters = new URLSearchParams;
        parameters.set('filtre', event.target.value)

        console.log(parameters.toString())

        const url = new URL(window.location.href);

        fetch(url.pathname + "?" + parameters.toString() + "&ajax=1",{
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(response => response.json()).then(data =>{
                const content = document.getElementById('content');
                content.innerHTML = data.content;
        })
            .catch(error => alert(error))
    });
}
