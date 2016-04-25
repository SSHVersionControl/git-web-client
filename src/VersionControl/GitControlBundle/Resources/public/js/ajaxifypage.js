
$(function(){
    $(document).ajaxStart(function() { Pace.restart(); });
    
    var $contentContainter = $('.content-wrapper');
    
    $('.sidebar-menu a').on('click',function(e){

        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            $contentContainter.load( this.href,function(){

            });  
        }
    });
    
    $contentContainter.on('click','a',function(e){
        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            $contentContainter.load( this.href,function(){

            });  
        }
    });
    
    
    $contentContainter.on('submit', 'form', function (e) {

        e.preventDefault();

        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: "html",
            success: function(data) {
                $contentContainter.html(data);
            },
            error: function(e) 
            {
                $contentContainter.html(e);
            }
        });
    });

});