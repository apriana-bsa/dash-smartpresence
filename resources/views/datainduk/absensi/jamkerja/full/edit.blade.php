@extends('layouts.master')
@section('title', trans('all.jamkerja'))
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
    
    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
        var full_berlakumulai = $("#full_berlakumulai").val();
        var full_jammasuksenin = $("#full_jammasuksenin").val();
        var full_jampulangsenin = $("#full_jampulangsenin").val();
        var full_jammasukselasa = $("#full_jammasukselasa").val();
        var full_jampulangselasa = $("#full_jampulangselasa").val();
        var full_jammasukrabu = $("#full_jammasukrabu").val();
        var full_jampulangrabu = $("#full_jampulangrabu").val();
        var full_jammasukkamis = $("#full_jammasukkamis").val();
        var full_jampulangkamis = $("#full_jampulangkamis").val();
        var full_jammasukjumat = $("#full_jammasukjumat").val();
        var full_jampulangjumat = $("#full_jampulangjumat").val();
        var full_jammasuksabtu = $("#full_jammasuksabtu").val();
        var full_jampulangsabtu = $("#full_jampulangsabtu").val();
        var full_jammasukminggu = $("#full_jammasukminggu").val();
        var full_jampulangminggu = $("#full_jampulangminggu").val();
        
        @if(Session::has('conf_webperusahaan'))
        @else
            alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
                            function() {
                                aktifkanTombol();
                            });
                            return false;
        @endif
        
        if(full_berlakumulai == ""){
            alertWarning("{{ trans('all.berlakumulaikosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#full_berlakumulai'));
            });
            return false;
        }
        
        //senin
        if (document.getElementById('full_masukkerjasenin').checked) {
            if(full_jammasuksenin == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasuksenin'));
                });
                return false;
            }
            
            if(full_jampulangsenin == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangsenin'));
                });
                return false;
            }
        }
        
        //selasa
        if (document.getElementById('full_masukkerjaselasa').checked) {
            if(full_jammasukselasa == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasukselasa'));
                });
                return false;
            }
            
            if(full_jampulangselasa == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangselasa'));
                });
                return false;
            }
        }
        
        //rabu
        if (document.getElementById('full_masukkerjarabu').checked) {
            if(full_jammasukrabu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasukrabu'));
                });
                return false;
            }
            
            if(full_jampulangrabu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangrabu'));
                });
                return false;
            }
        }
        
        //kamis
        if (document.getElementById('full_masukkerjakamis').checked) {
            if(full_jammasukkamis == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasukkamis'));
                });
                return false;
            }
            
            if(full_jampulangkamis == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangkamis'));
                });
                return false;
            }
        }
        
        //jumat
        if (document.getElementById('full_masukkerjajumat').checked) {
            if(full_jammasukjumat == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasukjumat'));
                });
                return false;
            }
            
            if(full_jampulangjumat == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangjumat'));
                });
                return false;
            }
        }
        
        //sabtu
        if (document.getElementById('full_masukkerjasabtu').checked) {
            if(full_jammasuksabtu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasuksabtu'));
                });
                return false;
            }
            
            if(full_jampulangsabtu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangsabtu'));
                });
                return false;
            }
        }
        
        //minggu
        if (document.getElementById('full_masukkerjaminggu').checked) {
            if(full_jammasukminggu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jammasukminggu'));
                });
                return false;
            }
            
            if(full_jampulangminggu == ""){
                alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#full_jampulangminggu'));
                });
                return false;
            }
        }
    }
    
    $(function(){
        setTimeout(function(){ $("#full_berlakumulai").focus(); },200);
        
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        
        $('.jam').inputmask( 'h:s' );
    });

    function tambahjamistirahat(hari){
        // dapatkan nilai i
        var i = $('#tambahjamistirahat'+hari).attr('ke');
        // format jam
        setTimeout(function(){ $('.jam').inputmask( 'h:s' ); },200);
        // tambahkan field jamistirahat
        $('#jamistirahat'+hari).append('<tr id="rowjamistirahat'+hari+'_'+i+'">' +
                                            '<td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>'+
                                            '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px">'+
                                                '<table width="100%">'+
                                                    '<tr>'+
                                                        '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px">'+
                                                            '<input type="text" size="7" class="form-control jam full_istirahatmulai'+hari+'" placeholder="hh:mm" id="full_istirahatmulai'+hari+'_'+i+'" name="full_istirahatmulai'+hari+'[]">'+
                                                        '</td>'+
                                                        '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>'+
                                                        '<td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">'+
                                                            '<input type="text" size="7" class="form-control jam full_istirahatselesai'+hari+'" placeholder="hh:mm" id="full_istirahatselesai'+hari+'_'+i+'" name="full_istirahatselesai'+hari+'[]">'+
                                                        '</td>'+
                                                        '<td style="padding:0px;padding-left:5px;padding-top:2px;">'+
                                                            '<button type="button" class="btn btn-danger" onclick="hapusjamistirahat(\''+hari+'\','+i+')"><i class="fa fa-trash"></i></button>'+
                                                        '</td>'+
                                                    '</tr>'+
                                                '</table>'+
                                            '</td>'+
                                        '</tr>');
        // tambahkan nilai i
        i++;
        // isikan nilai i ke atribut ke
        $('#tambahjamistirahat'+hari).attr('ke',i);
    }

    function hapusjamistirahat(hari,i){
        $('#rowjamistirahat'+hari+'_'+i).remove();
        $('#tambahjamistirahat'+hari).attr('ke',i);
    }
    </script>
    <style type="text/css">
        .tebal{
            font-weight: bold;
        }

        span{
            cursor:default;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.jamkerja')." (".$jamkerja.")" }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.datainduk') }}</li>
                <li>{{ trans('all.absensi') }}</li>
                <li>{{ trans('all.jamkerja') }}</li>
                <li>{{ trans('all.full') }}</li>
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
                        <form action="../{{ $jamkerjafull->id }}" method="post" onsubmit="return validasi()">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="put">
                            <table width="500px">
                                <tr>
                                    <td width="120px">{{ trans('all.berlakumulai') }}</td>
                                    <td style="float:left">
                                        <input type="text" class="form-control date" id="full_berlakumulai" value='{{ date_format(date_create($jamkerjafull->berlakumulai), "d/m/Y") }}' size="11" placeholder="dd/mm/yyyy" name="full_berlakumulai">
                                    </td>
                                </tr>
                                <!-- full time -->
                                <tr>
                                    <td valign="top" style="padding-top:10px">{{ trans('all.hari') }}</td>
                                    <td>
                                        <table>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjasenin" id="full_masukkerjasenin" value="y" onclick="checkboxclick('full_masukkerjasenin',false,'full_senindetail')" @if($jamkerjafull->_2_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjasenin" onclick="spanclick('full_masukkerjasenin',false,'full_senindetail')">{{ trans('all.senin') }}</span></td>
                                            </tr>
                                            <tr class="full_senindetail" @if($jamkerjafull->_2_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_2_masukkerja == "y") value="{{ $jamkerjafull->_2_jammasuk }}" @endif id="full_jammasuksenin" name="full_jammasuksenin">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_2_masukkerja == "y") value="{{ $jamkerjafull->_2_jampulang }}" @endif id="full_jampulangsenin" name="full_jampulangsenin">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_senindetail" @if($jamkerjafull->_2_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatsenin">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatsenin != '')
                                                            @foreach($jamistirahatsenin as $key)
                                                                <tr id="rowjamistirahatsenin_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaisenin_{{ $i }}" name="full_istirahatmulaisenin[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaisenin_{{ $i }}" name="full_istirahatselesaisenin[]">
                                                                                </td>
                                                                                <td style="padding:0;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('senin',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_senindetail" @if($jamkerjafull->_2_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatsenin" onclick="tambahjamistirahat('senin')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjaselasa" id="full_masukkerjaselasa" value="y" onclick="checkboxclick('full_masukkerjaselasa',false,'full_selasadetail')" @if($jamkerjafull->_3_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjaselasa" onclick="spanclick('full_masukkerjaselasa',false,'full_selasadetail')">{{ trans('all.selasa') }}</span></td>
                                            </tr>
                                            <tr class="full_selasadetail" @if($jamkerjafull->_3_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_3_masukkerja == "y") value="{{ $jamkerjafull->_3_jammasuk }}" @endif id="full_jammasukselasa" name="full_jammasukselasa">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_3_masukkerja == "y") value="{{ $jamkerjafull->_3_jampulang }}" @endif id="full_jampulangselasa" name="full_jampulangselasa">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_selasadetail" @if($jamkerjafull->_3_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatselasa">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatselasa != '')
                                                            @foreach($jamistirahatselasa as $key)
                                                                <tr id="rowjamistirahatselasa_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaiselasa_{{ $i }}" name="full_istirahatmulaiselasa[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaiselasa_{{ $i }}" name="full_istirahatselesaiselasa[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('selasa',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_selasadetail" @if($jamkerjafull->_3_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatselasa" onclick="tambahjamistirahat('selasa')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjarabu" id="full_masukkerjarabu" value="y" onclick="checkboxclick('full_masukkerjarabu',false,'full_rabudetail')" @if($jamkerjafull->_4_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjarabu" onclick="spanclick('full_masukkerjarabu',false,'full_rabudetail')">{{ trans('all.rabu') }}</span></td>
                                            </tr>
                                            <tr class="full_rabudetail" @if($jamkerjafull->_4_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_4_masukkerja == "y") value="{{ $jamkerjafull->_4_jammasuk }}" @endif id="full_jammasukrabu" name="full_jammasukrabu">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_4_masukkerja == "y") value="{{ $jamkerjafull->_4_jampulang }}" @endif id="full_jampulangrabu" name="full_jampulangrabu">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_rabudetail" @if($jamkerjafull->_4_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatrabu">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatrabu != '')
                                                            @foreach($jamistirahatrabu as $key)
                                                                <tr id="rowjamistirahatrabu_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulairabu_{{ $i }}" name="full_istirahatmulairabu[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesairabu_{{ $i }}" name="full_istirahatselesairabu[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('rabu',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_rabudetail" @if($jamkerjafull->_4_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatrabu" onclick="tambahjamistirahat('rabu')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjakamis" id="full_masukkerjakamis" value="y" onclick="checkboxclick('full_masukkerjakamis',false,'full_kamisdetail')" @if($jamkerjafull->_5_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjakamis" onclick="spanclick('full_masukkerjakamis',false,'full_kamisdetail')">{{ trans('all.kamis') }}</span></td>
                                            </tr>
                                            <tr class="full_kamisdetail" @if($jamkerjafull->_5_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_5_masukkerja == "y") value="{{ $jamkerjafull->_5_jammasuk }}" @endif id="full_jammasukkamis" name="full_jammasukkamis">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_5_masukkerja == "y") value="{{ $jamkerjafull->_5_jampulang }}" @endif id="full_jampulangkamis" name="full_jampulangkamis">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_kamisdetail" @if($jamkerjafull->_5_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatkamis">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatkamis != '')
                                                            @foreach($jamistirahatkamis as $key)
                                                                <tr id="rowjamistirahatkamis_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaikamis_{{ $i }}" name="full_istirahatmulaikamis[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaikamis_{{ $i }}" name="full_istirahatselesaikamis[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('kamis',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_kamisdetail" @if($jamkerjafull->_5_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatkamis" onclick="tambahjamistirahat('kamis')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjajumat" id="full_masukkerjajumat" value="y" onclick="checkboxclick('full_masukkerjajumat',false,'full_jumatdetail')" @if($jamkerjafull->_6_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjajumat" onclick="spanclick('full_masukkerjajumat',false,'full_jumatdetail')">{{ trans('all.jumat') }}</span></td>
                                            </tr>
                                            <tr class="full_jumatdetail" @if($jamkerjafull->_6_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_6_masukkerja == "y") value="{{ $jamkerjafull->_6_jammasuk }}" @endif id="full_jammasukjumat" name="full_jammasukjumat">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_6_masukkerja == "y") value="{{ $jamkerjafull->_6_jampulang }}" @endif id="full_jampulangjumat" name="full_jampulangjumat">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_jumatdetail" @if($jamkerjafull->_6_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatjumat">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatjumat != '')
                                                            @foreach($jamistirahatjumat as $key)
                                                                <tr id="rowjamistirahatjumat_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaijumat_{{ $i }}" name="full_istirahatmulaijumat[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaijumat_{{ $i }}" name="full_istirahatselesaijumat[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('jumat',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_jumatdetail" @if($jamkerjafull->_6_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatjumat" onclick="tambahjamistirahat('jumat')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjasabtu" id="full_masukkerjasabtu" value="y" onclick="checkboxclick('full_masukkerjasabtu',false,'full_sabtudetail')" @if($jamkerjafull->_7_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjasabtu" onclick="spanclick('full_masukkerjasabtu',false,'full_sabtudetail')">{{ trans('all.sabtu') }}</span></td>
                                            </tr>
                                            <tr class="full_sabtudetail" @if($jamkerjafull->_7_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_7_masukkerja == "y") value="{{ $jamkerjafull->_7_jammasuk }}" @endif id="full_jammasuksabtu" name="full_jammasuksabtu">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_7_masukkerja == "y") value="{{ $jamkerjafull->_7_jampulang }}" @endif id="full_jampulangsabtu" name="full_jampulangsabtu">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_sabtudetail" @if($jamkerjafull->_7_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatsabtu">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatsabtu != '')
                                                            @foreach($jamistirahatsabtu as $key)
                                                                <tr id="rowjamistirahatsabtu_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaisabtu_{{ $i }}" name="full_istirahatmulaisabtu[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaisabtu_{{ $i }}" name="full_istirahatselesaisabtu[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('sabtu',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_sabtudetail" @if($jamkerjafull->_7_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatsabtu" onclick="tambahjamistirahat('sabtu')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><input type="checkbox" name="full_masukkerjaminggu" id="full_masukkerjaminggu" value="y" onclick="checkboxclick('full_masukkerjaminggu',false,'full_minggudetail')" @if($jamkerjafull->_1_masukkerja == "y") checked @endif>&nbsp;&nbsp;<span class="tebal full_masukkerjaminggu" onclick="spanclick('full_masukkerjaminggu',false,'full_minggudetail')">{{ trans('all.minggu') }}</span></td>
                                            </tr>
                                            <tr class="full_minggudetail" @if($jamkerjafull->_1_masukkerja == "t") style="display:none" @endif>
                                                <td width="100px">{{ trans('all.waktukerja') }}</td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;float:left">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_1_masukkerja == "y") value="{{ $jamkerjafull->_1_jammasuk }}" @endif id="full_jammasukminggu" name="full_jammasukminggu">
                                                            </td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                            <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                <input type="text" size="7" class="form-control jam" placeholder="hh:mm" @if($jamkerjafull->_1_masukkerja == "y") value="{{ $jamkerjafull->_1_jampulang }}" @endif id="full_jampulangminggu" name="full_jampulangminggu">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr class="full_minggudetail" @if($jamkerjafull->_1_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:2px;">
                                                    <table id="jamistirahatminggu">
                                                        <?php $i=0; ?>
                                                        @if($jamistirahatminggu != '')
                                                            @foreach($jamistirahatminggu as $key)
                                                                <tr id="rowjamistirahatminggu_{{ $i }}">
                                                                    <td width="100px" style="padding-left:0px;padding-top:0px;padding-bottom:0px">{{ trans('all.istirahat') }}</td>
                                                                    <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                        <table width="100%">
                                                                            <tr>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamawal }}" id="full_istirahatmulaiminggu_{{ $i }}" name="full_istirahatmulaiminggu[]">
                                                                                </td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px">-</td>
                                                                                <td style="padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px">
                                                                                    <input type="text" size="7" class="form-control jam" placeholder="hh:mm" value="{{ $key->jamakhir }}" id="full_istirahatselesaiminggu_{{ $i }}" name="full_istirahatselesaiminggu[]">
                                                                                </td>
                                                                                <td style="padding:0px;padding-left:5px;padding-top:2px;">
                                                                                    <button type="button" class="btn btn-danger" onclick="hapusjamistirahat('minggu',{{ $i }})"><i class="fa fa-trash"></i></button>
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
                                            <tr class="full_minggudetail" @if($jamkerjafull->_1_masukkerja == "t") style="display:none" @endif>
                                                <td colspan="2" style="padding-top:0">
                                                    <button title="{{ trans('all.tambahjamistirahat') }}" id="tambahjamistirahatminggu" onclick="tambahjamistirahat('minggu')" ke="{{ $i }}" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=2>
                                        <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                        <button type="button" id="kembali" onclick="return ke('../../full')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop