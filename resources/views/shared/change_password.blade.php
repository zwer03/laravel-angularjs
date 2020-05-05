<div class="modal fade" id="changepwModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
        	@if(!Auth::user()->last_change_password)
                You must change your password.
            @else
                Change password form
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
	    	@endif
        </div>
        <div class="modal-body">
            <form action="{{route('user.password_change')}}" method="post" id="UserChangePasswordForm">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control" id="username" placeholder="Username" name="username" value="{{Auth::user()->username}}" readonly>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="oldpwd" placeholder="Old Password" name="old_password">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="newpwd" placeholder="New Password" name="password">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="confirmpwd" placeholder="Confirm Password" name="confirm_password">
                </div>
                @if(!Auth::user()->last_change_password && $default_pf_type->value)
                <h5 class="bg-info text-center">Default PF charge when unattended</h5>
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="default_pf_type" value="1000" required>Room Rate times Number of Days Stayed
                    </label>
                </div>
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="default_pf_type" value="1001">System Default
                    </label>
                </div>
                @endif
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </form>
        </div><!-- 
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div> -->
      </div>
    </div>
</div>	


<script>
	jQuery(document).ready(function(){
		var last_change_password = "{{Auth::user()->last_change_password}}";
		// console.log(last_change_password);
		if(!last_change_password)
			jQuery('#changepwModal').modal({backdrop: 'static', keyboard: false});
		jQuery('#change-password').click(function(){
            jQuery('#changepwModal').modal('show');
        });
        var change_pw_form = jQuery('#UserChangePasswordForm');
		change_pw_form.submit(function(){
			var isValid = true;
			jQuery("#UserChangePasswordForm input").each(function() {
			   var element = jQuery(this);
			   if (jQuery.trim(element.val()) == "") {
			       isValid = false;
			   }
			});
			// console.log(isValid);
			if(isValid){
				if(jQuery("#confirmpwd").val() == jQuery("#newpwd").val()){
                    jQuery.ajax({
                        url: change_pw_form.attr('action'),
                        data:change_pw_form.serialize(),
                        type: 'POST',
                        dataType : 'json',
                        success:function(data){
                            if(data.success){
                                jQuery('#changepwModal').modal('hide');
                                alert('Password has been changed. Please log in again.');
                                jQuery('#logout-form').submit();
                            }else
                                alert(data.message)
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(thrownError);
                        }
                    });
				}else
					alert("Please make sure your passwords match.")
			}else
				alert('Please complete all fields');

			return false;
		});
	});
	
</script>