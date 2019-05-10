
// Define the local_enrolmultiselect namespace if it has not already been defined
M.local_enrolmultiselect = M.local_enrolmultiselect || {};

/**
 * Initialise a new selector toggle.
 *
 * @param {YUI} Y The YUI3 instance
 * @param {string} toggleId
 */
M.local_enrolmultiselect.init_selector_toggle = function (Y, toggleId, hash) {
    // Creates a new selector_toggle object
    var selector_toggle = {
        selectorName : $('#'+toggleId).parent().prev().find('select').attr('id'),
        potentialSelectorName : $('#'+toggleId).parent().next().find('select').attr('id'),
        /** This id/name used for this control in the HTML. */
        addButtonName : 'add',
        removeButtonName : 'remove',

        /** The select element that contains the list of users. */
        selectorListbox : $('#' + $('#'+toggleId).parent().prev().find('select').attr('id')),
        /** The select element that contains the list of users. */
        potentialSelectorListbox : $('#' + $('#'+toggleId).parent().next().find('select').attr('id')),
        /**
         * Initialises the selector toggle object
         * @constructor
         */
        init : function() {
            
            var data = {sTObject:this};

            $(this.selectorListbox).on('keyup click change', null, data, function(e){
                e.data.sTObject.handle_selector_selection_change(e);
            });
            
            $(this.potentialSelectorListbox).on('keyup click change',  null, data, function(e){
                e.data.sTObject.handle_potential_selector_selection_change(e);
            });
            
            $('#'+toggleId+'_add').on('click', null, data, function(e){
                e.preventDefault();
                var selectedOptions = [];
                $("#" + e.data.sTObject.potentialSelectorName +" option:selected").each(function(index){
                    selectedOptions.push($(this).html());
                });
                e.data.sTObject.send_query(e,'toggle_to_current', selectedOptions, e.data.sTObject.potentialSelectorName, e.data.sTObject.selectorName);
            });
            
            $('#'+toggleId+'_remove').on('click', null, data, function(e){
                e.preventDefault();
                var selectedOptions = [];
                $("#" + e.data.sTObject.selectorName +" option:selected").each(function(index){
                    selectedOptions.push($(this).html());
                });
                e.data.sTObject.send_query(e,'toggle_to_potential', selectedOptions, e.data.sTObject.selectorName,e.data.sTObject.potentialSelectorName);
            });
            
            $('#'+this.selectorName).closest('form').find('button[name=submitbutton], input[name=submitbutton]').on('click',null, data, function(e){
                $("#" + e.data.sTObject.selectorName +" option").prop( 'selected', true );
            });
        },
        send_query :function(e, toggleType, selectedOptions, selectorFrom, selectorTo ){
            $.post(M.cfg.wwwroot + '/local/enrolmultiselect/ajaxtoggle.php', {
                    current_selectorid : $("#"+ e.data.sTObject.selectorName).data('selectorid'),
                    potential_selectorid : $("#"+ e.data.sTObject.potentialSelectorName).data('selectorid'),
                    sesskey : M.cfg.sesskey,
                    toggle_type : toggleType,
                    selected_options : JSON.stringify( selectedOptions )
            }).done(function(data){
                if(data.success){
                    $("#"+selectorTo+'_clearbutton' ).click();
                    e.data.sTObject.move_option(selectorFrom, selectorTo);
                }
            });
        },
        move_option: function (fromSelectorName, toSelectorName){
            $("#" + fromSelectorName +" option:selected").each(function(index){
                var newOption = $(this).clone();console.log(newOption);
                var fromOptGroup = $(this).parent().clone();
                var groupName = $(this).parent().data('groupname');
                
                if( !$('#'+toSelectorName+' optgroup[data-groupname='+groupName+']').length ){
                    $(fromOptGroup).html("");
                    $('#'+toSelectorName).append(fromOptGroup);
                }

                $('#'+toSelectorName+' optgroup[data-groupname='+groupName+']').append(newOption);
                
                //console.log($(this).parent().data('groupname'));

                var toOptGroupCount = $('#'+toSelectorName+' optgroup[data-groupname='+groupName+']').children().length;
                
                $('#'+toSelectorName+' optgroup[data-groupname='+groupName+']').attr('label',groupName+' ('+toOptGroupCount+')');
                
                $(this).remove();
                var fromOptGroupCount = $('#'+fromSelectorName+' optgroup[data-groupname='+groupName+']').children().length;

                if(fromOptGroupCount){
                    $('#'+fromSelectorName+' optgroup[data-groupname='+groupName+']').attr('label',groupName+' ('+fromOptGroupCount+')');
                }else{
                    $('#'+fromSelectorName+' optgroup[data-groupname='+groupName+']').remove(); 
                }
            });
        },
        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selector_selection_change : function(e) {
            
            Y.one('#'+toggleId+'_add').set('disabled', true);
            Y.one('#'+toggleId+'_remove').set('disabled', false);

            $("#" +this.potentialSelectorName+ " option").prop( 'selected', false );
        },
        handle_potential_selector_selection_change : function(e) {
            Y.one('#'+toggleId+'_remove').set('disabled', true);
            Y.one('#'+toggleId+'_add').set('disabled', false);
            
            $("#" +this.selectorName+ " option").prop( 'selected', false );
        }
    };
    // Augment the selector toggle with the EventTarget class so that we can use
    // custom events
    Y.augment(selector_toggle, Y.EventTarget, null, null, {});
    // Initialise the selector toggle
    selector_toggle.init();

    // Return the selector toggle
    return selector_toggle;
};