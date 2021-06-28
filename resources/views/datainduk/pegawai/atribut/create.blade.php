@extends('layouts.master')
@section('title', trans('all.atribut'))
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
        
		var atribut = $("#atribut").val();
		var tampilpadaringkasan = $("#tampilpadaringkasan").val();
        var penting = $("#penting").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
		@endif

		if(atribut == ""){
			alertWarning("{{ trans('all.atributkosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#atribut'));
            });
            return false;
		}

        if(tampilpadaringkasan == ""){
            alertWarning("{{ trans('all.tampilpadaringkasankosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tampilpadaringkasan'));
                });
            return false;
        }

        if(penting == ""){
            alertWarning("{{ trans('all.pentingkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#penting'));
            });
            return false;
        }

        var nilai = document.getElementsByClassName("nilai");
        for(var i=0; i<nilai.length; i++) {
            if(nilai[i].value == ""){
                alertWarning("{{ trans('all.nilaikosong') }}",
                      function() {
                        aktifkanTombol();
                      });
                return false;
            }
        }
	}

	$(document).ready(function() {
		var option = {
			template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><a class="popover-cancel">&times;</a><div class="popover-content"></div></div>',
			trigger: 'focus click'
		};

		@if ($onboarding)
			$('[data-toggle="popover-atributadd"]').popover(option);
			$('[data-toggle="popover-atributadd"]').popover('show');
			$('[data-toggle="popover-atributadd"]').on('shown.bs.popover', function () {
				var $popup = $(this);
				$(this).next('.popover').find('.popover-cancel').click(function (e) {
						$popup.popover('hide');
				});
            });
            $('[data-toggle="popover-atributopsional"]').popover(option);
			$('[data-toggle="popover-atributopsional"]').popover('show');
			$('[data-toggle="popover-atributopsional"]').on('shown.bs.popover', function () {
				var $popup = $(this);
				$(this).next('.popover').find('.popover-cancel').click(function (e) {
						$popup.popover('hide');
				});
			});
		@endif
	});

	var i = -1;
    function addNilai(){
        i = i + 1;
        $('#tabNilai').append("<tr id='addr_nilai"+i+"'><td style=padding-left:0px;><input autocomplete='off' name='nilai[]' class='form-control nilai' placeholder='{{ trans('all.nilai') }}' type='text' id='nilai"+i+"' maxlength='100'  data-toggle='popover-atribut-nilai"+i+"' data-trigger='focus' data-placement='auto right' data-content='{{ trans('onboarding.tambah_atribut_nilai') }}'></td><td style=padding-left:0px><input autocomplete='off' name='nilaikode[]' size='5' class='form-control kode' placeholder='{{ trans('all.kode') }}' type='text' id='kode"+i+"' maxlength='20'></td><td style=padding-left:0px;width:20px;float:left;margin-top:4px><button onclick='deleteNilai("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button></td></tr>");
        document.getElementById('nilai'+i).focus();
				var option = {
	        template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><a class="popover-cancel">&times;</a><div class="popover-content"></div></div>',
	        // trigger: 'manual'
	      };
				@if ($onboarding)
					$('[data-toggle="popover-atribut-nilai'+i+'"]').popover(option);
					$('[data-toggle="popover-atribut-nilai'+i+'"]').popover('show');
					$('[data-toggle="popover-atribut-nilai'+i+'"]').on('shown.bs.popover', function () {
						var $popup = $(this);
						$(this).next('.popover').find('.popover-cancel').click(function (e) {
								$popup.popover('hide');
						});
					});
				@endif
    }

    function deleteNilai(index){
        $("#addr_nilai"+index).remove();
        i--;
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.atribut') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.kepegawaian') }}</li>
            <li>{{ trans('all.atribut') }}</li>
        <li class="active"><strong>{{ trans('all.tambahdata') }}</strong></li>
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
          	<form action="{{ $onboarding ? url('datainduk/pegawai/atribut?onboarding=true') : url('datainduk/pegawai/atribut')}}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="150px">{{ trans('all.atribut') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="atribut" id="atribut" maxlength="100" data-toggle="popover-atributadd" data-placement="auto right" data-content="{{ trans('onboarding.tambah_atribut') }}">
                        </td>
                    </tr>
                    <tr>
                        <td width="110px">{{ trans('all.kode') }}</td>
                        <td style="float:left">
                            <input type="text" size="25" class="form-control" autocomplete="off" name="kode" id="kode" maxlength="20" data-toggle="popover-atributopsional" data-placement="auto right" data-content="{{ trans('onboarding.opsional') }}">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampilpadaringkasan') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="tampilpadaringkasan" id="tampilpadaringkasan">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.penting') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="penting" id="penting">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.jumlahinputan') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jumlahinputan" id="jumlahinputan">
                                <option value="satu">{{ trans('all.satu') }}</option>
                                <option value="multiple">{{ trans('all.multiple') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td valign=top style="padding-top:15px">{{ trans('all.nilai') }}</td>
                        <td>
                            <table width=100% id='tabNilai'></table>
                            <table>
                            <tr>
                                <td style='padding-left:0px;' colspan=2>
                                    <a id="tambahnilai" title="{{ trans('all.tambahnilai') }}" onclick='addNilai()' class="btn btn-success glyphicon glyphicon-plus"></a>
                                </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../atribut')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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