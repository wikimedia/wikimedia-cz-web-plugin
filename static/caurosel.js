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
    let imageEl = document.querySelector(`.wmcz-caurosel-left[data-caurosel-id="${cauroselId}"] img`);
    let imageLinkEl = document.querySelector(`.wmcz-caurosel-left[data-caurosel-id="${cauroselId}"] a`);
    let headlineRawText = JSON.parse(cauroselEl.getAttribute('data-headlines'))[index];
    let headlineInnerHtml = headlineRawText;
    let link = JSON.parse(cauroselEl.getAttribute('data-links'))[index];
    if ( link != "" ) {
        headlineInnerHtml = `<a href="${link}">${headlineRawText}</a>`;
    }
    headlineEl.innerHTML = headlineInnerHtml;
    descriptionEl.innerText = JSON.parse(cauroselEl.getAttribute('data-descriptions'))[index];
    if (link != "") {
        imageLinkEl.setAttribute( 'href', link );
    } else {
        imageLinkEl.setAttribute( 'href', '#' );
    }

    let image = JSON.parse(cauroselEl.getAttribute('data-images'))[index];
    if (image) {
        imageEl.setAttribute('src', image);
    } else {
        imageEl.setAttribute('src', 'https://upload.wikimedia.org/wikipedia/commons/8/84/Example.svg');
    }
    
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.wmcz-caurosel-menu .wmcz-caurosel-menu-dot').forEach( ( menuEl ) => {
        menuEl.addEventListener('click', ( e ) => {
            let cauroselId = e.srcElement.getAttribute('data-caurosel-id');
            setCauroselText(cauroselId, e.srcElement.getAttribute('data-index'));
        });
    } );
    
});