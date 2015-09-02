

$(function(){
    $('#commit-select-all').on('click',function(){
        $('.commit-file').prop( "checked", true );
    });

    $('#commit-deselect-all').on('click',function(){
        $('.commit-file').prop( "checked", false );
    });
    
    $('form').submit(function(e){
        if( $(this).hasClass('form-submitted') ){
          e.preventDefault();
          return;
        }
        $(this).addClass('form-submitted');
        
        var $submitButton = $('button.submit');
        if($submitButton.length > 0){
            var loadingText = $submitButton.data('loading-text');
            if(!loadingText){
                loadingText  = 'Loading...';
            }
            $submitButton.text(loadingText).attr("disabled", true);
        }
    });
    
    $("#versioncontol_gitcontrolbundle_issuelabel_hexColor").pickAColor({
            showSpectrum            : true,
            showSavedColors         : true,
            saveColorsPerElement    : false,
            fadeMenuToggle          : true,
            showHexInput            : true,
            showBasicColors         : true,
            allowBlank              : false,
            inlineDropdown          : true
      });

});