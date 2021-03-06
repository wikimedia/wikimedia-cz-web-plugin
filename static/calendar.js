document.addEventListener("DOMContentLoaded", () => {
    document.querySelector("#wmcz-calendar-next-month").hidden = true;

    document.querySelectorAll('.wmcz-calendar-control').forEach(( element ) => {
        element.addEventListener( 'click', ( e ) => {
            let el = e.srcElement;
            let targetId = el.id.replace('-control', '');
            document.querySelectorAll( '.wmcz-calendar-set' ).forEach( ( element ) => {
                element.hidden = element.id != targetId;
            } );
            document.querySelectorAll('.wmcz-calendar-control').forEach((element) => {
                (el == element) ? element.classList.add('active') : element.classList.remove('active'); 
            });
        });
    });

    document.querySelectorAll('.event-container').forEach(( el ) => {
        el.addEventListener('click', ( e ) => {
            let srcEl = e.srcElement;
            if ( !srcEl.classList.contains('event-container') ) {
                srcEl = srcEl.parentElement;
            }

            let eventId = srcEl.getAttribute('data-event-id');
            let datetimeEl = document.querySelector(`.event-container[data-event-id="${eventId}"] > .event-datetime`);
            let locationEl = document.querySelector(`.event-container[data-event-id="${eventId}"] > .event-location`);
            let descriptionEl  = document.querySelector(`.event-container[data-event-id="${eventId}"] > .event-title`);
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
                modalEl.classList.remove('wmcz-modal-active');
            });
            window.addEventListener('click', (e) => {
                if (e.target == modalEl) {
                    modalEl.classList.remove('wmcz-modal-active');
                }
            });
            document.addEventListener("keydown", function(event) {
                const key = event.key;
                if (key === "Escape") {
                    modalEl.classList.remove('wmcz-modal-active');
                }
            });
            modalContentEl.innerHTML = `<h3>${title}</h3>
            <p>Místo: ${location}</p>
            <p>Datum: ${datetimeStart} - ${datetimeEnd}</p>
            <p>${description}</p>
            `;
            modalContentEl.prepend(closeBtn);
            modalEl.prepend(modalContentEl);
            document.querySelector('body').prepend(modalEl);
        });
    });
});