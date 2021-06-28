@extends('layouts.master')
@section('title', trans('all.lainnya'))
@section('content')
  
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });
    
    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });

  function validasi(){
    var tahun = $("#tahun").val();
    if(tahun == ""){
      alertWarning("{{ trans('all.tahunkosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#tahun'));
              });
      return false;
    }
  }
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.lainnya') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.lainnya') }}</li>
        <li class="active"><strong>{{ trans('all.harilibur') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('laporan/lainnya/harilibur') }}">{{ trans('all.harilibur') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/terlambat') }}">{{ trans('all.terlambat') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/pulangawal') }}">{{ trans('all.pulangawal') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/belumabsenmasuk') }}">{{ trans('all.belumabsenmasuk') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/belumabsenpulang') }}">{{ trans('all.belumabsenpulang') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/masuktanpajadwal') }}">{{ trans('all.masuktanpajadwal') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/prosentaseabsen') }}">{{ trans('all.prosentaseabsen') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/pegawai') }}">{{ trans('all.pegawai') }}</a></li>
          </ul>
          <br>
          <form id="form1" method="post" action="" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table width="100%">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.tahun') }}</td>
                <td style="float:left">
                  <select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">
                    @if(Session::has('lapharilibur_tahun'))
                      <option value="{{ $tahun->tahun1 }}" @if(Session::get('lapharilibur_tahun') == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                      <option value="{{ $tahun->tahun2 }}" @if(Session::get('lapharilibur_tahun') == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                      <option value="{{ $tahun->tahun3 }}" @if(Session::get('lapharilibur_tahun') == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                      <option value="{{ $tahun->tahun4 }}" @if(Session::get('lapharilibur_tahun') == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                      <option value="{{ $tahun->tahun5 }}" @if(Session::get('lapharilibur_tahun') == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                    @else
                      <option value="{{ $tahun->tahun1 }}">{{ $tahun->tahun1 }}</option>
                      <option value="{{ $tahun->tahun2 }}">{{ $tahun->tahun2 }}</option>
                      <option value="{{ $tahun->tahun3 }}">{{ $tahun->tahun3 }}</option>
                      <option value="{{ $tahun->tahun4 }}">{{ $tahun->tahun4 }}</option>
                      <option value="{{ $tahun->tahun5 }}">{{ $tahun->tahun5 }}</option>
                    @endif
                  </select>
                </td>
                @if(Session::has('lapharilibur_tahun'))
                  <td style="float:right;">
                    <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/lainnya/harilibur/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                  </td>
                @endif
              </tr>
            </table>
          </form>
          <p></p>
          @if(Session::has('lapharilibur_tahun'))
            <div class="ibox float-e-margins">
              <div class="ibox-content">
                <table width=100% class="table datatable table-striped table-condensed table-hover">
                  <thead>
                  <tr>
                    <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="nama"><b>{{ trans('all.harilibur') }}</b></td>
                    @if($arrkolom != '')
                      @foreach($arrkolom as $key)
                          <td class="opsi4"><b>{{ $key }}</b></td>
                      @endforeach
                    @endif
                  </tr>
                  </thead>
                </table>
              </div>
            </div>
          @endif
      </div>
    </div>
  </div>

  <script>
  @if(Session::has('lapharilibur_tahun'))
    $(function() {
      $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        //ajax: '{!! url("laporan/lainnya/harilibur/index-data") !!}',
        ajax: {
          url: '{!! url("laporan/lainnya/harilibur/index-data") !!}',
          type: "POST",
          data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
          { data: 'tanggalawal', name: 'tanggalawal' },
          { data: 'keterangan', name: 'keterangan' },
          @if($arrkolomtabel != '')
            @foreach($arrkolomtabel as $key)
                { data: '{{ $key }}', name: '{{ $key }}' },
            @endforeach
          @endif
        ]
      });
    });
  @endif
  </script>
@stop