@extends('layouts.master')
@section('title', trans('all.tvdetail'))
@section('content')

	<style>
	td{
		padding:5px;
	}

    .sortcutpilihan{
        display: inline-block;
        padding: 5px;
        margin-top: 10px;
    }
	</style>
    <script src="{{ asset('lib/js/jscolor/jscolor.js') }}"></script>
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

        $('.jam').inputmask( 'h:s' );

        $('#faicon').on('change', function(e) {
            //console.log(e.icon);
            $('#faicon').attr("data-icon", e.icon);
            $('#icon').val(e.icon);
        });

        //$('select[name="pilihwarna"]').simplecolorpicker({picker: true, theme: 'glyphicons'});
        //$('#pilihwarna').simplecolorpicker({picker: true, theme: 'glyphicons'});
        $('select[name="pilihwarna"]').simplecolorpicker({
            picker: true,
            theme: 'glyphicons'
        }).on('change', function() {
            $('#warna').val($('#pilihwarna').next().attr('title'));
            //alert($('#warna').val());
        });
    });

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');

        var urutan = $("#urutan").val();
        var tv = $("#tv").val();
        var tvgroup = $("#tvgroup").val();

        @if(Session::has('conf_webperusahaan'))
        @else
            alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
                function() {
                    aktifkanTombol();
                }
            );
            return false;
        @endif

        if (cekAlertAngkaValid(urutan,0,999,0,"{{ trans('all.urutan') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#urutan'));
                }
            )==false) return false;

        if(tv == ""){
            alertWarning("{{ trans('all.tvkosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tv'));
                });
            return false;
        }

        if(tvgroup == ""){
            alertWarning("{{ trans('all.tvgroupkosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tvgroup'));
                });
            return false;
        }
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.tvdetail') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.pengaturan') }}</li>
            <li>{{ trans('all.tvdetail') }}</li>
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
          	<form action="{{ url('pengaturan/tvdetail') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="100px">{{ trans('all.urutan') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="7" onkeypress="return onlyNumber(0,event)" autofocus autocomplete="off" name="urutan" id="urutan" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tv') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="tv" id="tv">
                                <option value=""></option>
                                @if($datatv != '')
                                    @foreach($datatv as $key)
                                        <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tvgroup') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="tvgroup" id="tvgroup">
                                <option value=""></option>
                                @if($datatvgroup != '')
                                    @foreach($datatvgroup as $key)
                                        <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../tvdetail')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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