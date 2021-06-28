@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  
  <script>
  $(function(){
    $("#kembali").click(function(){
      window.location.href="../profil";
    });
  });

  function validasi() {
  
    $('#submit').attr('data-loading', '');
    $('#submit').attr('disabled', 'disabled');
    $('#kembali').attr('disabled', 'disabled');
    
    var nama = $('#nama').val();
  
    if(nama == ""){
      alertWarning("{{ trans('all.namakosong') }}",
      function() {
        aktifkanTombol();
        setFocus($('#nama'));
        $('#loading-saver').css('display', 'none');
      });
      return false;
    }
  }
  </script>
  <style>
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.ubahprofil') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            <form id="form" name="form" method="post" action="../profil" onsubmit="return validasi()">
              {{ csrf_field() }}
              <table>
                <tr>
                  <td width="100px">{{ trans('all.nama') }}</td>
                  <td><input type="text" name="nama" autofocus autocomplete="off" id="nama" value="{{ $profil->nama }}" class="form-control"></td>
                </tr>
                <tr>
                  <td>{{ trans('all.nomorhp') }}</td>
                  <td><input type="text" name="nomorhp" autocomplete="off" id="nomorhp" value="{{ $profil->nomorhp }}" class="form-control"></td>
                </tr>
                <tr>
                  <td colspan="2">
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" id="kembali" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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