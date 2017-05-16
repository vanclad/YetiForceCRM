/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery(document).ready(function ($) {
    $('#tabs').tab();
    $('#pills').tab();
    
    // modal is greyed out if z-index is low
    $("#myModal").css("z-index", "9999999");
    $("#myRegisterModal").css("z-index", "9999999");
    
    // Hide modal if "Okay" is pressed
    $('#myModal .okay-button').click(function() {
        var disabled = $('#confirm').attr('disabled');
        if(typeof disabled == 'undefined') {
            $('#myModal').modal('hide');
            $('#delete #EditView').submit();
        }
    });
    $('#myRegisterModal .okay-button').click(function() {
        //var confirm = $('#confirmRegistration').attr('checked');
        //if ( confirm === false ) {            
        var disabled = $('#confirmRegistration').attr('disabled');
        if(typeof disabled == 'undefined') {
            $('#myRegisterModal').modal('hide');
        }
    });
    
    // enable/disable confirm button
    $('#status').change(function() {
        $('#confirm').attr('disabled', !this.checked);
    });
    $('#confirmRegistration').click(function() {
        $('#register_changes').prop("checked", $('#statusRegistration').prop("checked"));
    });
	$('#register_changes').click(function(){
		app.showModalWindow($('#myRegisterModal'));
	});
});