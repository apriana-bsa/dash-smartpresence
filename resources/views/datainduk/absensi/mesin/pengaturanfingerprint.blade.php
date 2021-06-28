@extends('layouts.master')
@section('title', trans('all.menu_mesin'))
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

  @if(Session::get('alert'))
      <script>
          $(document).ready(function() {
              alertSuccess("{{ Session::get("alert") }}");
              return false;
          });
      </script>
  @endif

  <script>
  $(function(){
      $('.date').mask("00/00/0000", {clearIfNotMatch: true});

      $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
      });

      $('.jam').inputmask( 'h:s' );
  })
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_mesin') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_mesin') }}</li>
        <li class="active"><strong>{{ trans('all.pengaturanfingerprint') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
              <table>
                  <tr>
                      <td style="padding:2px">{{ trans('all.nama') }}</td>
                      <td style="padding:2px">: {{ $data->nama }}</td>
                  </tr>
                  <tr>
                      <td style="padding:2px">{{ trans('all.ip') }}</td>
                      <td style="padding:2px" id="fp_ip" val="{{ $data->fp_ip }}">: <span id="fp_comkey" val="{{ $data->fp_comkey }}">{{ $data->fp_ip.'/'.$data->fp_comkey }}</span></td>
                  </tr>
                  <tr>
                      <td style="padding:2px">{{ trans('all.port') }}</td>
                      <td style="padding:2px">: <span id="fp_soapport" val="{{ $data->fp_soapport }}">{{ $data->fp_soapport }} (SOAP)</span> <span id="fp_udpport" val="{{ $data->fp_udpport }}">{{ $data->fp_udpport }} (UDP)</span></td>
                  </tr>
                  <tr>
                      <td style="padding:2px">{{ trans('all.connector') }}</td>
                      <td style="padding:2px">: {{ $data->fp_connector }}</td>
                  </tr>
                  <tr>
                      <td style="padding:2px">{{ trans('all.pushapi') }}</td>
                      <td style="padding:2px" id="fp_pushapi" val="{{ $data->fp_pushapi }}">: {{ $data->fp_pushapi }}</td>
                  </tr>
                  <tr>
                      <td style="padding:2px;padding-top:10px"><button class="btn btn-primary" id="kembali" onclick="ke('{{ url('datainduk/absensi/mesin') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button></td>
                  </tr>
              </table>
              <ul class="nav nav-tabs" style="padding:10px;padding-bottom:0">
                  <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.device') }}</a></li>
                  <li><a data-toggle="tab" href="#tab-2">{{ trans('all.pegawai') }}</a></li>
                  <li><a data-toggle="tab" href="#tab-3">{{ trans('all.fingersample') }}</a></li>
                  <li><a data-toggle="tab" href="#tab-4">{{ trans('all.superadmin') }}</a></li>
              </ul>
              <p></p>
              <div class="tab-content">
                  <div id="tab-1" class="tab-pane active">
                      <table>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.enable') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodenable" id="methodenable">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitenable" onclick="proses('enable')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.disable') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methoddisable" id="methoddisable">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitdisable" onclick="proses('disable')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.restart') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodrestart" id="methodrestart">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitrestart" onclick="proses('restart')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.refreshdb') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodrefreshdb" id="methodrefreshdb">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitrefreshdb" onclick="proses('refreshdb')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.deleteall') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methoddeleteall" id="methoddeleteall">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitdeleteall" onclick="proses('deleteall')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.setdatetime') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methoddatetime" id="methoddatetime">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;float:left;width:120px">
                                  <input type="text" class="form-control date" id="tanggal" placeholder="dd/mm/yyyy" value="{{ date('d/m/Y') }}" name="tanggal">
                              </td>
                              <td style="padding:5px;float:left;width:90px">
                                  <input type="text" class="form-control jam" id="jam" placeholder="hh:mm" value="{{ date('H:i') }}" name="jam">
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitdatetime" onclick="proses('datetime')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                      </table>
                  </div>
                  <div id="tab-2" class="tab-pane">
                      <table>
                          <tr>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="tipepengguna" id="tipepengguna" onchange="setTipe()">
                                      <option value="get">{{ trans('all.tampilkan') }}</option>
                                      <option value="delete">{{ trans('all.hapus') }}</option>
                                  </select>
                              </td>
                              <td style="padding:5px;display: none;" class="tdpengguna">
                                  <input type="text" id="pinpengguna" size="7" name="pinpengguna" placeholder="{{ trans('all.pin') }}" class="form-control">
                              </td>
                              <td style="padding:5px">{{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodpengguna" id="methodpengguna">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitpengguna" onclick="proses('pengguna')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                      </table>
                      <table class="utilpengguna" style="display: none;">
                          <tr>
                              <td colspan="6">
                                  <table>
                                      <tr>
                                          <td style="padding:5px;" class="tdget">
                                              <button id="submitsimpansemuapegawai" type="button" onclick="insertAllPegawai()" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.simpansemuapegawai') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                          </td>
                                          <td style="padding:5px;" class="tdget">
                                              <input type="checkbox" checked id="simpanpakaifingersample" name="simpanpakaifingersample">&nbsp;&nbsp;{{ trans('all.simpanpakaifingersample') }}
                                          </td>
                                          <td style="padding:5px;" class="tdget">
                                              <input type="checkbox" checked id="tumpukdatajikakembar" name="tumpukdatajikakembar">&nbsp;&nbsp;{{ trans('all.tumpukdatajikakembar') }}
                                          </td>
                                      </tr>
                                  </table>
                              </td>
                          </tr>
                      </table>
                      <table id="datatablepengguna" class="table datatable table-striped table-condensed utilpengguna" id="tabelpengguna" style="display: none;">
                          <thead>
                              <th class="opsi1">{{ trans('all.manipulasi') }}</th>
                              <th class="opsi1">{{ trans('all.pin') }}</th>
                              <th class="nama">{{ trans('all.nama') }}</th>
                          </thead>
                          <tbody id="tabelpenggunatbody"></tbody>
                      </table>
                  </div>
                  <div id="tab-3" class="tab-pane">
                      <table>
                          <tr>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="tipefingersample" id="tipefingersample">
                                      <option value="get">{{ trans('all.tampilkan') }}</option>
                                      <option value="delete">{{ trans('all.hapus') }}</option>
                                  </select>
                              </td>
                              <td style="padding:5px">
                                  <input type="text" id="pin" size="7" name="pin" placeholder="{{ trans('all.pin') }}" class="form-control">
                              </td>
                              <td style="padding:5px">{{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodfingersample" id="methodfingersample">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitfingersample" onclick="proses('fingersample')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                      </table>
                      <table class="utilfingersample" style="display: none;">
                          <tr>
                              <td colspan="6">
                                  <table>
                                      <tr>
                                          <td style="padding:5px;">
                                              <button id="submitsimpansemuafingersample" type="button" onclick="insertAllFingerSample()" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.simpansemuafingersample') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                          </td>
                                      </tr>
                                  </table>
                              </td>
                          </tr>
                      </table>
                      <span id="algoritma" val=""></span>
                      <table id="datatablefingersample" class="table datatable table-striped table-condensed utilfingersample" id="tabelfingersample" style="display: none;">
                          <thead>
                              <th class="opsi1">{{ trans('all.manipulasi') }}</th>
                              <th class="opsi1">{{ trans('all.fingerid') }}</th>
                              <th class="opsi1">{{ trans('all.size') }}</th>
                              <th class="opsi1">{{ trans('all.valid') }}</th>
                              <th class="keterangan">{{ trans('all.template') }}</th>
                          </thead>
                          <tbody id="tabelfingersampletbody"></tbody>
                      </table>
                  </div>
                  <div id="tab-4" class="tab-pane">
                      <table>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.kuncifingerprint') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodlock" id="methodlock">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitlock" onclick="proses('lock')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px"><b>{{ trans('all.bukakuncifingerprint') }}</b>, {{ trans('all.method') }}</td>
                              <td>:</td>
                              <td style="padding:5px">
                                  <select style="float:left" class="form-control" name="methodunlock" id="methodunlock">
                                      <option value="soap">SOAP</option>
                                      <option value="udp">UDP</option>
                                  </select>
                              </td>
                              <td style="padding:5px;">
                                  <button id="submitunlock" onclick="proses('unlock')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2">&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              </td>
                          </tr>
                      </table>
                  </div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
var comkey = $('#fp_comkey').attr('val');
var ip = $('#fp_ip').attr('val');
var soapport = $('#fp_soapport').attr('val');
var udpport = $('#fp_udpport').attr('val');
var pushapi = $('#fp_pushapi').attr('val');

function proses(jenis){
    setButton(jenis,'freeze');

    $.ajax({
        type: "GET",
        url: '{{ url('generatecsrftoken') }}',
        data: '',
        cache: false,
        success: function (token) {

            var methodjenis = $('#method'+jenis).val();
            var method = 'POST';
            var tipe = '';
            var objectData =
            {
                "method": methodjenis,
                "ip": ip,
                "port_soap": soapport,
                "port_udp": udpport,
                "comkey": comkey
            };
            if(jenis == 'deleteall'){
                method = 'DELETE';
            }
            var url = pushapi+'/device/'+jenis;
            if(jenis == 'pengguna') {
                tipe = $('#tipepengguna').val();
                url = pushapi+'/user/'+tipe;
                if(tipe == 'delete'){
                    method = 'DELETE';
                    var pin = $('#pinpengguna').val();
                    if(pin == ''){
                        alertWarning("{{ trans('all.pinkosong') }}",
                            function() {
                                setButton(jenis,'unfreeze');
                                setFocus($('#pinpengguna'));
                            });
                        return false;
                    }
                    url = pushapi+'/user/'+tipe+'/'+pin;
                }

                $('#resultpengguna').css('display', 'none');
                $('#resultpengguna').val('');
            }else if(jenis == 'fingersample'){
                var pin = $('#pin').val();
                tipe = $('#tipefingersample').val();
                if(tipe == 'delete'){
                    method = 'DELETE';
                }
                if(pin == ''){
                    alertWarning("{{ trans('all.pinkosong') }}",
                        function() {
                            setButton(jenis,'unfreeze');
                            setFocus($('#pin'));
                        });
                    return false;
                }
                url = pushapi+'/fingersample/'+tipe+'/'+pin;

                $('#resultfingersample').css('display', 'none');
                $('#resultfingersample').val('');
            }else if(jenis == 'datetime'){
                method = 'PUT';
                var tanggal = $('#tanggal').val();
                var jam = $('#jam').val();
                if(tanggal == ''){
                    alertWarning("{{ trans('all.tanggalkosong') }}",
                        function() {
                            setButton(jenis,'unfreeze');
                            setFocus($('#tanggal'));
                        });
                    return false;
                }
                if(jam == ''){
                    alertWarning("{{ trans('all.jamkosong') }}",
                        function() {
                            setButton(jenis,'unfreeze');
                            setFocus($('#jam'));
                        });
                    return false;
                }

                //jadikan format tgl jadi yyyy-mm-dd
                var tgl = tanggal.split('/');
                var newtgl = tgl[2]+'-'+tgl[1]+'-'+tgl[0];

                url = pushapi+'/device/datetime';
                objectData =
                {
                    "method": methodjenis,
                    "ip": ip,
                    "port_soap": soapport,
                    "port_udp": udpport,
                    "comkey": comkey,
                    "date": newtgl,
                    "time": jam+':00'
                };
            }else if(jenis == 'lock'){
                url = pushapi+'/superadmin';
                method = 'POST';
            }else if(jenis == 'unlock'){
                url = pushapi+'/superadmin';
                method = 'DELETE';
            }

            var objectDataString = JSON.stringify(objectData);
            var dataString = "_token="+token+"&methods="+method+"&url="+url+"&objectdata="+objectDataString;

            $.ajax({
                type: 'POST',
                url: '{{ url('mesin/'.$data->idmesin.'/fingerprint') }}',
                data: dataString,
                cache: false,
                success: function(html){
                    //console.log(html);
                    //console.log(JSON.stringify(html['data'], null, 2));
                    if(jenis != 'pengguna' && jenis != 'fingersample')
                    {
                        if (html['status'] == 'OK') {
                            alertSuccess('{{ trans('all.prosesselesai') }}');
                        }
                    }
                    if(jenis == 'pengguna'){
                        if(tipe != 'delete') {
                            //$('#resultpengguna').css('display', '');
                            //$('#resultpengguna').val(JSON.stringify(html['data'], null, 2));
                            //console.log(html['data']);
                            //bantuan data untuk smimpan semua pegawai
                            var isitabelpengguna = '';
                            for(var i = 0;i<html['data'].length;i++){
                                isitabelpengguna += '<tr>' +
                                                        '<td><center><i style="cursor: pointer;" onclick="insertPegawai('+html['data'][i]['pin']+')" class="fa fa-save"></i></center></td>' +
                                                        '<td>'+html['data'][i]['pin']+'</td>' +
                                                        '<td id="nama_'+html['data'][i]['pin']+'">'+html['data'][i]['nama']+'</td>' +
                                                    '</tr>';
                            }
                            $('#tabelpengguna').css('display', '');
                            $('.utilpengguna').css('display', '');

                            if($.fn.DataTable.isDataTable( '#datatablepengguna' )) {
                                $("#datatablepengguna").dataTable().fnDestroy();
                                $('#datatablepengguna').DataTable().clear().destroy();
                            }
                            $('#tabelpenggunatbody').html(isitabelpengguna);
                            $('#datatablepengguna').DataTable({
                                scrollX: true,
                                columnDefs: [{
                                    targets: 0,
                                    orderable: false
                                }],
                                order: [[1, 'asc']]
                            });
                        }else{
                            if (html['status'] == 'OK') {
                                alertSuccess('{{ trans('all.prosesselesai') }}');
                            }
                        }
                    }else if(jenis == 'fingersample'){
                        if(tipe != 'delete') {
//                            $('#resultfingersample').css('display', '');
//                            $('#resultfingersample').val(JSON.stringify(html['data'], null, 2));
                            var isitabelfingersample = '';
                            $('#algoritma').attr('val', html['algoritma']);
                            for(var i = 0;i<html['data'].length;i++){
                                isitabelfingersample += '<tr>' +
                                                            '<td><center><i style="cursor: pointer;" onclick="insertFingerSample('+html['data'][i]['FingerID']+','+html['data'][i]['PIN']+')" class="fa fa-save"></i></center></td>' +
                                                            '<td>'+html['data'][i]['FingerID']+'</td>' +
                                                            '<td id="size_'+html['data'][i]['FingerID']+'">'+html['data'][i]['Size']+'</td>' +
                                                            '<td id="valid_'+html['data'][i]['FingerID']+'">'+html['data'][i]['Valid']+'</td>' +
                                                            '<td id="template_'+html['data'][i]['FingerID']+'">'+html['data'][i]['Template']+'</td>' +
                                                        '</tr>';
                            }
                            $('#tabelfingersample').css('display', '');
                            $('.utilfingersample').css('display', '');

                            if($.fn.DataTable.isDataTable( '#datatablefingersample' )) {
                                $("#datatablefingersample").dataTable().fnDestroy();
                                $('#datatablefingersample').DataTable().clear().destroy();
                            }
                            $('#tabelfingersampletbody').html(isitabelfingersample);
                            $('#datatablefingersample').DataTable({
                                scrollX: true,
                                columnDefs: [{
                                    targets: 0,
                                    orderable: false
                                }],
                                order: [[1, 'asc']]
                            });
                        }else{
                            if (html['status'] == 'OK') {
                                alertSuccess('{{ trans('all.prosesselesai') }}');
                            }
                        }
                    }
                    setButton(jenis,'unfreeze');
                }
            });
        }
    });
}

function setTipe(){
    var tipepengguna = $('#tipepengguna').val();
    $('.tdpengguna').css('display', 'none');
    $('.tdget').css('display', '');
    if(tipepengguna == 'delete'){
        $('.tdpengguna').css('display', '');
        $('#resultpengguna').css('display', 'none');
        $('.tdget').css('display', 'none');
    }
}

function setButton(jenis, type){
    if(type == 'freeze'){
        $('#submit'+jenis).attr( 'data-loading', '' );
        $('#submit'+jenis).attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
    }else{
        $('#submit'+jenis).removeAttr('data-loading');
        $('#submit'+jenis).removeAttr('disabled');
        $('#kembali').removeAttr('disabled');
    }
}

function insertAllPegawai(){
    alertConfirm('{{ trans('all.tambahkanpegawaiini') }}',function(){
        setButton('simpansemuapegawai', 'freeze');
        var simpanpakaifingersample = 0;
        var tumpukdatajikakembar = 0;
        var methods = $('#methodpengguna').val();
        var url = pushapi+'/user/get';
        if($('#tumpukdatajikakembar').is(':checked')){
            tumpukdatajikakembar = 1;
        }
        if($('#simpanpakaifingersample').is(':checked')){
            simpanpakaifingersample = 1;
        }

        var objectData =
        {
            "method": methods,
            "ip": ip,
            "port_soap": soapport,
            "port_udp": udpport,
            "comkey": comkey
        };
        var objectDataString = JSON.stringify(objectData);

        $.ajax({
            type: "GET",
            url: '{{ url('generatecsrftoken') }}',
            data: '',
            cache: false,
            success: function (token) {

                var dataString = "_token="+token+'&simpanpakaifingersample='+simpanpakaifingersample+'&tumpukdatajikakembar='+tumpukdatajikakembar+"&url="+url+"&objectdata="+objectDataString+'&pushapi='+pushapi;
                $.ajax({
                    type: "POST",
                    url: '{{ url('mesin/fingerprint/importsemuapegawai') }}',
                    data: dataString,
                    cache: false,
                    success: function (data) {
                        if (data == 'ok') {
                            alertSuccess('{{ trans('all.databerhasilditambahkan') }}');
                        } else {
                            alertWarning(data);
                        }
                        setButton('simpansemuapegawai', 'unfreeze');
                    }
                })
            }
        });
    },
    function(){

    })
}

function insertPegawai(pin){
    //alert($('#nama_'+pin).html());
    alertConfirm('{{ trans('all.tambahkanpegawaiini') }}',function(){
        var nama = $('#nama_'+pin).html();
        var simpanpakaifingersample = 0;
        var tumpukdatajikakembar = 0;
        var methods = $('#methodpengguna').val();
        var url = pushapi+'/fingersample/get/'+pin;
        if($('#tumpukdatajikakembar').is(':checked')){
            tumpukdatajikakembar = 1;
        }
        if($('#simpanpakaifingersample').is(':checked')){
            simpanpakaifingersample = 1;
        }

        var objectData =
            {
                "method": methods,
                "ip": ip,
                "port_soap": soapport,
                "port_udp": udpport,
                "comkey": comkey
            };
        var objectDataString = JSON.stringify(objectData);

        $.ajax({
            type: "GET",
            url: '{{ url('generatecsrftoken') }}',
            data: '',
            cache: false,
            success: function (token) {

                var dataString = "_token="+token+'&pin='+pin+'&nama='+nama+'&simpanpakaifingersample='+simpanpakaifingersample+'&tumpukdatajikakembar='+tumpukdatajikakembar+"&url="+url+"&objectdata="+objectDataString;
                $.ajax({
                    type: "POST",
                    url: '{{ url('mesin/fingerprint/importpegawai') }}',
                    data: dataString,
                    cache: false,
                    success: function (data) {
                        if (data == 'ok') {
                            alertSuccess('{{ trans('all.databerhasilditambahkan') }}');
                        } else {
                            alertWarning(data);
                        }
                    }
                })
            }
        });
    },
    function(){

    })
}

function insertAllFingerSample(){
    alertConfirm('{{ trans('all.tambahkanfingersampleini') }}',function(){
        var pin = $('#pin').val();
        var methods = $('#methodfingersample').val();
        var url = pushapi+'/fingersample/get/'+pin;
        var objectData =
            {
                "method": methods,
                "ip": ip,
                "port_soap": soapport,
                "port_udp": udpport,
                "comkey": comkey
            };
        var objectDataString = JSON.stringify(objectData);
        $.ajax({
            type: "GET",
            url: '{{ url('generatecsrftoken') }}',
            data: '',
            cache: false,
            success: function (token) {

                var dataString = "_token="+token+'&pin='+pin+'&url='+url+'&objectdata='+objectDataString;
                $.ajax({
                    type: "POST",
                    url: '{{ url('mesin/fingerprint/importsemuafingersample') }}',
                    data: dataString,
                    cache: false,
                    success: function (data) {
                        if (data == 'ok') {
                            alertSuccess('{{ trans('all.databerhasilditambahkan') }}');
                        } else {
                            alertWarning(data);
                        }
                    }
                })
            }
        });
    },
    function(){

    })
}

function insertFingerSample(fingerid,pin){
    alertConfirm('{{ trans('all.tambahkanfingersampleini') }}',function(){
        var algoritma = $('#algoritma').attr('val');
        var size = $('#size_'+fingerid).html();
        var valid = $('#valid_'+fingerid).html();
        var template = $('#template_'+fingerid).html();
        $.ajax({
            type: "GET",
            url: '{{ url('generatecsrftoken') }}',
            data: '',
            cache: false,
            success: function (token) {

                var dataString = "_token="+token+'&algoritma='+algoritma+'&pin='+pin+'&fingerid='+fingerid+'&size='+size+'&valid='+valid+'&template='+template;
                $.ajax({
                    type: "POST",
                    url: '{{ url('mesin/fingerprint/importfingersample') }}',
                    data: dataString,
                    cache: false,
                    success: function (data) {
                        if (data == 'ok') {
                            alertSuccess('{{ trans('all.databerhasilditambahkan') }}');
                        } else {
                            alertWarning(data);
                        }
                    }
                })
            }
        });
    },
    function(){

    })
}
</script>
@endpush