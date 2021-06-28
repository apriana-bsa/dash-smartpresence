@extends('layouts.master')
@section('title', trans('all.slipgaji'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
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

        var postingdatadefault = '';
        @if(Session::has('payrollslipgaji_idpayrollposting'))
            postingdatadefault = '{{ Session::get('payrollslipgaji_idpayrollposting') }}';
        @endif
        getPayrollPosting(postingdatadefault)
    });

    function validasi(jenis){
        if(jenis == 'filter') {
            var kelompok = $('#kelompok').val();
            var payrollposting = $('#payrollposting').val();
            if (kelompok == '') {
                alertWarning("{{ trans('all.kelompok').' '.trans('all.sa_kosong') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#kelompok'))
                    });
                return false;
            }
            if (payrollposting == '') {
                alertWarning("{{ trans('all.postingdata').' '.trans('all.sa_kosong') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#payrollposting'))
                    });
                return false;
            }
        }else if(jenis == 'generate'){
            if($('.checkbox_penerimaankomponenmaster:checked').length == 0){
                alertWarning("{{ trans('all.komponenmasterpenerimaanbelumdipilih') }}");
                return false;
            }
        }
    }

    function getPayrollPosting(postingdatadefault) {
        var kelompok = $('#kelompok').val();
        if(kelompok != '') {
            $.ajax({
                type: "GET",
                url: '{{ url('getpayrollposting') }}/' + kelompok + '/' + postingdatadefault,
                data: '',
                cache: false,
                success: function (response) {
//                    console.log(response);
                    var option = '<option value=""></option>';
                    for(var i = 0;i<response.length;i++){
                        option += '<option value="'+response[i]['id']+'" '+response[i]['selected']+'>'+response[i]['periode']+'</option>';
                    }
                    $('#payrollposting').html(option);
                }
            })
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.slipgaji') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li class="active"><strong>{{ trans('all.slipgaji') }}</strong></li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li><a href="{{ url('datainduk/payroll/slipgaji') }}">{{ trans('all.slipgaji') }}</a></li>
            <li class="active"><a href="{{ url('datainduk/payroll/slipgajiekspor') }}">{{ trans('all.ekspor') }}</a></li>
        </ul>
        <br>
        <form name="form1" method="post" action="" onsubmit="return validasi('filter')">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.kelompok') }}</td>
                    <td>
                        <select class="form-control" id="kelompok" name="kelompok" onchange="return getPayrollPosting()">
                            <option value=""></option>
                            @if($datakelompok != '')
                                @foreach($datakelompok as $key)
                                    @if(Session::has('payrollslipgaji_idkelompok'))
                                        <option value="{{ $key->id }}" @if(Session::get('payrollslipgaji_idkelompok') == $key->id) selected @endif>{{ $key->nama }}</option>
                                    @else
                                        <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>{{ trans('all.postingdata') }}</td>
                    <td>
                        <select class="form-control" id="payrollposting" name="payrollposting"></select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</button>
                    </td>
                </tr>
            </table>
        </form>
        @if(count($datakomponenmaster) > 0)
            <form name="form1" method="post" action="{{ url('datainduk/payroll/payrollslipgaji/generate') }}" onsubmit="return validasi('generate')">
                {{ csrf_field() }}
                <div class="ibox float-e-margins">
                    <div class="ibox-content row">
                        <div class="col-md-12" style="padding:0">
                            <b style="font-size: 24px;">{{ trans('all.komponenmaster') }}</b>
                            <input style="display: none;" type="checkbox" id="checkbox_semuapenerimaankomponenmaster">
                            {{--&nbsp;<span style="cursor: pointer;" onclick="spanallclick('checkbox_semuapenerimaankomponenmaster','checkbox_penerimaankomponenmaster')">[{{ trans('all.pilihsemua') }}]</span>--}}
                        </div>
                        <div class="col-md-12" style="padding:0">
                            <b style="font-size: 18px;">{{ trans('all.penerimaan') }}</b>
                            <input style="display: none;" type="checkbox" id="checkbox_semuapenerimaankomponenmaster">
                            &nbsp;<span style="cursor: pointer;" onclick="spanallclick('checkbox_semuapenerimaankomponenmaster','checkbox_penerimaankomponenmaster')">[{{ trans('all.pilihsemua') }}]</span>
                        </div>
                        @foreach($datakomponenmaster as $key)
                            <div class="col-md-4" style="padding:2px">
                                <input type="checkbox" class="checkbox_penerimaankomponenmaster" value="{{ $key->id }}" name="penerimaan_komponenmaster[]" id="penerimaankomponenmaster_{{ $key->id }}">&nbsp;&nbsp;
                                <span style="cursor: pointer;" onclick="spanClick('penerimaankomponenmaster_{{ $key->id }}')">{{ $key->nama.' ('.$key->kode.')' }}</span>
                            </div>
                        @endforeach
                        <div class="col-md-12" style="padding:0;margin-top:10px">
                            <b style="font-size: 18px;">{{ trans('all.potongan') }}</b>
                            <input style="display: none;" type="checkbox" id="checkbox_semuapotongankomponenmaster">
                            &nbsp;<span style="cursor: pointer;" onclick="spanallclick('checkbox_semuapotongankomponenmaster','checkbox_potongankomponenmaster')">[{{ trans('all.pilihsemua') }}]</span>
                        </div>
                        @foreach($datakomponenmaster as $key)
                            <div class="col-md-4" style="padding:2px">
                                <input type="checkbox" class="checkbox_potongankomponenmaster" value="{{ $key->id }}" name="potongan_komponenmaster[]" id="potongankomponenmaster_{{ $key->id }}">&nbsp;&nbsp;
                                <span style="cursor: pointer;" onclick="spanClick('potongankomponenmaster_{{ $key->id }}')">{{ $key->nama.' ('.$key->kode.')' }}</span>
                            </div>
                        @endforeach
                        <div class="col-md-12" style="padding:2px;margin-top:10px">
                            @if(count($dataatribut) > 0)
                                <p><b>{{ trans('all.atribut') }}</b></p>
                            @endif
                            <div class="col-md-4">
                                @foreach($dataatribut as $key)
                                    <input type="checkbox" id="semuaatribut_{{ $key->id }}" onclick="checkboxallclick('semuaatribut_{{ $key->id }}','attr_{{ $key->id }}')">&nbsp;&nbsp;
                                    <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $key->id }}','attr_{{ $key->id }}')">{{ $key->atribut.' ('.$key->kode.')' }}</span><br>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top:10px;padding:0">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.generateslipgaji') }}</button>
                            {{--<button class="btn btn-primary" type="submit"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;{{ trans('all.generateslipgaji') }}</button>--}}
                            {{--<div class="input-group" style="margin-bottom:10px">--}}
                                {{--<div class="input-group-prepend">--}}
                                    {{--<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" type="button"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.generateslipgaji') }}</button>--}}
                                    {{--<ul class="dropdown-menu">--}}
                                        {{--<li>--}}
                                            {{--<a href="#" onclick="return ekspor('excel')">{{ trans('all.excel') }}</a>--}}
                                        {{--</li>--}}
                                        {{--<li><a href="#" onclick="return ekspor('pdf')">{{ trans('all.pdf') }}</a></li>--}}
                                    {{--</ul>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        </div>
                    </div>
                </div>
            </form>
        @endif
      </div>
    </div>
  </div>
@stop