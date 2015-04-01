/**
 * Wrapper function to safely use $
 * @author Mucunguzi Ayebare Brooks
 */

function txwt_admin($) {
    var txwt = {
        /**
         * Main entry point
         */
        init: function () {
            txwt.prefix = 'txwt_';
            txwt.templateURL = $('#templateURL').val();

            if (adminpage == 'transifex_page_txwt-plugin_switcher') {
                txwt.PageStart();
                txwt.EventHandlers(); 
            }else if(adminpage == 'toplevel_page_txwt-plugin'){
				txwt.TxInit();
                txwt.EventHandlers(); 
            }
     
        },
		
        PageStart: function(){	
				
            // Color picker
            $('.colorpick').wpColorPicker( {
                change: txwt.wpColorChange,
                clear: txwt.wpColorClear
            } ).each( function() {
                $(this).css( {
                    backgroundColor: $(this).val()
                } );
            });
			
            //lang order
            $( "#sortable" ).sortable({
                update: function(event, ui) {
                    var LangOrder = $(this).sortable('toArray').toString();
                    $('#lang_order').attr('value',LangOrder);
                }
            });
			
            $( "#sortable" ).disableSelection();			
			
            $('.inactive-sec').next('table').hide();
            $('.inactive-sec').hide();
			
            if($('.tx_switcher_pos')[0].checked){
                txwt.customPosEnabled();
            }else{
                txwt.customPosDisabled();
            }
            if($('#use_custom_flags')[0].checked){
                txwt.customFlagsEnalbed();
            }else{
                txwt.customFlagsDisabled();
            }
            if($('#txwt-ls_theme').val()==0){
                $('.txwt-customizer').show();
            }else{
                $('.txwt-customizer').hide();
            }
		

        },
		
		TxInit: function(){
			//Initialize transifex
            
                window.liveSettings = {
                    api_key: TXWT.api_key
                };
            
		},

        EventHandlers: function (event) {
            $('.wpurl_switcher').click(txwt.showWPstgs);
            $('.default_switcher').click(txwt.showTXstgs);
            $('.tx_switcher_pos').click(txwt.txSwitcherPos);
            $('#use_custom_flags').click(txwt.customFlags);
            $('#default_dir').click(txwt.defaultFlagDir);
            $('#fetch_langs').click(txwt.fetchLanguages);
            $('#txwt-ls_theme').change(txwt.customCustomizer);
            $('.lang-url-formats input').click(txwt.redFlagSubdomains);
        },
		
        wpColorChange:  function(event, ui){
            $(this).css( {
                backgroundColor: ui.color.toString()
            } );
        },
		
        wpColorClear: function(){
            pickColor( '' );
        },
				
		
        showWPstgs: function (event){
            $('#switcher_wp').next('table').show();
            $('#switcher_wp').show();
            $('#switcher_tx').next('table').hide();
            $('#switcher_tx').hide();			
			
        },
        showTXstgs: function (event){
            $('#switcher_wp').next('table').hide();
            $('#switcher_wp').hide();
            $('#switcher_tx').next('table').show();
            $('#switcher_tx').show();			
			
        },
        txSwitcherPos: function(event){
            if(this.checked){
                txwt.customPosEnabled();
			
            }else{
                txwt.customPosDisabled();
            }
			
        },
        customPosDisabled: function(){
            $('.tx_custom_pos').hide();			
            $('.tx_default_pos input').attr('DISABLED',false);			
        },
        customPosEnabled: function(){
            $('.tx_custom_pos').show();
            $('.tx_default_pos input').attr('DISABLED',true);						
        },
		
        customFlags: function(event){
            if(this.checked){
                txwt.customFlagsEnalbed();
			
            }else{
                txwt.customFlagsDisabled();
            }
			
        },		
        customFlagsDisabled: function(){
            $('.custom_flags input, .custom_flags select').attr('DISABLED',true);
        },
        customFlagsEnalbed: function(){
            $('.custom_flags input, .custom_flags select').attr('DISABLED',false);
        },
        defaultFlagDir: function(){
            document.getElementById('txwt-flag-dir').value=TXWT.flag_dir;
            return false;
        },
        fetchLanguages: function(){
            if (typeof Transifex == 'undefined'){
                alert('Unable to connect to server');			
                return false;
            }  
			
            if(window.liveSettings.api_key != document.getElementById('tx_api_key').value){
                alert(TXWT.save_key);
                return false;
            }
			
            if(window.liveSettings.api_key == ''){
                alert(TXWT.empty_key);
                return false;
            }
			
            var langs = Transifex.live.getAllLanguages();
            if (typeof langs != 'undefined'){   
                var data={
                    action:'txwt_store_langs',
                    langs:langs,
                    _wpnonce:$('#txwt_fetch_langs').val()
                };
                var spinner =  $(this).next('.spinner');
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: data,
                    cache: false,
                    beforeSend: function () {
                        spinner.show();
                    },
                    success: function (data) {
                        spinner.hide();
                        if(data != 'err_1' || data != 'err_2'){
                            $('#langs-fetched-notice .languages').html(data);
                            $('#langs-fetched-notice').show();
                        }else{
                            $('#integrity-err').show();
                        }
                    }
                });	
            }else{
                $('#empty-langs-err').show();
            }
			
        },
        customCustomizer: function(){
            if(this[0].selected){
                $('.txwt-customizer').show();
            }else{
                $('.txwt-customizer').hide();
            }
        },
		
        redFlagSubdomains: function(){
            if(TXWT.sudomain_langs=='0'){
                if(this.value=='2'){
                    $('#subdomain-langs label').css('color', 'red');
                    $('#subdomain-langs p').show();
                }else{
                    $('#subdomain-langs p').hide();
                }
            }
        }
		
    };
		      

    $(document).ready(txwt.init);

} // end txwt_admin()

txwt_admin(jQuery);