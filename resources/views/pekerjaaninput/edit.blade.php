@extends('layouts.master')
@section('title', trans('all.pekerjaanuser'))
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
                toastr.warning('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);

		@endif

		$('.date').mask("00/00/0000", {clearIfNotMatch: true});

		$('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
			$(this).datepicker('hide');
		});
	});
	
	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');

        var tanggal = $("#tanggal").val();
        var pekerjaan = $("#pekerjaan").val();
        var pegawai = $("#pegawai").val();
        var satuan = $("#satuan").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      		return false;
		@endif

        if(tanggal == ""){
            alertWarning("{{ trans('all.tanggal').' '.trans('all.sa_kosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggal'));
                });
            return false;
        }

        if(pekerjaan == ""){
            alertWarning("{{ trans('all.pekerjaan').' '.trans('all.sa_kosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pekerjaan'));
                });
            return false;
        }

        if(pegawai == ""){
            alertWarning("{{ trans('all.pegawaikosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#token-input-pegawai'));
                });
            return false;
        }
	}

    function pilihPekerjaan() {
        $('#tabelitempekerjaan').html('');
        var pekerjaankategori = $('#pekerjaankategori').val();
        if(pekerjaankategori != '') {
            $.ajax({
                type: "GET",
                url: "{{ url('getpekerjaanitem') }}/" + pekerjaankategori,
                data: '',
                cache: false,
                success: function (data) {
                    var itempekerjaan = '';
                    if(data.length > 0){
                        for(var i = 0;i<data.length;i++){
                            itempekerjaan += '<tr>' +
                                                '<td style="width:110px">'+data[i]['item']+'<input type="hidden" name="idpekerjaanitem[]" value="'+data[i]['id']+'"></td>' +
                                                '<td><input type="text" autocomplete="off" name="jumlahitem[]" class="form-control"></td>' +
                                                '<td>'+data[i]['satuan']+'</td>' +
                                              '</tr>';
                        }
                    }
                    $('#tabelitempekerjaan').html(itempekerjaan);
                }
            });
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.pekerjaanuser') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pekerjaanuser') }}</li>
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
          	<form action="../{{ $data->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width="110px">{{ trans('all.tanggal') }}</td>
						<td style="float:left">
							<input type="text" class="form-control date" autofocus autocomplete="off" value='{{ date_format(date_create($data->tanggal), "d/m/Y") }}' name="tanggal" id="tanggal" size="10" maxlength="10">
						</td>
					</tr>
                    <tr>
                        <td width="110px">{{ trans('all.pegawai') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
                                        theme: "facebook",
                                        tokenLimit: 1,
                                        prePopulate: [
                                            @if($data->idpegawai != '')
                                                {id: {{ $data->idpegawai }}, nama: '{{ \App\Utils::getNamaPegawai($data->idpegawai) }}'}
                                            @endif
                                        ]
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.keterangan') }}</td>
                        <td>
                            <textarea style="resize:none" name="keterangan" id="keterangan" class="form-control">{{ $data->keterangan }}</textarea>
                        </td>
                    </tr>
                    <tr>
						<td>{{ trans('all.pekerjaan') }}</td>
						<td style="float:left">
                            <select class="form-control" id="pekerjaankategori" name="pekerjaankategori" onchange="return pilihPekerjaan()">
                                @if($datapekerjaankategori != '')
                                    @foreach($datapekerjaankategori as $key)
                                        <option value="{{ $key->id }}" @if($data->idpekerjaankategori == $key->id) selected @endif>{{ $key->nama }}</option>
                                    @endforeach
                                @endif
							</select>
						</td>
					</tr>
					{{--<tr>--}}
						{{--<td>{{ trans('all.satuan') }}</td>--}}
						{{--<td style="float:left">--}}
							{{--<input type="text" class="form-control" value="{{ $data->satuan }}" onkeypress="return onlyNumber(1,event)" size="10" name="satuan" autocomplete="off" id="satuan" maxlength="9">--}}
						{{--</td>--}}
						{{--<td style="float:left;margin-top:7px;" id="spansatuan"></td>--}}
					{{--</tr>--}}
                </table>
                <table id="tabelitempekerjaan">
                    @if(count($datapekerjaaniteminput) > 0)
                        @foreach($datapekerjaaniteminput as $key)
                            <tr>
                                <td style="width:110px">{{ $key->item }}<input type="hidden" name="idpekerjaanitem[]" value="{{ $key->idpekerjaanitem }}"></td>
                                <td><input type="text" autocomplete="off" name="jumlahitem[]" value="{{ $key->jumlah }}" class="form-control"></td>
                                <td>{{ $key->satuan }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
                <table>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../pekerjaaninput')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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