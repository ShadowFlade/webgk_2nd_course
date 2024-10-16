document.addEventListener('DOMContentLoaded', () => {

    const tabsEl = document.querySelectorAll('.js-register__tabs');
    const tabs = document.querySelectorAll('.js-register__tab');
    const physFields = document.querySelectorAll('.js-phys-field input');
    physFields.forEach(field => {field.setAttribute('required', '');});
    const jurFields = document.querySelectorAll('.js-jur-field input');
    const formWrapper = document.querySelector('.js-reg-form');
    const form = formWrapper.querySelector('form');
    const typeElHidden = document.querySelector("[name='REGISTER[UF_TYPE]']");
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            tabs.forEach(tab => tab.classList.remove('active'));
            e.target.classList.add('active');
            const target = e.target;
            if (!formWrapper) return;

            if (target.dataset.type == 'phys') {
                formWrapper.classList.add('reg-form__active--active-phys');
                formWrapper.classList.remove('reg-form__active--active-jur');
                typeElHidden.value = "PHYSICAL";
                jurFields.forEach(field => {field.removeAttribute('required');});
                physFields.forEach(field => {field.setAttribute('required', '');});



            } else if (target.dataset.type == 'jur') {
                formWrapper.classList.remove('reg-form__active--active-phys');
                formWrapper.classList.add('reg-form__active--active-jur');
                typeElHidden.value = "JURIDICAL";
                physFields.forEach(field => {field.removeAttribute('required');});
                jurFields.forEach(field => {field.setAttribute('required', '');});
            }
        })
    })


    function captchaExecute() {
        return new Promise((resolve, reject) => {
            window.grecaptcha.execute('6LfmoCkqAAAAAAcbZpEg4WcicDd_Q5CW19KkSATL', {action: 'call_your_mom'}).then(function (token) {
                resolve(token);
            }).catch(error => {
                reject(error);
            });
        });
    }

    form && form.addEventListener('submit', (e) => {
        e.preventDefault();
        window.captchaExecute = captchaExecute;
        let data = new FormData(form);

        data.set('REGISTER[UF_TYPE]', typeElHidden.value); //TODO why do we need to do this and why its not being updated in DOM properly?

        captchaExecute().then(async (token) => {

            data.append('recaptcha_response', token);
            const resp = await fetch(window.endpoints.registerUser, {
                method: 'POST',
                body: data
            })
            const jData = await resp.json();
            if (jData.redirect) {
                window.location.replace('/auth')
            }
        });

    });

    function appendFormData(form, data) {
        if (form.dataset.appendForm) {
            const appendForm = document.querySelector(`#${form.dataset.appendForm}`);

            const appendData = new FormData(appendForm);


            [...appendData.entries()].forEach(item => {
                data.append(item[0], item[1])
            })


            return data;
        }

        return data;
    }


})