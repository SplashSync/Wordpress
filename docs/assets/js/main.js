$(document).ready(function() {
	
    /* ===== Stickyfill ===== */
    /* Ref: https://github.com/wilddeer/stickyfill */
    // Add browser support to position: sticky
//    var elements = $('.sticky');
//    Stickyfill.add(elements);


    /* Activate scrollspy menu */
    $('body').scrollspy({target: '#doc-menu', offset: 100});
    
    /* Smooth scrolling */
    $('a.scrollto').on('click', function(e){
        var page = $(this).attr('href');    // Page cible
        var speed = 800;                    // Durée de l'animation (en ms)
        $('html, body').animate( { scrollTop: $(page).offset().top }, speed ); // Go
    });
     
    //------------------------------------------------------------------------------
    // Detect Doc Images
    var collection = $(".lightbox-content img");
    if(collection.length) {
        //------------------------------------------------------------------------------
        // Init All Typed Texts
        collection.each(function(){
            // Extract Image Url
            imgUrl = $( this ).attr('src');
            // Build Lightvox Version
            newHtml = '<div class="screenshot-holder">';
            newHtml+= '<a href="' + imgUrl + '" data-toggle="lightbox"><img class="img-fluid" src="' + imgUrl + '" /></a>';
            newHtml+= '<a class="mask" href="' + imgUrl + '" data-toggle="lightbox"><i class="icon fa fa-search-plus"></i></a>';
            newHtml+= '</div>';
            // Replace Image By Lightbox Version
            $( this ).replaceWith(newHtml);
                
        });
    }
    
    /* Bootstrap lightbox */
    /* Ref: http://ashleydw.github.io/lightbox/ */
    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(e) {
        e.preventDefault();
        $(this).ekkoLightbox();
    });    

    /* Datatables */
    var table = $('.datatable').DataTable({
        "autoWidth": false
    });

    table.on( 'draw', function () {
        $('[data-toggle="popover"]').popover();
        $('[data-toggle="tooltip"]').tooltip();
    } );
        
    /* BS4 Popover */
        $('[data-toggle="popover"]').popover();
        $('[data-toggle="tooltip"]').tooltip();
    
    /* Pretty Print for Json Contents */
    $( ".pretty-json" ).each(function( index ) {
        console.log($( this ).html());
        
        $( this ).html(JSON.stringify(JSON.parse($( this ).html())));
    });
        
});