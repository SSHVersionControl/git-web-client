/**
 * Intercepts all links and forms requests in the content 
 * section to call them using ajax. To exclude a link 
 * from been called by ajax set a class of "non-ajax" 
 */
$(function(){
    
    var firstLoad = false;
    
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

            loadUrl($contentContainter,this.href,loadingText);  
        }
    });
    
    /* Ajaxify all links in container*/
    $contentContainter.on('click','a',function(e){
        if($(this).hasClass('non-ajax') == false){
            e.preventDefault();
            var loadingText = 'Loading...';
            if($(this).data('masklabel')){
                loadingText = $(this).data('masklabel');
            }

            loadUrl($contentContainter,this.href,loadingText); 
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
        //loadUrl($(this).attr('action'));
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: "html",
            success: function(data, textStatus, jqXHR) {
                $contentContainter.html(data);
                $contentContainter.unmask();
                 console.log(textStatus);
                 console.log(jqXHR);
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
        
         $.ajax({
            url: $(this).attr('href'),
            dataType: "json",
            success: function(data) {
                $.each( data, function( id, val ) {
                    var $label = $('#'+id);
                    if(val == 0){
                        $label.addClass('hide');
                    }else{
                       $label.removeClass('hide'); 
                    }
                    $label.html(val);
                });
            },
            error: function(e) 
            {
                 console.log(e);
            }
        });
    });
    
    function loadUrl($element,url,loadingText){
        
        $element.mask({label:loadingText});
        setUrlHistory(url);
        $element.load( url,function(){
            $element.unmask();
        }); 
        
        //Side Navigation state
        $('.sidebar-menu a').each(function(){
            $this = $(this);
            if( url.indexOf(($(this).attr('href'))) >= 0){
                var $parentUl = $(this).parent('li').addClass('active').parent('ul');
                if($parentUl.hasClass('treeview-menu')){
                    $parentUl.closest('li').addClass('active');
                }
            }else{
                $(this).parent('li').removeClass('active');
            }
        });
    }
    
    function setUrlHistory(url){
        if(url!=window.location){
            //add the new page to the window.history
            //if the new page was triggered by a 'popstate' event, don't add it
            window.history.pushState({path: url},'',url);
        }
    }
    
    //detect the 'popstate' event - e.g. user clicking the back button
    $(window).on('popstate', function() {
        if( firstLoad ) {
            /*
            Safari emits a popstate event on page load - check if firstLoad is true before animating
            if it's false - the page has just been loaded 
            */
           //console.log(location.pathname);
            //var newPageArray = location.pathname.split('/'),
            //this is the url of the page to be loaded 
            //newPage = newPageArray[newPageArray.length - 1].replace('.html', '');
            //if( !isAnimating ) triggerAnimation(newPage, false);
        }
        firstLoad = true;
    });
    
    //Open Default page
    loadUrl( $contentContainter,defaultPage,'Loading...');

});