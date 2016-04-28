/**
 * Intercepts all links and forms requests in the content 
 * section to call them using ajax. To exclude a link 
 * from been called by ajax set a class of "non-ajax" 
 */
$(function(){
    
    //Enables PaceJs (http://github.hubspot.com/pace/docs/welcome/)
    $(document).ajaxStart(function() { Pace.restart(); });
    
    /* Ajaxifed container */
    var $contentContainter = $('.content-wrapper');
    
    /* Side menu links */
    $('.sidebar-menu a').on('click',function(e){

        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            var loadingText = 'Loading...';
            if($(this).data('masklabel')){
                loadingText = $(this).data('masklabel');
            }
            $contentContainter.mask({label:loadingText});
            
            $contentContainter.load( this.href,function(){
                $contentContainter.unmask();
            });  
        }
    });
    
    /* Ajaxify all links in container*/
    $contentContainter.on('click','a',function(e){
        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            $contentContainter.mask({label:'Loading...'});
            $contentContainter.load( this.href,function(){
                $contentContainter.unmask();
            });  
        }
    });
    
    /* Ajaxify all Forms in container*/
    $contentContainter.on('submit', 'form', function (e) {

        e.preventDefault();
        var loadingText = 'Submitting Form...';
        if($(this).data('masklabel')){
            loadingText = $(this).data('masklabel');
        }

        $contentContainter.mask({label:loadingText});
         
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
     * Side menu refresh menu link
     */
    $('#refresh-nav').on('click',function(e){
       e.preventDefault();
       $(this).trigger('status-refresh');
    });
    
    /* Trigger for side menu refresh status */
    $('#refresh-nav').on('status-refresh',function(){
        //Pace.ignore(function(){
            $.getJSON( $(this).attr('href'), function( data ) {

                console.log(data);

                $.each( data, function( id, val ) {
                    var $label = $('#'+id);
                    if(val == 0){
                        $label.addClass('hide');
                    }else{
                       $label.removeClass('hide'); 
                    }
                    $label.html(val);
                });

            });
        //});
    });

});