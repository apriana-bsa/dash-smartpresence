@extends('layouts.master')
@section('title', trans('all.tukarshift'))
@section('content')


    <script>
        $(document).ready(function() {
            @if(Session::get('message'))
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
            @endif

            $(".pegawaiinput").tokenInput("{{ url('tokenpegawai') }}", {
                theme: "facebook",
                tokenLimit: 1
            });

            $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
                $(this).datepicker('hide');
            });

            $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        });

        function validasi() {

            $('#submit').attr( 'data-loading', '' );
            $('#submit').attr('disabled', 'disabled');

            var pegawai1 = $("#pegawai1").val();
            var tanggal1 = $("#tanggal1").val();
            var pegawai2 = $("#pegawai2").val();
            var tanggal2 = $("#tanggal2").val();

            if(pegawai1 == ""){
                alertWarning("{{ trans('all.pegawaikosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#token-input-pegawai1'));
                        });
                return false;
            }

            if(tanggal1 == ""){
                alertWarning("{{ trans('all.tanggalkosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tanggal1'));
                        });
                return false;
            }

            if(pegawai2 == ""){
                alertWarning("{{ trans('all.pegawaikosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#token-input-pegawai2'));
                        });
                return false;
            }

            if(tanggal2 == ""){
                alertWarning("{{ trans('all.tanggalkosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tanggal2'));
                        });
                return false;
            }
        }

        function loadShift(dari){
            if(dari == 1){
                var pegawai = $('#pegawai1').val();
                var tanggal = $('#tanggal1').val();
            }else{
                var pegawai = $('#pegawai2').val();
                var tanggal = $('#tanggal2').val();
            }

            if(pegawai == ""){
                alertWarning("{{ trans('all.pegawaikosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#token-input-pegawai'+dari));
                        });
                return false;
            }

            if(tanggal == ""){
                alertWarning("{{ trans('all.tanggalkosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tanggal'+dari));
                        });
                return false;
            }

            $.ajax({
                type: "GET",
                url: '{{ url('jamkerjashiftpegawai') }}/'+pegawai+'/'+tanggal.replace('/','-').replace('/','-')+'/'+dari,
                data: '',
                cache: false,
                success: function(html){
                    $('#jamkerjashift'+dari).html(html);
                }
            });
        }
    </script>

    <style>
        td{
            padding:5px;
        }

        .dataTables_wrapper{
            padding-bottom:0;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.tukarshift') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.datainduk') }}</li>
                <li>{{ trans('all.absensi') }}</li>
                <li class="active"><strong>{{ trans('all.jadwalshift') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <table>
                    <tr>
                        <td>{{ trans('all.pegawai') }}</td>
                        <td>{{ $pegawai1 }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tanggal') }}</td>
                        <td>{{ $tanggal1 }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.shift') }}</td>
                        <td>{{ $shift1 }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.ditukardengan') }}</td>
                        <td>{{ $tukardengan }}</td>
                    </tr>
                </table>
                <hr>
                <form action="{{ url('datainduk/absensi/tukarshift/submit') }}" method="post" onsubmit="return validasi()">
                    {{csrf_field()}}
                    <input type="hidden" name="idpegawai1" value="{{ $idpegawai1 }}">
                    <input type="hidden" name="idjadwalshift_1" value="{{ $idjadwalshift_1 }}">
                    <input type="hidden" name="tanggal1" value="{{ $tanggal1 }}">
                    <table>
                        <tr>
                            <td width="80px">{{ trans('all.pegawai') }}</td>
                            <td style="min-width:200px" colspan="2">
                                <input type="text" name="pegawai2" class="pegawaiinput form-control" id="pegawai2">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ trans('all.tanggal') }}</td>
                            <td style="float: left;">
                                <input type="text" size="22" name="tanggal2" class="date form-control" id="tanggal2" placeholder="dd/mm/yyyy">
                            </td>
                            <td style="float: left;">
                                <input type="button" value="{{ trans('all.carijadwal') }}" onclick="loadShift(2)" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                    <div id="jamkerjashift2">
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop