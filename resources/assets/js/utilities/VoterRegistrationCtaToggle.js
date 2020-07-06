const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to action on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const confirmedContent = document.getElementById('voter-reg-cta-confirmed');
    const uncertainContent = document.getElementById('voter-reg-cta-uncertain');
    const unregisteredContent = document.getElementById('voter-reg-cta-unregistered');
    const value = event.target.value

    if(value === 'unregistered') {
        unregisteredContent.classList.remove('hidden')
        uncertainContent.classList.add('hidden')
        confirmedContent.classList.add('hidden')
    } else if(value === 'uncertain') {
        uncertainContent.classList.remove('hidden')
        unregisteredContent.classList.add('hidden')
        confirmedContent.classList.add('hidden')
    } else if(value === 'confirmed') {
        confirmedContent.classList.remove('hidden')
        unregisteredContent.classList.add('hidden')
        uncertainContent.classList.add('hidden')
    } else {
        unregisteredContent.classList.add('hidden');
        uncertainContent.classList.add('hidden');
        confirmedContent.classList.add('hidden')
    }

}

const init = () => {
    $(document).ready(() => {
        const voterRegStatusInputs = document.getElementsByClassName('voter-reg-status')
        voterRegStatusInputs.forEach(inputField => {
            inputField.addEventListener('click', clickHandlerToggleContent)
        })
      });
}

export default { init };