@extends('layouts.master')
@section('title', trans('all.aktivitas'))
@section('content')
  
  @if(Session::get('message'))
    <script>
    $(document).ready(function() {
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 5000,
                      extendedTimeOut: 5000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    });
    </script>
  @endif
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.aktivitas') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.aktivitas') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li class="active"><a href="{{ url('datainduk/pegawai/aktivitas') }}">{{ trans('all.aktivitas') }}</a></li>
              <li><a href="{{ url('datainduk/pegawai/aktivitaskategori') }}">{{ trans('all.kategori') }}</a></li>
          </ul>
          <br>
          <form id="formfilter" action="{{url('datainduk/pegawai/aktivitas/submitfilter')}}" method="post">
              {{csrf_field()}}
              <table>
                  <tr>
                      <td style="padding:5px;padding-left:0">{{trans('all.kategori')}}</td>
                      <td style="padding:5px">
                        <select class="form-control" name="aktivitaskategori" id="aktivitaskategori">
                            <option value=""></option>
                            @if($dataaktivitaskategori != '')
                                @foreach($dataaktivitaskategori as $key)
                                    <option value="{{$key->id}}" @if($aktivitaskategori == $key->id) selected @endif>{{$key->nama}}</option>
                                @endforeach
                            @endif
                        </select>
                      </td>
                      <td style="padding:5px">
                          <button type="button" onclick="validasiFilterAktivitasKategori()" class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{trans('all.tampilkan')}}</button>&nbsp;&nbsp;
                          <button type="button" onclick="return ke('{{ url('setulang/aktivitas') }}')" class="btn btn-primary"><i class="fa fa-refresh"></i>&nbsp;&nbsp;{{trans('all.setulang')}}</button>
                      </td>
                  </tr>
              </table>
          </form>
          @if($aktivitaskategori != "")
                <br>
                <table width="100%">
                  <tr>
                      @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 't') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                            <td style="float:left">
                                <a href="{!! url('datainduk/pegawai/aktivitas/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                                <a style="margin-left:10px" href="{!! url('urutan/aktivitas') !!}" class="btn btn-primary pull-left"><i class="fa fa-sort"></i>&nbsp;&nbsp;{{ trans('all.urutan') }}</a>
                            </td>
                      @endif
                      <td>
                          <button class="btn btn-primary pull-right" onclick="return ke('{{ url('aktivitas/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                      </td>
                  </tr>
                </table>
                <p></p>
                <div class="ibox float-e-margins">
                  <div class="ibox-content">
                    <table width=100% class="table datatable table-striped table-condensed table-hover">
                      <thead>
                        <tr>
                            @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                                <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                            @endif
                            <td class="opsi1"><b>{{ trans('all.urutan') }}</b></td>
                            <td class="nama"><b>{{ trans('all.pertanyaan') }}</b></td>
                            <td class="nama"><b>{{ trans('all.keterangan') }}</b></td>
                            <td class="opsi3"><center><b>{{ trans('all.harusdiisi') }}</b></center></td>
                            <td class="opsi3"><center><b>{{ trans('all.digunakan') }}</b></center></td>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
          @endif
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
function validasiFilterAktivitasKategori(){
    var aktivitaskategori = $('#aktivitaskategori').val();
    if(aktivitaskategori === ''){
        alertWarning("{{ trans('all.kategori').' '.trans('all.sa_kosong') }}",
        function() {
            aktifkanTombol();
            setFocus($('#nama'));
        });
        return false;
    }

    $('#formfilter').submit();
}

@if($aktivitaskategori != "")
    @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
        var ordercolumn = 1;
    @else
        var ordercolumn = 0;
    @endif
    $(function() {
        $('.datatable').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{!! url("datainduk/pegawai/aktivitas/index-data") !!}',
                type: "POST",
                data: { _token: '{!! csrf_token() !!}' }
            },
            language: lang_datatable,
            columns: [
                @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                @endif
                { data: 'urutan', name: 'urutan' },
                { data: 'pertanyaan', name: 'pertanyaan' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'harusdiisi', name: 'harusdiisi' },
                { data: 'digunakan', name: 'digunakan' }
            ],
            order: [[ordercolumn, 'asc']]
        });
    });
@endif
</script>
@endpush