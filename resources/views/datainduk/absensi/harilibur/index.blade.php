@extends('layouts.master')
@section('title', trans('all.harilibur'))
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
      <h2>{{ trans('all.harilibur') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.harilibur') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12">
        <table width="100%">
            <tr>
                <td style="float:left">
                    <form action="{{ url('datainduk/abensi/hariliburfilter') }}" method="post">
                        {{ csrf_field() }}
                        <select name="tahun" id="tahun" class="form-control" onchange="this.form.submit()" style="float:left">
                            <option value="">--{{ trans('all.pilihtahun') }}--</option>
                            @if(Session::has('harilibur_tahun'))
                                @foreach($tahun as $key)
                                    <option value="{{ $key->yyyy }}" @if(Session::get('harilibur_tahun') == $key->yyyy) selected @endif>{{ $key->yyyy }}</option>
                                @endforeach
                            @else
                                @foreach($tahun as $key)
                                    <option value="{{ $key->yyyy }}">{{ $key->yyyy }}</option>
                                @endforeach
                            @endif
                        </select>
                    </form>
                </td>
                @if(strpos(Session::get('hakakses_perusahaan')->harilibur, 't') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'm') !== false)
                    <td width="100px">
                        <a href="harilibur/create" class="btn btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                    </td>
                @endif
                <td width="100px">
                    <button class="btn btn-primary pull-right" onclick="return ke('{{ url('harilibur/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                </td>
            </tr>
        </table>
        <br>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->harilibur, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                    <td class="nama"><b>{{ trans('all.agama') }}</b></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
@if(strpos(Session::get('hakakses_perusahaan')->harilibur, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'm') !== false)
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
            url: '{!! url("datainduk/absensi/harilibur/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->harilibur, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'tanggalawal', name: 'tanggalawal' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'nilai', name: 'nilai' },
            { data: 'agama', name: 'agama' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush