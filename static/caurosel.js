function setCauroselText( cauroselId, index ) {
    let menuEl = document.querySelector(`.wmcz-caurosel-menu-dot[data-index="${index}"][data-caurosel-id="${cauroselId}"]`);
    let menuAllEl = document.querySelectorAll(`.wmcz-caurosel-menu[data-caurosel-id="${cauroselId}"] .wmcz-caurosel-menu-dot`);
    menuAllEl.forEach(( el ) => {
        el.classList.remove('wmcz-caurosel-menu-dot-active');
    });
    menuEl.classList.add('wmcz-caurosel-menu-dot-active');

    let cauroselEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"]`);
    let headlineEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"] h2`);
    let descriptionEl = document.querySelector(`.wmcz-caurosel-right-colored[data-caurosel-id="${cauroselId}"] p`);
    headlineEl.innerText = JSON.parse(cauroselEl.getAttribute('data-headlines'))[index];
    descriptionEl.innerText = JSON.parse(cauroselEl.getAttribute('data-descriptions'))[index];
    
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.wmcz-caurosel-menu .wmcz-caurosel-menu-dot').forEach( ( menuEl ) => {
        menuEl.addEventListener('click', ( e ) => {
            let cauroselId = e.srcElement.getAttribute('data-caurosel-id');
            setCauroselText(cauroselId, e.srcElement.getAttribute('data-index'));
        });
    } );
    
});