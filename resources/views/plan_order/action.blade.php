@foreach ($userOrders as $userOrder)
    @if ($user->active_plan == $order->plan_id && $order->order_id == $userOrder->order_id && $order->is_refund == 0)
        <div class="badge fix_badges bg-warning p-2 px-3">
        {!! Form::open([
                'method' => 'get',
                'route' => ['order.refund', [$order->id, $order->user_id]],
                'id' => 'refund-form-' . $order->id,
            ]) !!}
            <a href="#" class="mx-3 align-items-center bs-pass-para show_confirm"  aria-label="Delete"
                data-text="{{ __('You want to confirm refund the plan. Press Yes to continue or Cancel to go back') }}"
                data-confirm-yes="refund-form-{{ $order->id }}">
                <span class ="text-white">{{ __('Refund') }}</span>
            </a>
            {{ Form::close() }}
        </div>
    @endif
@endforeach
