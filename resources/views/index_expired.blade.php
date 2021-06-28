@extends('layouts/master')
@section('title',trans('all.pembayaran'))
@section('content')
  <div class="wrapper wrapper-content animated fadeIn">
    @include('layouts/component_index_expired')
  </div>
@endsection
@push('scripts')
  <script>
    @if(Session::get('error'))
      alertError('{{ Session::get('error') }}');
    @endif

    @if(Session::has('overkuota'))
      alertConfirmNotClose('{{ Session::get('overkuota') }}',
              function(){
                alertConfirmNotClose('{{ trans("all.overkuota_konfirmasi_delete_pegawai") }}',
                        function(){
                          window.location.href="{{ url('datainduk/pegawai/pegawai') }}";
                        },
                        function(){
                            window.open("{{env('URL_TUTORIAL_HAPUS_PEGAWAI')}}")
                        },
                        "Ya, Lanjut",
                        "Lihat Tutorial"
                )
              },
              function(){}
      );
    @endif
  </script>
@endpush
