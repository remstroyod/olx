<div class="mb-3">
    <h5 class="subtitle">
        {{ __('Name Product: :name', ['name' => $item->title]) }}
    </h5>
    <div>
        {{ __('Price: :price :currency', ['price' => isset($item->price) ? $item->price : 0, 'currency' => isset($item->currency) ? $item->currency : '']) }}
    </div>

    <input type="hidden" name="price" value="{{ isset($item->price) ? $item->price : 0 }}">
    <input type="hidden" name="currency" value="{{ isset($item->price) ? $item->currency : 0 }}">

</div>

<div class="mb-3">
    <label for="userEmail" class="form-label">
        {{ __('Email address') }}
    </label>
    <input
        type="email"
        class="form-control"
        id="userEmail"
        name="email"
    >
</div>
<x-button-primary>
    {{ __('Subscribe') }}
</x-button-primary>
