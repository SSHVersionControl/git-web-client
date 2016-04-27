
$(function(){
    $(document).ajaxStart(function() { Pace.restart(); });
    
    var $contentContainter = $('.content-wrapper');
    
    $('.sidebar-menu a').on('click',function(e){

        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            $contentContainter.mask({label:'Loading...'});
            
            $contentContainter.load( this.href,function(){
                $contentContainter.unmask();
            });  
        }
    });
    
    $contentContainter.on('click','a',function(e){
        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            $contentContainter.mask({label:'Loading...'});
            $contentContainter.load( this.href,function(){
                $contentContainter.unmask();
            });  
        }
    });
    
    
    $contentContainter.on('submit', 'form', function (e) {

        e.preventDefault();

        $contentContainter.mask({label:'Form Submitted...'});
         
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: "html",
            success: function(data) {
                $contentContainter.html(data);
                $contentContainter.unmask();
            },
            error: function(e) 
            {
                $contentContainter.html(e);
                $contentContainter.unmask();
            }
        });
    });
    
    /**
     * Refresh Icon in side navigation
     */
    $('#refresh-nav').on('click',function(e){
       e.preventDefault();
       $(this).trigger('status-refresh');
    });
    
    $('#refresh-nav').on('status-refresh',function(){
        Pace.ignore(function(){
            $.getJSON( $(this).attr('href'), function( data ) {

                console.log(data);

                $.each( data, function( id, val ) {
                  $('#'+id).html(val);
                });

            });
        });
    });

});