@extends('layouts.app')

@section('content')
<script>
    window.location.href = @json(route('manage-student'));
</script>
@endsection
