document.addEventListener('DOMContentLoaded', () => {

    const tabsEl = document.querySelectorAll('.js-register__tabs');
    const tabs = document.querySelectorAll('.js-register__tab');
    const physFields = document.querySelectorAll('.js-phys-field input');
    physFields.forEach(field => {
        field.setAttribute('required', '');
    });
    const jurFields = document.querySelectorAll('.js-jur-field input');
    const formWrapper = document.querySelector('.js-reg-form');
    const form = formWrapper.querySelector('form');
    const typeElHidden = document.querySelector("[name='REGISTER[UF_TYPE]']");
    const errorEl = document.querySelector(".js-back-error");

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
                jurFields.forEach(field => {
                    field.removeAttribute('required');
                    field.setCustomValidity('');
                });
                physFields.forEach(field => {
                    field.setAttribute('required', '');
                });
                window.user.type = 'phys';

            } else if (target.dataset.type == 'jur') {
                formWrapper.classList.remove('reg-form__active--active-phys');
                formWrapper.classList.add('reg-form__active--active-jur');
                typeElHidden.value = "JURIDICAL";
                physFields.forEach(field => {
                    field.removeAttribute('required');
                    field.setCustomValidity('');
                });
                jurFields.forEach(field => {
                    field.setAttribute('required', '');
                });
                window.user.type = 'jur';
            }
        })
    })


    function captchaExecute() {
        return new Promise((resolve, reject) => {
            window.grecaptcha.execute(window.CAPTCHA_PUBLIC, {action: 'call_your_mom'}).then(function (token) {
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
            if (jData.redirect && !jData.ERROR) {
                hideBackendError()
                window.location.replace('/auth')
            } else if (jData.ERROR) {
                showBackendError(jData.ERROR)
            }
        });

        function showBackendError(errorText) {
            errorEl.innerHTML = errorText;
            errorEl.classList.add('active');
        }

        function hideBackendError() {
            errorEl.innerHTML = "";
            errorEl.classList.remove('active');
        }
    });
})