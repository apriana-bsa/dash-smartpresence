@extends('layouts/master')
@section('title', trans('all.bugsreport'))
@section('content')
  
  <script>
  $(function(){
    $('#submit').click(function(){
      
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      $('#clear').attr('disabled', 'disabled');
      
      var namabug = $('#namabug').val();
      var detailbug = $('#detailbug').val();
      var tingkatbug = $('#tingkatbug').val();
      var namapelapor = $('#namapelapor').val();
      var emailpelapor = $('#emailpelapor').val();
      var nohppelapor = $('#nohppelapor').val();
      
      if(namabug == ""){
        alertWarning("{{ trans('all.namabugkosong') }}",function(){aktifkanTombol();setFocus($('#namabug'));});
        return false;
      }
      
      if(detailbug == ""){
        alertWarning("{{ trans('all.detailbugkosong') }}",function(){aktifkanTombol();setFocus($('#detailbug'));});
        return false;
      }
      
      if(tingkatbug == ""){
        alertWarning("{{ trans('all.tingkatbugkosong') }}",function(){aktifkanTombol();setFocus($('#tingkatbug'));});
        return false;
      }
      
      if(namapelapor == ""){
        alertWarning("{{ trans('all.namakosong') }}",function(){aktifkanTombol();setFocus($('#namapelapor'));});
        return false;
      }
      
      if(emailpelapor == ""){
        if(nohppelapor == ""){
          alertWarning("{{ trans('all.nomorhpkosong') }}",function(){aktifkanTombol();setFocus($('#nohppelapor'));});
          return false;
        }
      }
      
      if(nohppelapor == ""){
        if(emailpelapor == ""){
          alertWarning("{{ trans('all.emailkosong') }}",function(){aktifkanTombol();setFocus($('#emailpelapor'));});
          return false;
        }
      }
      
      $('#loading').css('display', '');
      
      var dataString = new FormData($('#myform')[0]);
      // setTimeout(function(){
        $.ajax({
          type: "POST",
          url: "{{ url('bugtracker.php') }}",
          data: dataString,
          async: true,
          cache: false,
          contentType: false,
          processData: false,
          success: function(html){
              console.log(html);
              if(html == '1'){
                alertSuccess('{{ trans("all.bugsreportterkirim") }}', function() {
                    $('#submit').closest('form').find("input[type=text], textarea, input[type=file], select").val("");
                    $('#submit').removeAttr('data-loading');
                    $('#loading').css('display', 'none');
                    $('#submit').removeAttr('disabled');
                    $('#clear').removeAttr('disabled');
                  });
              }else{
                alertError('{{ trans("all.cekkoneksiinternetanda") }}', function() {
                    $('#submit').closest('form').find("input[type=text], textarea, input[type=file], select").val("");
                    $('#submit').removeAttr('data-loading');
                    $('#loading').css('display', 'none');
                    $('#submit').removeAttr('disabled');
                    $('#clear').removeAttr('disabled');
                  });
              }
              return false;
          }
        });
      //},500);
      return false;
    });
    
    $('#clear').click(function(){
      $('#namabug').focus();
    });
  });
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.bugsreport') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            <form method="POST" id='myform' enctype="multipart/form-data">
              <input name='aplikasi' type='hidden' class='form-control' id='aplikasi' value='Dashboard Absensi'>
              <input name='versi' type='hidden' class='form-control' id='versi' value='1.0'>
              <input name='namaclient' type='hidden' class='form-control' id='namaclient' value='Absensi'>
              <table cellpadding="0" width='100%' cellspacing="0" border="0">
                <tr>
                  <td>
                    <table>
                      <tr>
                        <td style='width:170px;padding:5px;'>{{ trans('all.namabug') }}</td>
                        <td style='padding:5px;'><input autofocus autocomplete='off' autofocus type='text' name='namabug' class='form-control' id='namabug'></td>
                      </tr>
                      <tr>
                        <td style='padding:5px;'>{{ trans('all.detailbug') }}</td>
                        <td style='padding:5px;'><textarea cols='100%' rows='8' style='resize:none;' class='form-control' name='detailbug' id='detailbug'></textarea></td>
                      </tr>
                      <tr>
                        <td style='padding:5px;'>{{ trans('all.tingkatbug') }}</td>
                        <td style='padding:5px;float:left'>
                          <select id='tingkatbug' name='tingkatbug' class='form-control'>
                            <option value=''></option>
                            <option value='Biasa'>{{ trans('all.biasa') }}</option>
                            <option value='Sedang'>{{ trans('all.sedang') }}</option>
                            <option value='Penting'>{{ trans('all.penting') }}</option>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td style='padding:5px;'>{{ trans('all.lampirkangambar') }}</td>
                        <td style='padding:5px;'>
                          <input type='file' name='foto' id='foto'>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>
                    <table>
                      <tr>
                        <td style='width:170px;padding:5px;'>{{ trans('all.nama') }}</td>
                        <td style='padding:5px;'><input type='text' autocomplete='off' name='namapelapor' size=43 class='form-control' id='namapelapor'></td>
                      </tr>
                      <tr>
                        <td style='padding:5px;'>{{ trans('all.email') }}</td>
                        <td style='padding:5px;'><input type='text' autocomplete='off' name='emailpelapor' class='form-control' id='emailpelapor'></td>
                      </tr>
                      <tr>
                        <td style='padding:5px;'>{{ trans('all.nomorhp') }}</td>
                        <td style='padding:5px;float:left'><input type='text' autocomplete='off' name='nohppelapor' class='form-control' id='nohppelapor'></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button id="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-paper-plane-o'></i>&nbsp;&nbsp;{{ trans('all.kirim') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type='reset' id='clear' class='btn btn-primary'><i class='fa fa-eraser'></i>&nbsp;&nbsp;{{ trans('all.bersihkan') }}</button>
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