document.addEventListener("DOMContentLoaded", () => {
    document.querySelector("#wmcz-calendar-next-month").hidden = true;

    document.querySelectorAll('.wmcz-calendar-control').forEach(( element ) => {
        element.addEventListener( 'click', ( e ) => {
            let el = e.srcElement;
            let targetId = el.id.replace('-control', '');
            document.querySelectorAll( '.wmcz-calendar-set' ).forEach( ( element ) => {
                element.hidden = element.id != targetId;
            } );
        } );
    });

    document.querySelectorAll('.event-container').forEach(( el ) => {
        el.addEventListener('click', ( e ) => {
            let eventId = e.srcElement.parentElement.getAttribute('data-event-id');
            let datetimeEl = document.querySelector(`.event-container.event-location-datetime[data-event-id="${eventId}"] > .event-datetime`);
            let locationEl = document.querySelector(`.event-container.event-location-datetime[data-event-id="${eventId}"] > .event-location`);
            let descriptionEl  = document.querySelector(`.event-container.event-description[data-event-id="${eventId}"] > .event-title`);
            let title = descriptionEl.innerText;
            let datetimeStart = datetimeEl.getAttribute('data-start-datetime');
            let datetimeEnd = datetimeEl.getAttribute('data-end-datetime');
            let location = locationEl.getAttribute('data-location');
            let description = descriptionEl.getAttribute('data-description');

            let modalEl = document.createElement('div');
            modalEl.classList.add('wmcz-modal');
            modalEl.classList.add('wmcz-modal-active');
            let modalContentEl = document.createElement('div');
            modalContentEl.classList.add('wmcz-modal-content');
            let closeBtn = document.createElement('span');
            closeBtn.classList.add('wmcz-modal-close');
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', ( e ) => {
                e.srcElement.parentElement.parentElement.classList.remove('wmcz-modal-active');
            });
            modalContentEl.innerHTML = `<h3>${title}</h3>
            <p>Místo: ${location}</p>
            <p>Datum: ${datetimeStart} - ${datetimeEnd}</p>
            <h4>Popisek</h4>
            <p>${description}</p>
            `;
            modalContentEl.prepend(closeBtn);
            modalEl.prepend(modalContentEl);
            document.querySelector('body').prepend(modalEl);
        });
    });
});