@extends('layouts.master')
@section('title', trans('all.perpegawai'))
@section('content')
  
  <script>
  $(function(){
      $('.date').mask("00/00/0000", {clearIfNotMatch: true});

      $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
      });
      selectInput('#pegawai','{{ url('select2pegawai') }}');
  });

  function submitTampilkan(){
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      $('#setulang').attr('disabled', 'disabled');

      var pegawai = $('#pegawai').val();
      var tanggalawal = $('#tanggalawal').val();
      var tanggalakhir = $('#tanggalakhir').val();

      if(pegawai == ''){
          alertWarning("{{ trans('all.pegawaikosong') }}",
                  function() {
                      aktifkanTombol();
                      $('#setulang').removeAttr('disabled');
                      setFocus($('#token-input-pegawai'));
                  });
          return false;
      }

      if(tanggalawal == ''){
          alertWarning("{{ trans('all.tanggalkosong') }}",
                  function() {
                      aktifkanTombol();
                      $('#setulang').removeAttr('disabled');
                      setFocus($('#tanggalawal'));
                  });
          return false;
      }

      if(tanggalakhir == ''){
          alertWarning("{{ trans('all.tanggalkosong') }}",
                  function() {
                      aktifkanTombol();
                      $('#setulang').removeAttr('disabled');
                      setFocus($('#tanggalakhir'));
                  });
          return false;
      }

      tanggalawal = tanggalawal.replace(new RegExp('/', 'g'), '-');
      tanggalakhir = tanggalakhir.replace(new RegExp('/', 'g'), '-');

      $.ajax({
          type: "GET",
          url: '{{ url('laporan/perpegawai/data') }}/'+pegawai+'/'+tanggalawal+'/'+tanggalakhir,
          data: '',
          cache: false,
          success: function(html){
              $('#isidata').html(html);
              aktifkanTombol();
              $('#buttonekspor').css('display', '');
              $('#buttonekspor').attr('idpegawai', pegawai);
              $('#setulang').removeAttr('disabled');
          }
      });
  }

  function setUlang(){
      $('#isidata').html('');
      $('.token-input-delete-token-facebook').trigger('click');
      $('#buttonekspor').css('display', 'none');
      $('#buttonekspor').attr('idpegawai', '');
      return false;
  }

  function ekspor(){
      var idpegawai = $('#buttonekspor').attr('idpegawai');
      window.location.href='{{ url('laporan/perpegawai/excel') }}/'+idpegawai;
  }
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.perpegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.perpegawai') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
            {{ csrf_field() }}
            <table width="100%">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.pegawai') }}</td>
                <td style="float:left;min-width:200px">
                    <select class="form-control" id="pegawai" name="pegawai">
                        <option value=""></option>
                    </select>
                </td>
                <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                <td style="float:left;">
                    <table>
                        <tr>
                            <td style="padding:0">
                                <input type="text" class="form-control date" size="11" value="{{ $valuetglawalakhir->tanggalawal }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
                            </td>
                            <td>-</td>
                            <td style="padding:0">
                                <input type="text" class="form-control date" size="11" value="{{ $valuetglawalakhir->tanggalakhir }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
                            </td>
                        </tr>
                    </table>
                </td>
                  <td style="float:left">
                    <button id="submit" type="button" onclick="return submitTampilkan()" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" id="setulang" onclick="return setUlang()" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                </td>
                  <td style="display: none;" id="buttonekspor" idpegawai="">
                    <button type="button" class="btn btn-primary pull-right" onclick="return ekspor()"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                </td>
              </tr>
            </table>
          <p></p>
          <div class="ibox float-e-margins" id="isidata"></div>
      </div>
    </div>
  </div>
@stop