@extends('layouts.master')
@section('title', trans('all.pengaturan'))
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

    $(function(){
        pilihPeriode();
    });
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
      var periode = $("#periode").val();
      var pertanggal = $("#pertanggal").val();
      
      @if(Session::has('conf_webperusahaan'))
      @else
        alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
              function() {
                  aktifkanTombol();
              });
              return false;
      @endif

      if(periode == "pertanggal"){
            if (cekAlertAngkaValid(pertanggal,1,28,0,"{{ trans('all.pertanggal') }}",
            function() {
              aktifkanTombol();
              setFocus($('#pertanggal'));
            }
            )==false) return false;
      }
    }
    
    function pilihPeriode(){
        var periode = $('#periode').val();
        $('.tr_pertanggal').css('display', 'none');
        if(periode == 'pertanggal'){
            $('.tr_pertanggal').css('display', '');
        }
    }

    function hapusFile(){
        alertConfirm('{{ trans('all.alerthapus') }}',function(){ window.location.href="{{ url('datainduk/payroll/payrollpengaturan/hapusfile')  }}" })
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.pengaturan') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li class="active"><strong>{{ trans('all.pengaturan') }}</strong></li>
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
          	<form action="" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="160px">{{ trans('all.periode') }}</td>
                        <td style="float:left">
                            <select name="periode" id="periode" class="form-control" onchange="pilihPeriode()">
                                <option value="bulanan" @if($data->periode == 'bulanan') selected @endif>{{ trans('all.bulanan') }}</option>
                                <option value="pertanggal" @if($data->periode == 'pertanggal') selected @endif>{{ trans('all.pertanggal') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="tr_pertanggal">
                        <td>{{ trans('all.pertanggal') }}</td>
                        <td style="float:left">
                            <input type="text" size="5" class="form-control" onkeypress="return onlyNumber(0,event)" value="{{ $data->pertanggal }}" autocomplete="off" name="pertanggal" id="pertanggal" maxlength="2">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.komponenmastertotal') }}</td>
                        <td style="float:left;min-width:200px">
                            <input type="text" class="form-control" autocomplete="off" name="komponenmaster_total" id="komponenmaster_total">
                            <script type="text/javascript">
                            $(document).ready(function(){
                              $("#komponenmaster_total").tokenInput("{{ url('tokenpayrollkomponenmaster') }}", {
                                theme: "facebook",
                                tokenLimit: 1,
                                prePopulate: [
                                    @if($datakomponenmaster != '')
                                        {id: {{ $datakomponenmaster->id }}, nama: '{{ $datakomponenmaster->nama }}'},
                                    @endif
                                ]
                              });
                            });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 10px" colspan="2"><b><i><u>{{ trans('all.templateheaderfootereksporpayroll') }}</u></i></b></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table>
                                <tr>
                                    @if($data->templatepayroll != '')
                                        <td>{{ $data->templatepayroll }}</td>
                                        <td><button type="button" onclick="return hapusFile()" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>
                                    @endif
                                    <td><input type="file" name="templatepayroll"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><i>* {{ trans('all.catatanheaderfooterpayrollpengaturan') }}</i></td>
                    </tr>
                    <tr>
                        <td colspan="2"><i>* {{ trans('all.catatanformatheaderfooterpayrollpengaturan') }}</i></td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>
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