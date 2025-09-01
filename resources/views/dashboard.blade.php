@extends('layouts.app')
@section('title'){{ __('Dashboard') }}@endsection

@push('css')

@endpush

@section('content')

    <div class="col-lg-6 col-lg-auto">
        <div class="mt-5">
            <h1 class="title text-center mb-5">
                {{ __('Subscribe to price tracking') }}
            </h1>

            @if (session('status'))
                <div class="alert alert-success alert-email-success">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('subscribe.store') }}" method="POST" class="subscribeStoreForm">
                @csrf
                @method('post')

                <x-alert />

                <div class="mb-3">
                    <div class="input-group">
                        <input
                            type="url"
                            class="form-control"
                            placeholder="{{ __('insert product link here') }}"
                            aria-label="{{ __('insert product link here') }}"
                            aria-describedby="button-check-link"
                            required
                            name="url"
                            value="https://www.olx.ua/d/uk/obyavlenie/kostyum-vishivaniy-tufl-IDYNAas.html"
                        >
                        <button
                            class="btn btn-outline-secondary checkLink"
                            type="button"
                            id="button-check-link"
                            data-url="{{ route('checkLink') }}"
                        >
                            {{ __('Check Link') }}
                        </button>
                    </div>
                    <div id="linkHelp" class="form-text">{{ __('insert product link here') }}</div>

                </div>

                <div class="text-center preloader d-none">
                    <x-spinner />
                </div>

                <div class="content">

                </div>

            </form>

        </div>
    </div>

@endsection

@push('js')

    @vite([
        'resources/js/script/olx.js'
    ])

@endpush
