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
});