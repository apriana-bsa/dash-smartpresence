@extends('layouts.master')
@section('title', trans('all.postingdata'))
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
                toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        });
    @endif

    function validasi(){
        var kelompok = $('#kelompok').val();
        if(kelompok == ''){
            alertWarning("{{ trans('all.kelompok').' '.trans('all.sa_kosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#kelompok'))
                });
            return false;
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.postingdata') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li class="active"><strong>{{ trans('all.postingdata') }}</strong></li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <form name="form1" method="post" action="" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.kelompok') }}</td>
                    <td>
                        <select class="form-control" id="kelompok" name="kelompok" onchange="this.form.submit()">
                            <option value=""></option>
                            @if($datakelompok != '')
                                @foreach($datakelompok as $key)
                                    @if(Session::has('postingdata_idkelompok'))
                                        <option value="{{ $key->id }}" @if(Session::get('postingdata_idkelompok') == $key->id) selected @endif>{{ $key->nama }}</option>
                                    @else
                                        <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
            </table>
        </form>
        @if(Session::has('postingdata_idkelompok'))
            <br>
            @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'm') !== false)
                <a href="{!! url('datainduk/payroll/payrollposting/generatepayroll') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.generatepayroll') }}</a>
            @endif
            <br>
            <p></p>
            <br>
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <table width=100% class="table datatable table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                                <td class="opsi4"><b>{{ trans('all.periode') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.pertanggal') }}</b></td>
                                <td class="opsi5" style="text-align: right"><b>{{ trans('all.total') }}</b></td>
                                <td class="keterangan"><b>{{ trans('all.atributnilai') }}</b></td>
                                <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                            </tr>
                        </thead>
                        <tbody>
                            @if($data != '')
                                @foreach($data as $key)
                                    <tr>
                                        <td>
                                            <center>
                                                <i style="cursor: pointer" onclick="ke('{{ url('payrollposting/excel/'.$key->id) }}')" class="fa fa-file-excel-o"></i>&nbsp;&nbsp;
                                                {{--  <i style="cursor: pointer" onclick="ke('{{ url('payrollposting/pdf/'.$key->id) }}')" class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;  --}}
                                                @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'm') !== false)
                                                    <i style="cursor: pointer" onclick="return hapusData({{$key->id}})" class="fa fa-trash"></i>
                                                @endif
                                            </center>
                                        </td>
                                        <td>{{ \App\Utils::periodeCantik($key->periode) }}</td>
                                        <td>{{ \App\Utils::tanggalCantikDariSampai($key->tanggalawal,$key->tanggalakhir) }}</td>
                                        <td style="text-align: right">Rp. {{ number_format($key->total, 0, ',', '.') }}</td>
                                        <td>{{ $key->atributnilai }}</td>
                                        <td>{{ $key->keterangan }}</td>
                                        <td>{{ $key->inserted }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
      </div>
    </div>
  </div>
  <script>
    function hapusData(id){
        alertConfirm('{{trans('all.apakahyakinakanmenghapusdataini')}}',
            function(){
                window.location.href="{{ url('datainduk/payroll/payrollposting/') }}/"+id+'/hapus';
            }
        );
    }
    $(function() {
        $('.datatable').DataTable({
            bStateSave: true,
            columnDefs: [{
                "targets": 0,
                "orderable": false
            }],
            language: lang_datatable,
            order: [[1, 'desc']]
        });
    });
  </script>
@stop