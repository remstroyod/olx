<h1>{{ __('The price has changed!') }}</h1>

<p>{{ __('Product') }}: <a href="{{ $url }}">{{ $url }}</a></p>
<p>{{ __('Old price') }}: <strong>{{ sprintf('%s %s', $oldPrice, $currency) }}</strong></p>
<p>{{ __('New price') }}: <strong>{{ sprintf('%s %s', $newPrice, $currency) }}</strong></p>
