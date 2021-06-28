@extends('layouts.master')
@section('title', trans('all.cuti'))
@section('content')
  
    <script>
    @if(Session::get('message'))
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
    @endif

    function setJumlahCuti(){
        var modaljumlahcuti = $('#modaljumlahcuti').val();
        if (cekAlertAngkaValid(modaljumlahcuti,0,999,0,"{{trans('all.jumlahcuti')}}",
            function() {
                aktifkanTombol();
                setFocus($('#modaljumlahcuti'));
            }
        )==false) return false;
        $('.jumlahcuti').val(modaljumlahcuti);
        $('#tutupmodal').trigger('click');
    }
    </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.cuti') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.cuti') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        @if(Session::has('cuti_tahun'))
            <div class="ibox float-e-margins">

              <div class="ibox-content">
                <div class="alert alert-danger">
                  <center>
                      {{ $keterangan }}
                  </center>
                </div>
                <form action="{{ url('datainduk/absensi/cuti/submitsimpan') }}" method="post">
                    {{ csrf_field() }}
                    @if(strpos(Session::get('hakakses_perusahaan')->cuti, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'm') !== false)
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
                    @endif
                    <button type="button" class="btn btn-primary" onclick="ke('{{ url('datainduk/absensi/cuti/setulang') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>&nbsp;&nbsp;
                    <button type="button" id="setjumlahcuti" class="btn btn-primary" data-toggle="modal" data-target="#modalsetjumlahcuti"><i class="fa fa-sliders"></i>&nbsp;&nbsp;{{ trans('all.setjumlahcuti') }}</button>
                    <button type="button" class="btn btn-primary pull-right" onclick="ke('{{ url('datainduk/absensi/cuti/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                    <table width=100% class="table table-condensed">
                      <thead>
                        <tr>
                            <td class="pin"><b>{{ trans('all.pin') }}</b></td>
                            <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                            <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                            <td class="opsi4"><b>{{ trans('all.jumlahcuti') }}</b></td>
                            <td class="opsi4"><b>{{ trans('all.terpakai') }}</b></td>
                            <td class="opsi4"><b>{{ trans('all.sisa') }}</b></td>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($data as $key)
                            <tr>
                                <td style="padding-top:15px">{{ $key->pin }}</td>
                                <td style="padding-top:15px">
                                    <span class="detailpegawai" title="{{ $key->nama }}" onclick="detailpegawai({{ $key->id }})" style="cursor:pointer;">{{ $key->nama }}</span>
                                </td>
                                <td style="padding-top:15px">{!! $key->atribut !!}</td>
                                <td>
                                    <input type="hidden" name="idpegawai[]" value="{{ $key->id }}">
                                    <table>
                                        <tr>
                                            <td>
                                                @if(strpos(Session::get('hakakses_perusahaan')->cuti, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'm') !== false)
                                                    <input type="text" name="jumlahcuti[]" autocomplete="off" class="jumlahcuti form-control" value="{{ $key->jumlahcuti }}">
                                                @else
                                                    {{ $key->jumlahcuti }}
                                                @endif
                                            </td>
                                            <td style="padding-left:10px">{{ trans('all.hari') }}</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="padding-top:15px">{{ $key->cutiterpakai.' '.trans('all.hari') }}</td>
                                <td style="padding-top:15px">{{ $key->sisa.' '.trans('all.hari') }}</td>
                            </tr>
                        @endforeach
                      </tbody>
                    </table>
                    @if(strpos(Session::get('hakakses_perusahaan')->cuti, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'm') !== false)
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
                    @endif
                    <button type="button" class="btn btn-primary" onclick="ke('{{ url('datainduk/absensi/cuti/setulang') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                </form>
              </div>
            </div>
        @endif
    </div>
  </div>

  <!-- Modal setjumlahcuti-->
  <div class="modal fade" id="modalsetjumlahcuti" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">
          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.setjumlahcuti') }}</h4>
              </div>
              <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
              <div class="modal-body">
                  <table width='100%'>
                      <tr id="tr_universal_inputmanual_nominal">
                          <td style="padding:10px;padding-top:0">
                              {{ trans('all.jumlahcuti') }}<br>
                              <input id="modaljumlahcuti" onkeypress="return onlyNumber(0,event)" maxlength="4" class="form-control" value="0" type="text">
                          </td>
                      </tr>
                      <tr>
                          <td align=right style="padding:10px">
                              <button type="button" class="btn btn-success" onclick="setJumlahCuti()"><i class='fa fa-save'></i> {{ trans('all.set') }}</button>&nbsp;&nbsp;
                              <button data-dismiss="modal" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
                          </td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal setjumlahcuti-->

@stop