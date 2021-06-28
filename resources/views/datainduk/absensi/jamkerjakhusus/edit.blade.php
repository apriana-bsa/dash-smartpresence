@extends('layouts.master')
@section('title', trans('all.jamkerjakhusus'))
@section('content')
    
    <style>
    td{
        padding:5px;
    }
    </style>
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
                toastr.warning('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        });
    @endif
        
    $(function(){
        perhitunganJamKerja();
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        $('.jam').inputmask( 'h:s' );
        
        $("#tambahatribut").click(function(){
            
            var atribut = document.getElementsByClassName("atributpopup");
            
            $("#tabelatribut").html("");
            $("#atributarea").html("");
            
            for(var i=0; i<atribut.length; i++) {
                if(document.getElementById("atributpopup"+atribut[i].value).checked){
                    //dapatkan idatribut
                    var nilai = $("#attrpopup"+atribut[i].value).html();
                    //isi atribut
                    $("#atribut"+atribut[i].value).val(atribut[i].value);
                    //buat input nya dan tampilan nya
                    $("#atributarea").append("<input type='hidden' name='atribut[]' value='"+atribut[i].value+"'>");
                    $("#tabelatribut").append("<tr><td>"+nilai+"</td></tr>");
                }
            }
            $("#closemodal").trigger("click");
        });

        $("#tambahjamkerja").click(function(){
            var jamkerja = document.getElementsByClassName("jamkerjapopup");

            $("#tabeljamkerja").html("");
            $("#jamkerjaarea").html("");

            for(var i=0; i<jamkerja.length; i++) {
                if(document.getElementById("jamkerjapopup"+jamkerja[i].value).checked){

                    //dapatkan idjamkerja
                    var nilai = $("#attrpopup"+jamkerja[i].value).html();
                    //isi jamkerja
                    $("#jamkerja"+jamkerja[i].value).val(jamkerja[i].value);
                    //buat input nya
                    $("#jamkerjaarea").append("<input type='hidden' name='jamkerja[]' value='"+jamkerja[i].value+"'>");
                    $("#tabeljamkerja").append("<tr><td>"+nilai+"</td></tr>");
                }
            }
            //$('.jamkerjapopup').prop('checked', false);
            $("#closemodal2").trigger("click");
        });
    });

    function perhitunganJamKerja(){
        var perhitunganjamkerja = $('#perhitunganjamkerja').val();
        $('.tr_hitunglemburesetelah').css('display', '');
        if(perhitunganjamkerja == 'lembur'){
            $('.tr_hitunglemburesetelah').css('display', 'none');
        }
    }
    
    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
        var jamkerja = $("#jamkerja").val();
        var tanggalawal = $("#tanggalawal").val();
        var tanggalakhir = $("#tanggalakhir").val();
        var toleransi = $("#toleransi").val();
        var hitunglembursetelah = $("#hitunglembursetelah").val();
        var jammasuk = $("#jammasuk").val();
        var jampulang = $("#jampulang").val();
        var jamistirahatmulai = $("#jamistirahatmulai").val();
        var jamistirahatselesai = $("#jamistirahatselesai").val();
        
        @if(Session::has('conf_webperusahaan'))
        @else
          alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
        function() {
            aktifkanTombol();
        });
        return false;
        @endif
        
        if(jamkerja == ""){
            alertWarning("{{ trans('all.jamkerjakosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jamkerja'));
            });
            return false;
        }
        
        if(tanggalawal == ""){
            alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalawal'));
            });
            return false;
        }
        
        if(tanggalakhir == ""){
            alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalakhir'));
            });
            return false;
        }

        if(tanggalakhir.split("/").reverse().join("-") < tanggalawal.split("/").reverse().join("-")){
            alertWarning("{{ trans('all.tanggalakhirlebihkecildaritanggalawal') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }

        if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
            alertWarning("{{ trans('all.selisihharimaksimal31') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }
        
        if(toleransi == ""){
            alertWarning("{{ trans('all.toleransikosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#toleransi'));
            });
            return false;
        }
        
        if(hitunglembursetelah == ""){
            alertWarning("{{ trans('all.hitunglembursetelahkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalawal'));
            });
            return false;
        }
        
        if(jammasuk == ""){
            alertWarning("{{ trans('all.waktukerjakosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jammasuk'));
            });
            return false;
        }
        
        if(jampulang == ""){
            alertWarning("{{ trans('all.waktukerjakosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jampulang'));
            });
            return false;
        }
    }

    function f_tambahjamistirahat(){
        // dapatkan nilai i
        var i = $('#tambahjamistirahat').attr('ke');
        // format jam
        setTimeout(function(){ $('.jam').inputmask( 'h:s' ); },200);
        // tambahkan field jamistirahat
        $('#jamistirahat').append('<tr id="rowjamistirahat_'+i+'">' +
                                        '<td width="150px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>'+
                                        '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px;float:left">'+
                                            '<table width="100%">'+
                                                '<tr>'+
                                                    '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px">'+
                                                        '<input type="text" size="7" class="form-control jam" placeholder="hh:mm" id="jamistirahatmulai_'+i+'" name="jamistirahatmulai[]">'+
                                                    '</td>'+
                                                    '<td style="padding-top:0px;padding-bottom:0px">-</td>'+
                                                    '<td style="padding-top:0px;padding-bottom:0px;padding-right:0px">'+
                                                        '<input type="text" size="7" class="form-control jam" placeholder="hh:mm" id="jamistirahatselesai_'+i+'" name="jamistirahatselesai[]">'+
                                                    '</td>'+
                                                    '<td style="padding:0px;padding-left:5px;padding-top:2px;">'+
                                                        '<button type="button" class="btn btn-danger" onclick="hapusjamistirahat('+i+')"><i class="fa fa-trash"></i></button>'+
                                                    '</td>'+
                                                '</tr>'+
                                            '</table>'+
                                        '</td>'+
                                    '</tr>');
        // tambahkan nilai i
        i++;
        // isikan nilai i ke atribut ke
        $('#tambahjamistirahat').attr('ke',i);
    }

    function hapusjamistirahat(i){
        $('#rowjamistirahat_'+i).remove();
        $('#tambahjamistirahat').attr('ke',i);
    }

    function aturjamkerja(){
        @if(count($jamkerja) > 0)
          $("#buttonmodaljamkerja").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
        return false;
    }
    </script>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.jamkerjakhusus') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.datainduk') }}</li>
                <li>{{ trans('all.absensi') }}</li>
                <li>{{ trans('all.jamkerjakhusus') }}</li>
                <li class="active"><strong>{{ trans('all.ubahdata') }}</strong></li>
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
                        <form action="../{{ $jamkerjakhusus->id }}" method="post" onsubmit="return validasi()">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="put">
                            <table width="480px">
                                <tr>
                                    <td width="150px">{{ trans('all.keterangan') }}</td>
                                    <td>
                                        <input type="text" class="form-control" value="{{ $jamkerjakhusus->keterangan }}" name="keterangan" id="keterangan" maxlength="100">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="110px">{{ trans('all.tanggal') }}</td>
                                    <td>
                                        <table>
                                            <tr>
                                                <td style="padding-left:0px;padding-top:0px;padding-bottom: 0px;float:left">
                                                    <input type="text" class="form-control date" size="11" value='{{ date_format(date_create($jamkerjakhusus->tanggalawal), "d/m/Y") }}' autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
                                                </td>
                                                <td style="padding-bottom: 0px;padding-top:0px;">-</td>
                                                <td style="padding-bottom: 0px;padding-top:0px;padding-right: 0px;float:left">
                                                    <input type="text" class="form-control date" size="11" value='{{ date_format(date_create($jamkerjakhusus->tanggalakhir), "d/m/Y") }}' autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans('all.toleransi') }}</td>
                                    <td style="float:left">
                                        <table>
                                            <tr>
                                                <td style="padding:0">
                                                    <input type="text" name="toleransi" size="5" class="form-control" value="{{ $jamkerjakhusus->toleransi }}" maxlength="10" id="toleransi" autocomplete="off">
                                                </td>
                                                <td style="padding:0;padding-left:10px">{{ trans('all.menit') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans('all.perhitunganjamkerja') }}</td>
                                    <td style="float:left">
                                        <select class="form-control" name="perhitunganjamkerja" id="perhitunganjamkerja" onChange="return perhitunganJamKerja()">
                                            <option value="normal" @if($jamkerjakhusus->perhitunganjamkerja == 'normal') selected @endif>{{ trans('all.normal') }}</option>
                                            <option value="lembur" @if($jamkerjakhusus->perhitunganjamkerja == 'lembur') selected @endif>{{ trans('all.lembur') }}</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="tr_hitunglemburesetelah">
                                    <td>{{ trans('all.hitunglembursetelah') }}</td>
                                    <td style="float:left">
                                        <table>
                                            <tr>
                                                <td style="padding:0">
                                                    <input type="text" name="hitunglembursetelah" class="form-control" value="{{ $jamkerjakhusus->hitunglemburstlh }}" maxlength="10" id="hitunglembursetelahkosong" size=5 autocomplete="off">
                                                </td>
                                                <td style="padding:0;padding-left:10px">{{ trans('all.menit') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="110px">{{ trans('all.waktukerja') }}</td>
                                    <td style="float:left">
                                        <table>
                                            <tr>
                                                <td style="padding-left:0px;padding-top:0px;padding-bottom: 0px">
                                                    <input type="text" size="7" class="form-control jam" autocomplete="off" value='{{ date_format(date_create($jamkerjakhusus->jammasuk), "H:i") }}' placeholder="hh:mm" name="jammasuk" id="jammasuk" maxlength="10">
                                                </td>
                                                <td style="padding-bottom: 0px;padding-top:0px;">-</td>
                                                <td style="padding-bottom: 0px;padding-top:0px;padding-right: 0px">
                                                    <input type="text" size="7" class="form-control jam" autocomplete="off" value='{{ date_format(date_create($jamkerjakhusus->jampulang), "H:i") }}' value="{{ $jamkerjakhusus->jampulang }}" placeholder="hh:mm" name="jampulang" id="jampulang" maxlength="10">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding-top: 7px">{{ trans('all.jamkerja') }}</td>
                                    <td style="float: left;">
                                        <table id="tabeljamkerja">
                                            @if(isset($jamkerja))
                                                @foreach($jamkerja as $key)
                                                    @if($key->dipilih == 1)
                                                        <tr>
                                                            <td>{{ $key->nama }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </table>
                                        <button type="button" class="btn btn-success" onclick="return aturjamkerja()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturjamkerja') }}</button>
                                        <button type="button" style="display:none" id="buttonmodaljamkerja" class="btn btn-success" data-toggle="modal" data-target="#myModal2"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturjamkerja') }}</button><br>
                                        <span id="jamkerjaarea">
                                            @if(isset($jamkerja))
                                                @foreach($jamkerja as $key)
                                                    @if($key->dipilih == 1)
                                                        <input type='hidden' name='jamkerja[]' value='{{ $key->id }}'>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                <?php $i=0; ?>
                                <tr>
                                    <td colspan="2" style="padding-top:2px;">
                                        <table id="jamistirahat">
                                        @if($jamistirahat != '')
                                                @foreach($jamistirahat as $key)
                                                    <tr id="rowjamistirahat_{{ $i }}">
                                                        <td width="150px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                        <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;float:left">
                                                            <table width="100%">
                                                                <tr>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <input type="text" size="7" class="form-control jam" autocomplete="off" placeholder="hh:mm" value="{{ $key->jamawal }}" id="jamistirahatmulai_{{ $i }}" name="jamistirahatmulai[]">
                                                                    </td>
                                                                    <td style="padding-top:0px;padding-bottom:0px">-</td>
                                                                    <td style="padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                        <input type="text" size="7" class="form-control jam" autocomplete="off" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="jamistirahatselesai_{{ $i }}" name="jamistirahatselesai[]">
                                                                    </td>
                                                                    <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                        <button type="button" class="btn btn-danger" onclick="hapusjamistirahat({{ $i }})"><i class="fa fa-trash"></i></button>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <?php $i++; ?>
                                                @endforeach
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top:0">
                                        <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahat" onclick="f_tambahjamistirahat()" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=2>
                                        <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                        <button type="button" id="kembali" onclick="return ke('../../jamkerjakhusus')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tambah jamkerja-->
    <div class="modal fade" id="myModal2" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal2' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.jamkerja') }}</h4>
                </div>
                <div class="modal-body" style="max-height:480px;overflow: auto;">
                    <table>
                        @if(isset($jamkerja))
                            @foreach($jamkerja as $key)
                                <tr>
                                    <td style="padding:2px">
                                        <input type="checkbox" class="jamkerjapopup" id="jamkerjapopup{{ $key->id }}" value="{{ $key->id }}" @if($key->dipilih == 1) checked @endif> <span id="attrpopup{{ $key->id }}" onclick="spanclick('jamkerjapopup{{ $key->id }}')">{{ $key->nama }}</span><br>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
                <div class="modal-footer">
                    <table width="100%" style="align:right">
                        <tr>
                            <td>
                                <button class="btn btn-primary" id="tambahjamkerja"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal tambah jamkerja-->

@stop