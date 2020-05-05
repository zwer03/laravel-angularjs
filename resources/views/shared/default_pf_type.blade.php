<div class="modal fade" id="defaultPfTypeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{route('user.default_pf_type')}}" method="post" id="defaultPfTypeForm">
        @csrf
        @method('patch')
        <div class="modal-header">
            Default PF charge when unattended
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" class="form-check-input" name="default_pf_type" value="1000">Room Rate times Number of Days Stayed
                </label>
            </div>
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" class="form-check-input" name="default_pf_type" value="1001">System Default
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        </form>
      </div>
    </div>
</div>	
<script>
    jQuery('#default-pf-type').click(function(){
        jQuery('#defaultPfTypeModal').modal('show');
        var default_pf_type = "{{Auth::user()->default_pf_type}}";
        $("input[name=default_pf_type][value=" + default_pf_type + "]").prop('checked', true);
    });
</script>