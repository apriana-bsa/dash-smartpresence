@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
    <script src="{{ asset('lib/js/i18next.js') }}"></script>
    <script src="{{ asset('lib/js/pwstrength.js') }}"></script>
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
  $(function(){
    $("#kembali").click(function(){
      window.location.href="../profil";
    });
    
    "use strict";
    i18next.init({
      lng: 'id',
      resources: {
          id: {
              translation: {
                  "veryWeak": "{{ trans('all.sangatlemah') }}",
                  "weak": "{{ trans('all.lemah') }}",
                  "normal": "{{ trans('all.normal') }}",
                  "medium": "{{ trans('all.sedang') }}",
                  "strong": "{{ trans('all.kuat') }}",
                  "veryStrong": "{{ trans('all.sangatkuat') }}"
              }
          }
      }
    }, function () {
      // Initialized and ready to go
      var options = {};
      options.ui = {
          container: "#form",
          showVerdictsInsideProgressBar: true,
          viewports: {
              progress: ".pwstrength_viewport_progress"
          },
          progressBarExtraCssClasses: "progress-bar-striped active"
      };
      options.common = {
          debug: false
      };
      $('#katasandibaru').pwstrength(options);
    });

      $("#pass1").hide();
      $("#pass2").hide();
      $("#pass3").hide();

      $("#katasandilama").on("keyup",function(){
          if($(this).val())
              $("#pass1").show();
          else
              $("#pass1").hide();
      });

      $("#katasandibaru").on("keyup",function(){
          if($(this).val())
              $("#pass2").show();
          else
              $("#pass2").hide();
      });

      $("#ulangikatasandibaru").on("keyup",function(){
          if($(this).val())
              $("#pass3").show();
          else
              $("#pass3").hide();
      });

      $("#pass1").mousedown(function(){
          $("#katasandilama").attr('type','text');
      }).mouseup(function(){
          $("#katasandilama").attr('type','password');
      }).mouseout(function(){
          $("#katasandilama").attr('type','password');
      });

      $("#pass2").mousedown(function(){
          $("#katasandibaru").attr('type','text');
      }).mouseup(function(){
          $("#katasandibaru").attr('type','password');
      }).mouseout(function(){
          $("#katasandibaru").attr('type','password');
      });

      $("#pass3").mousedown(function(){
          $("#ulangikatasandibaru").attr('type','text');
      }).mouseup(function(){
          $("#ulangikatasandibaru").attr('type','password');
      }).mouseout(function(){
          $("#ulangikatasandibaru").attr('type','password');
      });
  });

  function validasi(){
    
    $('#submit').attr('data-loading', '');
    $('#submit').attr('disabled', 'disabled');
    $('#kembali').attr('disabled', 'disabled');

    var katasandilama = $("#katasandilama").val();
    var katasandibaru = $("#katasandibaru").val();
    var ulangikatasandibaru = $("#ulangikatasandibaru").val();

    if(katasandilama == ""){
      alertWarning("{{ trans('all.katasandilamakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#katasandilama'));
            });
      return false;
    }

    if(katasandibaru == ""){
      alertWarning("{{ trans('all.katasandibarukosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#katasandibaru'));
            });
      return false;
    }

    if(ulangikatasandibaru == ""){
      alertWarning("{{ trans('all.ulangikatasandibarukosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#ulangikatasandibaru'));
            });
      return false;
    }

    if(katasandibaru != ulangikatasandibaru){
      alertWarning("{{ trans('all.katasandibarudanulangikatasandibarutidaksama') }}",
            function() {
              aktifkanTombol();
              setFocus($('#ulangikatasandibaru'));
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
        <h2>{{ trans('all.gantipassword') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            <form id="form" name="form" method="post" action="../profil/gantikatasandi" onsubmit="return validasi()">
              {{ csrf_field() }}
              <table>
                <tr>
                  <td width="150px">{{ trans('all.katasandilama') }}</td>
                  <td><input type="password" name="katasandilama" id="katasandilama" autofocus autocomplete="off" class="form-control"></td>
                  <td><i id="pass1" class="fa fa-eye" style="cursor: pointer;"></i></td>
                </tr>
                <tr>
                  <td>{{ trans('all.katasandibaru') }}</td>
                  <td><input type="password" name="katasandibaru" id="katasandibaru" autocomplete="off" class="form-control"></td>
                  <td><i id="pass2" class="fa fa-eye" style="cursor: pointer;"></i></td>
                </tr>
                <tr>
                  <td></td>
                  <td><div class="pwstrength_viewport_progress"></div></td>
                </tr>
                <tr>
                  <td>{{ trans('all.ulangikatasandibaru') }}</td>
                  <td><input type="password" name="ulangikatasandibaru" id="ulangikatasandibaru" autocomplete="off" class="form-control"></td>
                  <td><i id="pass3" class="fa fa-eye" style="cursor: pointer;"></i></td>
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