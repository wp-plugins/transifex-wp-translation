/**
 * Wrapper function to safely use $
 * @author Mucunguzi Ayebare Brooks
 */

function txwt_main($) {
    var txwt = {
        /**
         * Main entry point
         * @author Mucunguzi Ayebare 
         */
        init: function () {
            txwt.prefix = 'txwt_';
            txwt.PageStart();
            txwt.EventHandlers();
        },

        EventHandlers: function () {
            Transifex.live.onFetchLanguages(txwt.onFetchLanguages);
            Transifex.live.onTranslatePage(txwt.OnTranslatePage);
            Transifex.live.onDynamicContent(txwt.OnDynamicContent);
            Transifex.live.onError(OnError);
        },
		
        PageStart: function () {
            $('.transln_method input').hide();
        },
		
        onFetchLanguages: function(languages){
            //get the source language 
               var sourceLang=  Transifex.live.getSourceLanguage().code;
               
            //empty our language list
            $('#post_langs_list').empty();

            //add translation languages to the list
            for (var i = 0; i < languages.length; ++i) {
                $('#post_langs_list').append(
                    '<li data-code="' + languages[i].code +
                    '" data-name="' + languages[i].name +
                    '"><a href="#">' + languages[i].name + '</a></li>'
                    );
            }

            //handle user selecting a language
            $('#post_langs_list').find('li').click(function(e) {
                e && e.preventDefault();
                var code = $(this).closest('[data-code]').data('code');
                var name = $(this).closest('[data-code]').data('name');

                //tell transifex live to translate the page
                //based on user selection
                Transifex.live.translateTo(code, true);
            });

           
        },
		
        OnTranslatePage: function(language_code){
			
        },
		
        OnDynamicContent: function (new_string){
			
        },
        
        OnError: function(err) {

        }
       

    }; // end txwt
  
        Transifex.live.onReady(txwt.init);
   
} // end txwt_main()

if (typeof Transifex != 'undefined'){ 
    txwt_main(jQuery);
}