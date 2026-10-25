@extends('core/table::table')

@section('content')
    @parent
    @include('plugins/marketplace::enquiries.partials.visibility-script')
@endsection
