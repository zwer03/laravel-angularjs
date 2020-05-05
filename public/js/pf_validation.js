$(function() { 
    //Confirmation before submit
    $('#toggle-pf-display').bootstrapToggle({
        on: 'Show PF',
        off: 'Hide PF'
    });
    $('[data-toggle="tooltip"]').tooltip(); 
    $("#professionalFee").submit(function(){
        console.log('here');
        if(confirm("You have entered "+$('input[type=text]').val() + " for patient " + $('#patient_name').text().trim() +". Click OK to confirm.")){
            //Add here
        }
        else{
            return false;
        }
    });
    //Maximum Length
    $('#pf_amount').keypress(function () {
        var maxLength = $(this).val().length;
        if (maxLength >= 8) {
            alert('You cannot enter more than 7 digits amount');
            return false;
        }
    });
    //Disabled entering of dot/period
    $('#pf_amount').keydown(function(e) {
        if (e.keyCode === 190 || e.keyCode === 110) {
            e.preventDefault();
        }
    });
    //Disabled cut copy paste 
    $('#pf_amount').bind("cut copy paste",function(e) {
        e.preventDefault();
    });
    //Remove 0.00 on focus

    $("#pf_amount").focus( function(){
        if(!$(this).prop('readonly'))
            $(this).val(""); 
    } );
});