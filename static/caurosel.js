document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.wmcz-caurosel-menu .wmcz-caurosel-menu-dot').forEach( ( menuEl ) => {
        menuEl.addEventListener('click', ( e ) => {
            let cauroselId = e.srcElement.getAttribute('data-caurosel-id');

            let menuAllEl = document.querySelectorAll(`.wmcz-caurosel-menu[data-caurosel-id="${cauroselId}"] .wmcz-caurosel-menu-dot`);
            menuAllEl.forEach(( el ) => {
                el.classList.remove('wmcz-caurosel-menu-dot-active');
            });
            e.srcElement.classList.add('wmcz-caurosel-menu-dot-active');

            let headlineEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"] h2`);
            let descriptionEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"] p`);
            let cauroselEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"]`);

            let index = e.srcElement.getAttribute('data-index');
            let headline = JSON.parse(cauroselEl.getAttribute('data-headlines'))[index];
            let description = JSON.parse(cauroselEl.getAttribute('data-descriptions'))[index];

            headlineEl.innerText = headline;
            descriptionEl.innerText = description;
        });
    } );
    
});