@extends('layouts/master')
@section('title', trans('all.profil'))
@section('content')
  
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
    $("#ubahprofil").click(function(){
      window.location.href="profil/ubah";
    });

    $("#gantikatasandi").click(function(){
      window.location.href="profil/gantikatasandi"
    });

    $("#file").change(function(){
        readURL(this);
    });
  });

  function upload(){
    $("#file").trigger("click");
  }

  function readURL(input) {
    
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function (e) {
          $('#imgInp_profil').attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]);
      $("#filefoto").css('display', '');
    }
  }

  function batalupload(){
    $('#file').val('');
    $('#imgInp_profil').attr('src', '{{ url("foto/user/".Session::get('iduser_perusahaan')) }}');
    $("#filefoto").css('display', 'none');
  }

  function hapusfotouser(){
    alertConfirm("{{ trans('all.apakahanadayakinakanmenghapusfoto') }} ?",
      function(){
        window.location.href="hapusfoto/user/{{ Session::get('iduser_perusahaan') }}";
      }
    );
  }
  
  function validasiGantiFoto(){
    $('#uploadfoto').attr( 'data-loading', '' );
    $('#uploadfoto').attr('disabled', 'disabled');
    $('#batalupload').attr('disabled', 'disabled');
  }
  
  function hoverFoto(param){
    if(param == 'ya'){
      $('#delfoto').css('display', '');
    }else{
      $('#delfoto').css('display', 'none');
    }
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.profil') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-md-4">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
            <h5>{{ trans('all.detailprofil') }}</h5>
          </div>
          <div>
            <div class="ibox-content no-padding border-left-right" style="background: #e5e6e7" @if(Session::get("fotouser_perusahaan") == "ada") onmouseover="hoverFoto('ya')" onmouseleave="hoverFoto('tidak')" @endif>
              <br>
              @if(Session::get("fotouser_perusahaan") == "ada") <center><span id="delfoto" style="position: absolute;margin-left:70px;display:none;" onclick="return hapusfotouser()" title="{{ trans('all.hapusfoto') }}"><i style="cursor: pointer" class="fa fa-close"></i></span></center> @endif
              <center><img title="{{ trans('all.klikuntukmenggantifoto') }}" onclick="return upload()" style="cursor:pointer;" id="imgInp_profil" alt="image" class="img-circle circle-border m-b-md" width="150px" height="150px" src="{{ url('foto/user/'.Session::get('iduser_perusahaan')) }}"></center>
              <form method="post" action="{{ url('profil/gantifoto') }}" enctype="multipart/form-data" onsubmit="return validasiGantiFoto()">
                <center id="filefoto" style="display: none;">
                  {{ csrf_field() }}
                  <input type="file" id="file" name="file" style="display: none;">
                  <button id="uploadfoto" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                  <span id="batalupload" onclick="return batalupload()" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.batal') }}</span>
                  <br><br>
                </center>
              </form>
            </div>
            <div class="ibox-content profile-content">
                
              <h4>
                <strong>{{ $profil->nama }}</strong>
              </h4>
              
              @if($profil->email != "")
                <p><i class="fa fa-envelope"></i> {{ $profil->email}}</p>
              @endif
              @if($profil->nomorhp != "")
                <p><i class="fa fa-phone"></i> {{ $profil->nomorhp}}</p>
              @endif
              <!-- <div class="row m-t-lg">
                  <div class="col-md-4">
                      <span class="bar">5,3,9,6,5,9,7,3,5,2</span>
                      <h5><strong>169</strong> Posts</h5>
                  </div>
                  <div class="col-md-4">
                      <span class="line">5,3,9,6,5,9,7,3,5,2</span>
                      <h5><strong>28</strong> Following</h5>
                  </div>
                  <div class="col-md-4">
                      <span class="bar">5,3,2,-1,-3,-2,2,3,5,2</span>
                      <h5><strong>240</strong> Followers</h5>
                  </div>
              </div> -->
              <div class="user-button">
                <div class="row">
                  <div class="col-md-12">
                    <button type="button" id="ubahprofil" class="btn btn-default"><i class="fa fa-pencil"></i> {{ trans('all.ubah') }}</button>&nbsp;&nbsp;
                    <button style="float:right" type="button" id="gantikatasandi" class="btn btn-primary"><i class="fa fa-key"></i> {{ trans('all.gantipassword') }}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
            <h5>{{ trans('all.riwayat') }}</h5>
          </div>
          <div class="ibox-content">
            <table width="100%" class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                  <td class="opsi5"><b>{{ trans('all.tanggal') }}</b></td>
                  <td class="opsi2"><b>{{ trans('all.method') }}</b></td>
                  <td class="posi5"><b>{{ trans('all.path') }}</b></td>
                  <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
  $(function() {
    $('.datatable').DataTable({
      processing: true,
      bStateSave: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: '{!! url("profil/index-data") !!}',
        type: "POST",
        data: { _token: '{!! csrf_token() !!}' }
      },
      language: lang_datatable,
      columns: [
        { data: 'tanggal', name: 'tanggal',
          render: function (data) {
            var ukDateTime = data.split(' ');
            var ukDate = ukDateTime[0].split('-');
            return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
          }
        },
        { data: 'method', name: 'method' },
        { data: 'path', name: 'path' },
        { data: 'keterangan', name: 'keterangan' }
      ],
      order: [[0, 'desc']]
    });
  });
</script>
@endpush