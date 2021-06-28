@extends('layouts/master')
@section('title', trans('all.perusahaan'))
@section('content')

	@if(Session::get('message'))
		<script>
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
		</script>
	@endif
	<style>
	td{
		padding:5px;
	}
	</style>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script>
    function utf8_decode (strData) { // eslint-disable-line camelcase
        //  discuss at: http://locutus.io/php/utf8_decode/
        // original by: Webtoolkit.info (http://www.webtoolkit.info/)
        //    input by: Aman Gupta
        //    input by: Brett Zamir (http://brett-zamir.me)
        // improved by: Kevin van Zonneveld (http://kvz.io)
        // improved by: Norman "zEh" Fuchs
        // bugfixed by: hitwork
        // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
        // bugfixed by: Kevin van Zonneveld (http://kvz.io)
        // bugfixed by: kirilloid
        // bugfixed by: w35l3y (http://www.wesley.eti.br)
        //   example 1: utf8_decode('Kevin van Zonneveld')
        //   returns 1: 'Kevin van Zonneveld'

        var tmpArr = []
        var i = 0
        var c1 = 0
        var seqlen = 0

        strData += ''

        while (i < strData.length) {
            c1 = strData.charCodeAt(i) & 0xFF
            seqlen = 0

            // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
            if (c1 <= 0xBF) {
                c1 = (c1 & 0x7F)
                seqlen = 1
            } else if (c1 <= 0xDF) {
                c1 = (c1 & 0x1F)
                seqlen = 2
            } else if (c1 <= 0xEF) {
                c1 = (c1 & 0x0F)
                seqlen = 3
            } else {
                c1 = (c1 & 0x07)
                seqlen = 4
            }

            for (var ai = 1; ai < seqlen; ++ai) {
                c1 = ((c1 << 0x06) | (strData.charCodeAt(ai + i) & 0x3F))
            }

            if (seqlen === 4) {
                c1 -= 0x10000
                tmpArr.push(String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF)))
                tmpArr.push(String.fromCharCode(0xDC00 | (c1 & 0x3FF)))
            } else {
                tmpArr.push(String.fromCharCode(c1))
            }

            i += seqlen
        }

        return tmpArr.join('')
    }

    function isUnicode(str) {
        // var encoded = encodeURI(str);
        // console.log(encoded, 'encoded');
        // // expected output: "https://mozilla.org/?x=%D1%88%D0%B5%D0%BB%D0%BB%D1%8B"

        // try {
        // console.log(decodeURI(encoded), 'decoded');
        // // expected output: "https://mozilla.org/?x=шеллы""
        // } catch(e) { // catches a malformed URI
        // console.error(e);
        // }

        if(str.length != encodeURI(str).length){
            alertWarning("{{ trans('all.isikanalphabetsaja') }}",
            function() {
                aktifkanTombol();
            });
            return false;
        }
        // utf8_decode(string);
        // return false;
    }

	function validasi(){
		
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');

		var nama = $("#nama").val();
		var pic_nama = $("#pic_nama").val();
		var pic_alamat = $("#pic_alamat").val();
		var pic_notelp = $("#pic_notelp").val();
		var pic_email = $("#pic_email").val();

        var letters = /^[A-Za-z]+$/;
        if(nama == ""){
			alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#nama'));
            });
      		return false;
		}

        // isUnicode(nama);
        // return false;
        // if(!nama.match(letters)){
        //     alertWarning("{{ trans('all.isikanhurufsaja') }}",
        //         function() {
        //             aktifkanTombol();
        //             setFocus($('#nama'));
        //         });
        //     return false;
        // }

        if(pic_nama == ""){
            alertWarning("{{ trans('all.namapengelolakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_nama'));
                });
            return false;
        }

        {{--  if(!pic_nama.match(letters)){
            alertWarning("{{ trans('all.isikanhurufsaja') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_nama'));
                });
            return false;
        }  --}}

        if(pic_alamat == ""){
            alertWarning("{{ trans('all.alamatpengelolakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_alamat'));
                });
            return false;
        }

        if(pic_notelp == ""){
            alertWarning("{{ trans('all.nomorhpkosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_notelp'));
                });
            return false;
        }

        if(isNaN(pic_notelp)){
            alertWarning("{{ trans('all.nomorhptidakvalid') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_notelp'));
                });
            return false;
        }

        if(pic_email == ""){
            alertWarning("{{ trans('all.emailpengelolakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#pic_email'));
                });
            return false;
        }
		$('#loading-saver').css('display', '');
	}

	function readURL(input) {
    
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          
          reader.onload = function (e) {
              $('#imgInp').attr('src', e.target.result);
          }
          
          reader.readAsDataURL(input.files[0]);
          $('#hapusfoto').removeAttr('disabled');
        }
    }

    function validateEmail(emailField){
        if(emailField.value != '') {
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

            if (reg.test(emailField.value) == false) {
                alertWarning("{{ trans('all.emailtidakvalid') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#email'));
                    });
                return false;
            }
        }

        return true;

    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      @if(Session::get('conf_totalperusahaan') > 0)
      	<h2>{{ trans('all.perusahaan') }}</h2>
	      <ol class="breadcrumb">
	        <li>{{ trans('all.perusahaan') }}</li>
	        <li class="active"><strong>{{ trans('all.tambahdata') }}</strong></li>
	      </ol>
	    @else
	    	<h2>{{ trans('all.tambahperusahaan') }}</h2>
	    @endif
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content">
          	<form action="{{ url('/perusahaan') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table width="480px">
					<tr>
						<td width=100px>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="50">
						</td>
					</tr>
					<tr>
						<td colspan="2"><b>{{ trans('all.pic') }}</b></td>
					</tr>
					<tr>
						<td>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" autocomplete="off" name="pic_nama" id="pic_nama" maxlength="50">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.alamat') }}</td>
						<td>
							<input type="text" class="form-control" autocomplete="off" name="pic_alamat" id="pic_alamat" maxlength="50">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.nomorhp') }}</td>
						<td style="float:left">
							<input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="pic_notelp" id="pic_notelp" maxlength="50">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.email') }}</td>
						<td>
							<input type="text" class="form-control" onblur="validateEmail(this);" autocomplete="off" name="pic_email" id="pic_email" maxlength="50">
						</td>
					</tr>
					{{--<tr>--}}
						{{--<td></td>--}}
						{{--<td>--}}
							{{--<div class="g-recaptcha" data-sitekey="{{ env('RE_CAP_SITE') }}"></div>--}}
						{{--</td>--}}
					{{--</tr>--}}
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							@if(isset($dari))
								<input type="hidden" name="dari" value="index">
								<button type="button" id="kembali" onclick="return ke('{{ url('/') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
							@else
								<input type="hidden" name="dari" value="perusahaan">
								<button type="button" id="kembali" onclick="return ke('../perusahaan')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
							@endif
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