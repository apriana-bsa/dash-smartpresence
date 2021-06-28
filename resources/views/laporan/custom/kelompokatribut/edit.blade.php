@extends('layouts.master')
@section('title', trans('all.kelompok'))
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
            getAtributNilai()
        });

        function validasi(){
            $('#submit').attr( 'data-loading', '' );
            $('#submit').attr('disabled', 'disabled');
            $('#kembali').attr('disabled', 'disabled');

            var atribut = $("#atribut").val();
            var atributnilai = $("#atributnilai").val();

            @if(Session::has('conf_webperusahaan'))
            @else
                alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
                function() {
                    aktifkanTombol();
                });
            return false;
			@endif

            if(atribut == ""){
                alertWarning("{{ trans('all.atribut').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#atribut'));
                    });
                return false;
            }

            if(atributnilai == ""){
                alertWarning("{{ trans('all.atributnilai').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#atributnilai'));
                    });
                return false;
            }
        }

        function getAtributNilai() {
            var atribut = $('#atribut').val();
            $('#atributnilai').html('<option value=""></option>');
            if(atribut != '') {
                $.ajax({
                    type: "GET",
                    url: '{{ url('getatributnilai/combobox') }}/' + atribut + '/{{ $data->idatributnilai }}',
                    data: '',
                    cache: false,
                    success: function (data) {
                        $('#atributnilai').html('').html(data);
                    }
                });
            }
        }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
		<div class="col-lg-10">
			<h2>{{ trans('all.kelompok') }}</h2>
			<ol class="breadcrumb">
				<li>{{ trans('all.laporan') }}</li>
				<li>{{ trans('all.custom') }}</li>
				<li>{{ trans('all.kelompok') }}</li>
				<li>{{ trans('all.atribut') }}</li>
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
							{{ csrf_field() }}
							<input type="hidden" name="_method" value="put">
							<table width="480px">
								<tr>
									<td width="110px">{{ trans('all.atribut') }}</td>
									<td style="float:left">
										<select id="atribut" class="form-control" onchange="getAtributNilai()">
											<option value=""></option>
											@if($dataatribut != '')
												@foreach($dataatribut as $key)
													<option value="{{ $key->id }}" @if($idatributselected == $key->id) selected @endif>{{ $key->nama }}</option>
												@endforeach
											@endif
										</select>
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.atributnilai') }}</td>
									<td style="float:left">
										<select id="atributnilai" class="form-control" name="atributnilai"></select>
									</td>
								</tr>
								<tr>
									<td colspan=2>
										<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
										<button type="button" id="kembali" onclick="return ke('../../atribut')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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