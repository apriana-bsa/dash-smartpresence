@extends('layouts.master')
@section('title', trans('all.perusahaan'))
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
                  toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    });
  @endif
  
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');

	    var nama = $("#nama").val();
		var kode = $("#kode").val();

		if(nama == ""){
			alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#nama'));
            });
            return false;
		}

		if(kode == ""){
			alertWarning("{{ trans('all.kodekosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#kode'));
            });
            return false;
		}
	}

	function readURL(input, jenis) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
//          console.log(e.target.result);
          $('#preview_'+jenis).attr('src', e.target.result);
      };
      
      reader.readAsDataURL(input.files[0]);
      $('#hapus'+jenis).removeAttr('disabled');
    }
  }

	$(function(){
		$("#foto").change(function(){
          readURL(this, 'foto');
        });

        $("#logoemployee").change(function(){
            readURL(this, 'logoemployee');
        });

        $("#logodatacapture").change(function(){
            readURL(this, 'logodatacapture');
        });
    
        $('#hapusfoto').click(function(){
            @if($adafoto == true)
                alertConfirm("{{ trans('all.apakahanadayakinakanmenghapusfoto') }} ?",
                  function(){
                    window.location.href="{{ url('hapusfoto/perusahaan/'.$perusahaan->id) }}";
                  }
                );
            @else
                $('#foto').val('');
                $('#preview_foto').attr('src', '{{ url('foto/perusahaan/'.$perusahaan->id) }}');
                $(this).attr('disabled', 'disabled');
                return false;
            @endif
        });
    
        $('#hapuslogoemployee').click(function(){
            @if($adalogoemployee == true)
                alertConfirm("{{ trans('all.apakahanadayakinakanmenghapusfoto') }} ?",
                  function(){
                    window.location.href="{{ url('hapuslogoperusahaan/'.$perusahaan->id.'/employee') }}";
                  }
                );
            @else
                $('#logoemployee').val('');
                $('#preview_logoemployee').attr('src', '{{ url('foto/perusahaan/'.$perusahaan->id) }}');
                $(this).attr('disabled', 'disabled');
                return false;
            @endif
        });

        $('#hapuslogodatacapture').click(function(){
            @if($adalogodatacapture == true)
                alertConfirm("{{ trans('all.apakahanadayakinakanmenghapusfoto') }} ?",
                function(){
                    window.location.href="{{ url('hapuslogoperusahaan/'.$perusahaan->id.'/datacapture') }}";
                }
            );
            @else
                $('#logodatacapture').val('');
                $('#preview_logodatacapture').attr('src', '{{ url('foto/perusahaan/'.$perusahaan->id) }}');
                $(this).attr('disabled', 'disabled');
                return false;
            @endif
        });
	});
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.perusahaan') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.perusahaan') }}</li>
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
	          <form action="../{{ $perusahaan->id }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="put">
                @if($perusahaan->status == 'c')
                    <input type="hidden" name="status" value="c">
                @endif
                <table width="640px">
                    @if($perusahaan->status != 'c')
                        <tr>
                          {{--<td>{{ trans('all.foto') }}</td>--}}
                          {{--<td>--}}
                              {{--<a href="{{ url('fotonormal/perusahaan/'.$perusahaan->id) }}" title="{{ $perusahaan->nama }}" data-gallery="">--}}
                                {{--<img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/perusahaan/'.$perusahaan->id) }}">--}}
                              {{--</a>--}}
                          {{--</td>--}}
                        {{--</tr>--}}
                        {{--<tr>--}}
                          {{--<td></td>--}}
                          {{--<td>--}}
                            {{--<table>--}}
                              {{--<tr>--}}
                                {{--<td><input type='file' name='foto' id='foto' class="filestyle"  data-badge="false" data-input="false"></td>--}}
                                {{--<td style='padding-left:5px;'><i @if($adafoto == false) disabled @endif class='glyphicon glyphicon-trash btn btn-default' title='{{ trans("all.hapusfoto")}}' name='hapusfoto' id='hapusfoto'></i></td>--}}
                              {{--</tr>--}}
                            {{--</table>--}}
                          {{--</td>--}}
                          <td colspan="2">
                              <table>
                                  <tr>
                                      <td width="212px">
                                          <table>
                                              <tr><td><center>{{ trans('all.logodashboard') }}</center></td></tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <a href="{{ url('fotonormal/perusahaan/'.$perusahaan->id) }}" title="{{ $perusahaan->nama }}" data-gallery="">
                                                              <img id="preview_foto" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/perusahaan/'.$perusahaan->id) }}">
                                                          </a>
                                                      </center>
                                                  </td>
                                              </tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <table>
                                                              <tr>
                                                                  <td><input type='file' name='foto' id='foto' class="filestyle"  data-badge="false" data-input="false"></td>
                                                                  <td style='padding-left:5px;'><i @if($adafoto == false) disabled @endif class='glyphicon glyphicon-trash btn btn-default' title='{{ trans("all.hapuslogoemployee")}}' name='hapusfoto' id='hapusfoto'></i></td>
                                                              </tr>
                                                          </table>
                                                      </center>
                                                  </td>
                                              </tr>
                                          </table>
                                      </td>
                                      <td width="212px">
                                          <table>
                                              <tr><td><center>{{ trans('all.logoemployeeapp') }}</center></td></tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <a href="{{ url('logoperusahaan/'.$perusahaan->id.'/employee') }}" title="{{ $perusahaan->nama }}" data-gallery="">
                                                              <img id="preview_logoemployee" width=120 style='border-radius: 50%;' height=120 src="{{ url('logoperusahaan/'.$perusahaan->id.'/employee/_thumb') }}">
                                                          </a>
                                                      </center>
                                                  </td>
                                              </tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <table>
                                                              <tr>
                                                                  <td><input type='file' name='logoemployee' id='logoemployee' class="filestyle"  data-badge="false" data-input="false"></td>
                                                                  <td style='padding-left:5px;'><i @if($adalogoemployee == false) disabled @endif class='glyphicon glyphicon-trash btn btn-default' title='{{ trans("all.hapuslogoemployee")}}' name='hapuslogoemployee' id='hapuslogoemployee'></i></td>
                                                              </tr>
                                                          </table>
                                                      </center>
                                                  </td>
                                              </tr>
                                          </table>
                                      </td>
                                      <td width="212px">
                                          <table>
                                              <tr><td><center>{{ trans('all.logodatacaptureapp') }}</center></td></tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <a href="{{ url('logoperusahaan/'.$perusahaan->id.'/datacapture') }}" title="{{ $perusahaan->nama }}" data-gallery="">
                                                              <img id="preview_logodatacapture" width=120 style='border-radius: 50%;' height=120 src="{{ url('logoperusahaan/'.$perusahaan->id.'/datacapture/_thumb') }}">
                                                          </a>
                                                      </center>
                                                  </td>
                                              </tr>
                                              <tr>
                                                  <td>
                                                      <center>
                                                          <table>
                                                              <tr>
                                                                  <td><input type='file' name='logodatacapture' id='logodatacapture' class="filestyle"  data-badge="false" data-input="false"></td>
                                                                  <td style='padding-left:5px;'><i @if($adalogodatacapture == false) disabled @endif class='glyphicon glyphicon-trash btn btn-default' title='{{ trans("all.hapuslogodatacapture")}}' name='hapuslogodatacapture' id='hapuslogodatacapture'></i></td>
                                                              </tr>
                                                          </table>
                                                      </center>
                                                  </td>
                                              </tr>
                                          </table>
                                      </td>
                                  </tr>
                              </table>
                          </td>
                        </tr>
                    @endif
                    <tr>
                        <td width=100px>{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" value="{{ $perusahaan->nama }}" name="nama" id="nama" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.kode') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" name="kode" value="{{ $perusahaan->kode }}" autocomplete="off" id="kode" maxlength="10">
                        </td>
                    </tr>
                    @if($perusahaan->status != 'c')
                        <tr>
                            <td>{{ trans('all.status') }}</td>
                            <td style="float:left">
                                <select class="form-control" name="status" id="status">
                                    <option value="a" @if($perusahaan->status == 'a') selected @endif>{{ trans('all.aktif') }}</option>
                                    <option value="t" @if($perusahaan->status == 't') selected @endif>{{ trans('all.tidakaktif') }}</option>
                                </select>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="2"><b>{{ trans('all.pic') }}</b></td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" value="{{ $perusahaan->pic_nama }}" autocomplete="off" name="pic_nama" id="pic_nama" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.alamat') }}</td>
                        <td>
                            <input type="text" class="form-control" value="{{ $perusahaan->pic_alamat }}" autocomplete="off" name="pic_alamat" id="pic_alamat" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.notelp') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" value="{{ $perusahaan->pic_notelp }}" autocomplete="off" name="pic_notelp" id="pic_notelp" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.email') }}</td>
                        <td>
                            <input type="text" class="form-control" onblur="validateEmail(this);" value="{{ $perusahaan->pic_email }}" autocomplete="off" name="pic_email" id="pic_email" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../../perusahaan')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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