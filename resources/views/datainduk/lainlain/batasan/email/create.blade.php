@extends('layouts.master')
@section('title', trans('all.batasan'))
@section('content')

	<style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  td{
      padding:5px;
  }
  span{
      cursor: pointer;
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

      $('#email').typeahead({
        name: 'email',
        remote : '../../../typeaheaduseremail/%QUERY',
        limit: 5
      });
    });

    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var email = $("#email").val();
        var batasan = $('#batasan').val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(email == ""){
			alertWarning("{{ trans('all.emailkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#email'));
            });
            return false;
		}

        if(!isEmail(email)){
            alertWarning("{{ trans('all.emailtidakvalid') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#email'));
                    });
            return false;
        }

        if(batasan == ""){
          alertWarning("{{ trans('all.batasankosong') }}",
                  function() {
                    aktifkanTombol();
                    setFocus($('#batasan'));
                  });
          return false;
        }
	}
    </script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.email') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li>{{ trans('all.batasan') }}</li>
        <li>{{ trans('all.email') }}</li>
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
          	<form action="{{ url('datainduk/lainlain/batasanemail') }}" method="post" onsubmit="return validasi()">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <table width="480px">
                <tr>
                    <td width="70px">{{ trans('all.email') }}</td>
                    <td>
                        <input type="text" class="form-control" autofocus autocomplete="off" name="email" id="email" maxlength="255">
                    </td>
                </tr>
                <tr>
                  <td valign="top" style="padding-top: 7px">{{ trans('all.batasan') }}</td>
                  <td style="float: left;">
                    <select class="form-control" id="batasan" name="batasan">
                      <option value=""></option>
                      @foreach($batasan as $key)
                        <option value="{{ $key->id }}">{{ $key->namabatasan }}</option>
                      @endforeach
                    </select>
                  </td>
                </tr>
                <tr>
                    <td colspan=2>
                      <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                      <button type="button" id="kembali" onclick="return ke('../batasanemail')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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