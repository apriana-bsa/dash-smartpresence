@extends('layouts.master')
@section('title', trans('all.rekapshift'))
@section('content')

    <script>
    $(function(){
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    });
    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#setulang').attr('disabled', 'disabled');

        var filtermode = $('#filtermode').val();
        if(filtermode == 'berdasarkantanggal'){
            if ($("#berdasarkantanggalinput").prop('checked')) {
            } else {
                $(".tanggalcheck").prop('checked', true);
            }

            var checked = $(".tanggalcheck:checked").length;
            if (checked == 0) {
                alertWarning('{{ trans('all.tanggalkosong') }}');
                return false;
            } else {
                return true;
            }
        }else if(filtermode == 'jangkauantanggal') {
            var tanggalawal = $("#tanggalawal").val();
            var tanggalakhir = $("#tanggalakhir").val();

            if (tanggalawal == '') {
                alertWarning("{{ trans('all.tanggalkosong') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#tanggalawal'));
                    });
                return false;
            }

            if (tanggalakhir == '') {
                alertWarning("{{ trans('all.tanggalkosong') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#tanggalakhir'));
                    });
                return false;
            }
        }
    }

    $(function(){
        setTimeout(lapFilterMode(), 200);
    });
    </script>
    <style type="text/css">
        td{
            padding:5px;
        }

        span{
            cursor:default;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.rekapshift') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.laporan') }}</li>
                <li class="active"><strong>{{ trans('all.rekapshift') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                @if($data == '')
                    <form action="{{ url('laporan/rekapshift') }}" method="post" onsubmit="return validasi()">
                        {{ csrf_field() }}
                        <table>
                            <tr>
                                <td>{{ trans('all.filtertanggal') }}</td>
                                <td style="float:left">
                                    <select id="filtermode" name="filtermode" class="form-control" onchange="return lapFilterMode()">
                                        @if(Session::has('laprekapshift_filtermode'))
                                            <option value="jangkauantanggal" @if(Session::get('laprekapshift_filtermode') == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                                            <option value="berdasarkantanggal" @if(Session::get('laprekapshift_filtermode') == 'berdasarkantanggal') selected @endif>{{ trans('all.berdasarkantanggal') }}</option>
                                        @else
                                            <option value="jangkauantanggal">{{ trans('all.jangkauantanggal') }}</option>
                                            <option value="berdasarkantanggal">{{ trans('all.berdasarkantanggal') }}</option>
                                        @endif
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <table width="100%" id="jangkauantanggal" style="display: none;">
                            <tr>
                                <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                                <td style="float:left">
                                    <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('laprekapshift_tanggalawal')) value="{{ Session::get('laprekapshift_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                                </td>
                                <td style="float:left;margin-top:8px">-</td>
                                <td style="float:left">
                                    <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('laprekapshift_tanggalakhir')) value="{{ Session::get('laprekapshift_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                                </td>
                                <td style="float:left">
                                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                    <button type="button" id="setulang" onclick="ke('setulang/rekapshift')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                                </td>
                            </tr>
                        </table>
                        <table width="100%" id="berdasarkantanggal" style="display: none;">
                            <tr>
                                <td style="width: 50px;">{{ trans('all.bulan') }}</td>
                                <td style="float:left">
                                    <select name="bulan" id="bulan" class="form-control" onchange="return lapPilihBulan()">
                                        <option value="1" @if($bulanterpilih == 1) selected @endif>{{ trans('all.januari') }}</option>
                                        <option value="2" @if($bulanterpilih == 2) selected @endif>{{ trans('all.februari') }}</option>
                                        <option value="3" @if($bulanterpilih == 3) selected @endif>{{ trans('all.maret') }}</option>
                                        <option value="4" @if($bulanterpilih == 4) selected @endif>{{ trans('all.april') }}</option>
                                        <option value="5" @if($bulanterpilih == 5) selected @endif>{{ trans('all.mei') }}</option>
                                        <option value="6" @if($bulanterpilih == 6) selected @endif>{{ trans('all.juni') }}</option>
                                        <option value="7" @if($bulanterpilih == 7) selected @endif>{{ trans('all.juli') }}</option>
                                        <option value="8" @if($bulanterpilih == 8) selected @endif>{{ trans('all.agustus') }}</option>
                                        <option value="9" @if($bulanterpilih == 9) selected @endif>{{ trans('all.september') }}</option>
                                        <option value="10" @if($bulanterpilih == 10) selected @endif>{{ trans('all.oktober') }}</option>
                                        <option value="11" @if($bulanterpilih == 11) selected @endif>{{ trans('all.november') }}</option>
                                        <option value="12" @if($bulanterpilih == 12) selected @endif>{{ trans('all.desember') }}</option>
                                    </select>
                                </td>
                                <td style="float:left;margin-top:8px">{{ trans('all.tahun') }}</td>
                                <td style="float:left">
                                    {{--<select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">--}}
                                    <select class="form-control" name="tahun" id="tahun">
                                        <option value="{{ $tahun->tahun1 }}" @if($tahunterpilih == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                                        <option value="{{ $tahun->tahun2 }}" @if($tahunterpilih == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                                        <option value="{{ $tahun->tahun3 }}" @if($tahunterpilih == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                                        <option value="{{ $tahun->tahun4 }}" @if($tahunterpilih == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                                        <option value="{{ $tahun->tahun5 }}" @if($tahunterpilih == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                                    </select>
                                </td>
                                <td style="float: left;">
                                    <button type="submit" class="btn btn-primary">{{ trans('all.tampilkan') }}</button>&nbsp;&nbsp;
                                    <button type="button" id="setulang" onclick="ke('setulang/logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <input type="checkbox" id="berdasarkantanggalinput" onclick="lapPilihTanggal('input')">
                                    <span class="spancheckbox" onclick="lapPilihTanggal('span')"><b>{{ trans('all.berdasarkantanggal') }}</b></span>
                                </td>
                            </tr>
                            <tr class="pilihtanggal" style="display:none">
                                <td colspan="4">
                                    @for($i=1;$i<=15;$i++)
                                        <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}"><span onclick="spanClick('tanggal_{{ $i }}')">&nbsp;&nbsp;{{ $i }}</span>&nbsp;&nbsp;
                                    @endfor
                                </td>
                            </tr>
                            <tr class="pilihtanggal" style="display:none">
                                <td colspan="4" id="changeable_pilihtanggal">
                                    @for($i=16;$i<=$totalhari;$i++)
                                        <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}"><span onclick="spanClick('tanggal_{{ $i }}')">&nbsp;&nbsp;{{ $i }}</span>&nbsp;&nbsp;
                                    @endfor
                                </td>
                            </tr>
                        </table>
                        @if(count($atributs) > 0)
                            <div class="col-md-12" style="padding-left:0;margin-top:5px;"><p><b>{{ trans('all.atribut') }}</b></p></div>
                        @endif
                        @foreach($atributs as $atribut)
                            @if(count($atribut->atributnilai) > 0)
                                <div class="col-md-4">
                                    <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                                    <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                                    <br>
                                    @foreach($atribut->atributnilai as $atributnilai)
                                        @if(Session::has('laprekapshift_atribut'))
                                            {{ $checked = false }}
                                            @for($i=0;$i<count(Session::get('laprekapshift_atribut'));$i++)
                                                @if($atributnilai->id == Session::get('laprekapshift_atribut')[$i])
                                                    <span style="display:none">{{ $checked = true }}</span>
                                                @endif
                                            @endfor
                                            <div style="padding-left:15px">
                                                <table>
                                                    <tr>
                                                        <td valign="top" style="width:10px;">
                                                            <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                        </td>
                                                        <td valign="top">
                                                            <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @else
                                            <div style="padding-left:15px">
                                                <table>
                                                    <tr>
                                                        <td valign="top" style="width:10px;">
                                                            <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                        </td>
                                                        <td valign="top">
                                                            <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </form>
                @else
                    <div class="ibox float-e-margins">
                        @if($totaldata > 0)
                            <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/rekapshift/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                        @endif
                        <button onclick="ke('rekapshift')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        <p></p>
                        <div class="alert alert-danger">
                            <center>
                                {{ $keterangan }}
                            </center>
                        </div>
                        <div class="ibox-content">
                            <table width=100% class="table datatable table-striped table-condensed table-hover">
                                <thead>
                                <tr>
                                    <td class="opsi2"><b>{{ trans('all.tanggal') }}</b></td>
                                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                    <td class="opsi5"><b>{{ trans('all.pin') }}</b></td>
                                    @if($atributvariablepenting_blade != '')
                                        @foreach($atributvariablepenting_blade as $key)
                                            @if($key != '')
                                                <td class="nama"><b>{{ $key }}</b></td>
                                            @endif
                                        @endforeach
                                    @endif
                                    <td class="opsi2"><center><b>{{ trans('all.masukkerja') }}</b></center></td>
                                    <td class="alamat"><b>{{ trans('all.jamkerjashift') }}</b></td>
                                    <td class="opsi4"><b>{{ trans('all.waktumasuk') }}</b></td>
                                    <td class="opsi4"><b>{{ trans('all.waktukeluar') }}</b></td>
                                    <td class="opsi1"><b>{{ trans('all.terlambat') }}</b></td>
                                    <td class="opsi4"><b>{{ trans('all.pulanglebihawal') }}</b></td>
                                    <td class="opsi2"><b>{{ trans('all.lamakerja') }}</b></td>
                                    <td class="opsi2"><b>{{ trans('all.lamalembur') }}</b></td>
                                    @if($atributpenting_blade != '')
                                        @foreach($atributpenting_blade as $key)
                                            @if($key != '')
                                                <td class="nama"><b>{{ $key }}</b></td>
                                            @endif
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

    <!-- Modal pegawai-->
    <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
    <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-md">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal pegawai-->

@stop

@push('scripts')
<script>
    window.detailpegawai=(function(idpegawai){
        $("#showmodalpegawai").attr("href", "");
        $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
        $('#showmodalpegawai').trigger('click');
        return false;
    });

    $('body').on('hidden.bs.modal', '.modalpegawai', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });

    @if($data != '')
        $(function() {
            $('.datatable').DataTable({
                processing: true,
                bStateSave: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{!! url("laporan/rekapshift/index-data") !!}',
                    type: "POST",
                    data: { _token: '{!! csrf_token() !!}' }
                },
                language: lang_datatable,
                columns: [
                    { data: 'tanggal', name: 'tanggal',
                        render: function (data) {
                            var ukDate = data.split('-');
                            return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                        }
                    },
                    { data: 'nama', name: 'nama' },
                    { data: 'pin', name: 'pin' },
                    @if($atributvariablepenting_controller != '')
                        @foreach($atributvariablepenting_controller as $key)
                            @if($key != '')
                                { data: '{{ $key }}', name: '{{ $key }}' },
                            @endif
                        @endforeach
                    @endif
                    { data: 'masukkerja', name: 'masukkerja' },
                    { data: 'jamkerjashift', name: 'jamkerjashift' },
                    { data: 'waktumasuk', name: 'waktumasuk',
                        render: function (data) {
                            if(data != null){
                                var ukDateTime = data.split(' ');
                                //var ukDate = ukDateTime[0].split('-');
                                //return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                                return ukDateTime[1];
                            }else{
                                return data;
                            }
                        }
                    },
                    { data: 'waktukeluar', name: 'waktukeluar',
                        render: function (data) {
                            if(data != null){
                                var ukDateTime = data.split(' ');
                                //var ukDate = ukDateTime[0].split('-');
                                //return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                                return ukDateTime[1];
                            }else{
                                return data;
                            }
                        }
                    },
                    { data: 'terlambat', name: 'terlambat' },
                    { data: 'pulanglebihawal', name: 'pulanglebihawal' },
                    { data: 'lamakerja', name: 'lamakerja' },
                    { data: 'lamalembur', name: 'lamalembur' },
                    @if($atributpenting_controller != '')
                        @foreach($atributpenting_controller as $key)
                            @if($key != '')
                                { data: '{{ $key }}', name: '{{ $key }}' },
                            @endif
                        @endforeach
                    @endif
                ],
                order: [[0, 'desc']]
            });
        });
    @endif
</script>
@endpush