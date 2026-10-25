@extends('core/table::table')

@section('content')
    @include('plugins/marketplace::enquiries.partials.visibility-toggle')

    @parent
    @include('plugins/marketplace::enquiries.partials.visibility-script')
@endsection
