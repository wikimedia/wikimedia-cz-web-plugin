document.addEventListener("DOMContentLoaded", () => {
    document.querySelector("#wmcz-events-next-month").hidden = true;

    document.querySelectorAll('.wmcz-events-control').forEach(( element ) => {
        element.addEventListener( 'click', ( e ) => {
            let el = e.srcElement;
            let targetId = el.id.replace('-control', '');
            document.querySelectorAll( '.wmcz-events-set' ).forEach( ( element ) => {
                element.hidden = element.id != targetId;
            } );
        } );
    });
});