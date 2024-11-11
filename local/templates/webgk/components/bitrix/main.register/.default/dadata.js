document.addEventListener('DOMContentLoaded', () => {
    console.log('slkdjflskdjflskdjflk')
    const innInput = document.querySelector('.js-dadata-inn input');
    const kppInput = document.querySelector('.js-dadata-kpp');
    const companyInput = document.querySelector('.js-dadata-company');
    innInput.addEventListener('input', debounce(async (e) => {
        const target = e.target;

        if (target.value.length < 3) {
            return;
        }

        let datalistEl = document.querySelector('.js-dadata-inn__datalist');

        const url = "http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party";
        const token = "fdb81af3ced5de86eab2c072b8b9a152bfdcfd2f";

        const options = {
            method: "POST",
            mode: "cors",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": "Token " + token
            },
            body: JSON.stringify({query: target.value})
        }
        const resp = await fetch(url, options);
        const data = await resp.json();

        if(data.suggestions.length == 0 ) {
            datalistEl.classList.add('hidden');
            return;
        }

        const suggestions = data.suggestions.slice(0,10);
        const pickSuggestion = (target) => {
            target.dataset.company && (companyInput.value = target.dataset.company);
            target.dataset.kpp && (kppInput.value = target.dataset.kpp);
        }

        const suggestionsOptions = suggestions.map((suggestion) => {
            console.log(suggestion,' suggestion');
            const el = document.createElement("option");
            el.value = suggestion.data.inn;
            suggestion.data.kpp && (el.dataset.kpp = suggestion.data.kpp);
            suggestion.value && (el.dataset.company = suggestion.value);
            el.textContent = `ИНН: ${suggestion.data.inn} Компания: ${suggestion.value || ''} <br/> КПП: ${suggestion.data.kpp || ''} `;

            pickSuggestion(el);
            return el
        })
        datalistEl.append(...suggestionsOptions);


    },1000))


})

function debounce(callee, timeoutMs) {
    return function perform(...args) {
        let previousCall = this.lastCall

        this.lastCall = Date.now()

        if (previousCall && this.lastCall - previousCall <= timeoutMs) {
            clearTimeout(this.lastCallTimer)
        }

        this.lastCallTimer = setTimeout(() => callee(...args), timeoutMs)
    }
}