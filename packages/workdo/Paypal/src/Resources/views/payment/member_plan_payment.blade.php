
<div class="tab-pane fade " id="paypal-payment" role="tabpanel"
    aria-labelledby="paypal-payment">
    <form method="post" action="{{ route('memberplan.pay.with.paypal') }}"
        class="require-validation" id="payment-form">
        @csrf
        <div class="row">
            <div class="form-group col-md-12">
                <label for="amount">{{ __('Amount') }}</label>
                <div class="input-group">
                    <span class="input-group-prepend"><span
                            class="input-group-text">{{ isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$' }}</span></span>
                    <input class="form-control" required="required"
                        min="0" name="amount" type="number"
                        value="{{ \Workdo\GymManagement\Entities\GymMember::getDue($assignmembershipplan->fee,$user->id) }}" min="0"
                        step="0.01" max="{{ \Workdo\GymManagement\Entities\GymMember::getDue($assignmembershipplan->fee,$user->id) }}"
                        id="amount">
                    <input type="hidden" value="{{ $assignmembershipplan->id }}"
                        name="membershipplan_id">

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="error" style="display: none;">
                    <div class='alert-danger alert'>
                        {{ __('Please correct the errors and try again.') }}</div>
                </div>
            </div>
        </div>
        <div class="text-end">
            <button type="button" class="btn  btn-secondary me-1"
                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button class="btn btn-primary"
                type="submit">{{ __('Make Payment') }}</button>
        </div>
    </form>
</div>

