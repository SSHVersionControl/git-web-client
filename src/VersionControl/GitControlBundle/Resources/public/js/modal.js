var GlobalModal = function () {

    return {

        /**
         * Makes a link open in a modal. Link must contain data-form-modal.
         * If you want to set the header of modal add data-header="" to link
         * It also ajaxify a form
         * 
         * @returns {undefined}
         */
        initModal : function(){
            var self = this;
            //Sets Popup Modal for links with data-form-modal
            $('body').on('click', 'a[data-form-modal]', function (ev) {
                ev.preventDefault();
                var href = $(this).attr('href')
                        , confirmHeader = $(this).data('header') ? $(this).data('header') : 'Submit Form'
                        , size = $(this).data('modal-large') ? true : false;
                        
                self.triggerModalOpen(href,confirmHeader,size);
                
                return false;
            });

            var $contentContainter = $('body');

            $contentContainter.on('submit', 'form.ajaxify', function (e) {
                e.preventDefault();
                var loadingText = 'Submitting Form...';
                if ($(this).data('masklabel')) {
                    loadingText = $(this).data('masklabel');
                }

                $contentContainter.mask({label: loadingText});

                var $form = $(this);
                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: new FormData(this),
                    dataType: "json",
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    cache: false,
                    success: function (data) {
                        console.log(data);
                        if(data.redirect){
                            location = data.redirect;
                        }else if(data.modalContent){
                            $('#dataFormModal .form-content').html(data.modalContent);
                        }else{
                            location.reload();
                        }
                        
                        //$contentContainter.unmask();
                        //$(".modal.in").modal('hide');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (typeof jqXHR.responseJSON !== 'undefined') {
                            if (jqXHR.responseJSON.hasOwnProperty('form')) {
                                $('#dataFormModal .form-content').html(jqXHR.responseJSON.form);
                            }
                            //$('.form_error').html(jqXHR.responseJSON.message);
                        } else {
                            alert(errorThrown);
                        }
                        $contentContainter.unmask();

                    }
                });
            });

        }
        
        ,triggerModalOpen : function(href,header,large){
            $(".modal.in").modal('hide');
            
            var sizeClass = large?'modal-lg':'';
            
            $('#dataFormModal').remove();
                if (!$('#dataFormModal').length) {
                    $('body').append('<div id="dataFormModal" class="modal " role="dialog" aria-labelledby="dataFormLabel" aria-hidden="true">\n\
                    <div class="modal-dialog '+sizeClass+'" role="document">\n\
                        <div class="modal-content">\n\
                            <div class="modal-header">\n\
                                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>\n\
                                <h4 id="dataFormHeader" class="modal-title">' + header + '</h4>\n\
                            </div>\n\
                            <div class="form-content">\n\
                                <div class="modal-body">Loading...</div>\n\
                            </div>\n\
                        </div>\n\
                    </div></div>');
                }
                $('#dataFormModal').find('.form-content').load(href,function(){
                    
                });
                
            $('#dataFormModal').modal({show: true});
        }

    };

}();


