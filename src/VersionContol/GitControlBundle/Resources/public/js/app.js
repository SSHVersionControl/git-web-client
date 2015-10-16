

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
            $submitButton.each(function(){
                var loadingText = $(this).data('loading-text');
                if(!loadingText){
                    loadingText  = 'Loading...';
                }
                $(this).text(loadingText)//.attr("disabled", true);
            });
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
      
      /**
       * Form Controls
       */
      
    $('.curriculum').each(function(index ){ 
        var $collectionHolder = $(this);
        $collectionHolder.data('index',$collectionHolder.find('.box').length);
        
        var $addTagLink = $('<a href="#" class="add_tag_link btn btn-link btn-small" id="link-'+index+'"><i class="fa fa-add"></i>Add a new environment</span></a>');               
        var $newLink = $('<div class="add_new_wrapper"></div>').append($addTagLink);
        
        $collectionHolder.append($newLink);
        
        $addTagLink.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            addCurriculumForm($collectionHolder,$newLink);
        });
    });
   

    function addCurriculumForm($collectionHolder,$newLink) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');

        // get the new index
        var index = $collectionHolder.data('index');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<div class="box curriculumbox" data-index="'+index+'"></div>').append(newForm);
        //$collectionHolder.append($newFormLi);
        $newLink.before($newFormLi);
        
    }
    
    /**
     * Delete Button for collections
     */
    $('body').on('click','.remove',function(event){
        event.preventDefault();
        $(this).closest('.box').remove();
    });

});