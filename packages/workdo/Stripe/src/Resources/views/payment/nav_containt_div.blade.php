
<div class="tab-pane fade " id="stripe-payment" role="tabpanel"
    aria-labelledby="stripe-payment">
    <form method="post" action="{{ route('invoice.pay.with.stripe') }}"
        class="require-validation" id="payment-form">
        @csrf
        @if($type == "invoice")
        <input type="hidden" name="type" value="invoice">
        @elseif($type == "retainer")
            <input type="hidden" name="type" value="retainer">
        @endif
        <div class="row">
            <div class="form-group col-md-12">
                <label for="amount">{{ __('Amount') }}</label>
                <div class="input-group">
                    <span class="input-group-prepend"><span
                            class="input-group-text">{{ isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$' }}</span></span>
                    <input class="form-control" required="required"
                        min="0" name="amount" type="number"
                        value="{{ $invoice->getDue() }}" min="0"
                        step="0.01" max="{{ $invoice->getDue() }}"
                        id="amount">
                    <input type="hidden" value="{{ $invoice->id }}"
                        name="invoice_id">
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

