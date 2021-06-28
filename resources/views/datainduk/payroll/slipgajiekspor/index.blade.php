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

        $('#payrollposting').select2();
    });

    function validasi(){
        var slipgaji = $('#slipgaji').val();
        var payrollposting = $('#payrollposting').val();
        if (slipgaji == '') {
            alertWarning("{{ trans('all.slipgaji').' '.trans('all.sa_kosong') }}",
                function () {
                    aktifkanTombol();
                    setFocus($('#slipgaji'))
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
    }

    function getPayrollPosting() {
        var slipgaji = $('#slipgaji').val();
        if(slipgaji != '') {
            $.ajax({
                type: "GET",
                url: '{{ url('getpostingdata') }}/' + slipgaji,
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
        <form name="form1" method="post" action="" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.postingdata') }}</td>
                    <td>
                        <select class="form-control" id="payrollposting" name="payrollposting">
                            <option value=""></option>
                            @if($dataposting != '')
                                @foreach($dataposting as $key)
                                    <option value="{{ $key->id }}">{{ \App\Utils::periodeCantik($key->periode).' '.$key->kelompok.' ('.\App\Utils::tanggalCantikDariSampai($key->tanggalawal,$key->tanggalakhir).')' }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                    </td>
                </tr>
            </table>
            @if(count($dataatribut) > 0)
                <p><b>{{ trans('all.atribut') }}</b></p>
            @endif
            @foreach($dataatribut as $atribut)
                @if(count($atribut->atributnilai) > 0)
                    <div class="col-md-4">
                        <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                        <br>
                        @foreach($atribut->atributnilai as $atributnilai)
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
                        @endforeach
                    </div>
                @endif
            @endforeach
        </form>
      </div>
    </div>
  </div>
@stop