@extends('layouts.master')
@section('title', trans('all.slideshow'))
@section('content')

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
                  toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    @endif

    var i= {{ $totaldata }};
    $("#tambah_gambar").click(function(){
        $('#kumpulangambar').append("<div class='col-xs-2' style='width:120px;margin:10px' id='addr_gambar"+i+"'><table id='tab_gambar' style='margin-bottom:0px !important'><tbody><tr><td><img id='displayfoto"+i+"' src='{{ url('foto/slideshow/0-0') }}' width='120px' height='120px'></td></tr><tr><td><table style='margin-top:10px'><tr><td><a onclick='ambilfoto("+i+")' class='btn btn-default'><i class='glyphicon glyphicon-folder-open'></i></a><input style='display:none' onchange='gantifoto(this,"+i+")' type='file' class='filestyle' id='gambar_"+i+"' data-badge='false' data-input='false' name='slideshow[]'></td><td style='padding-left:5px;'><a title='{{ trans('all.hapusfoto') }}' disabled class='btn btn-default' onclick='hapusfoto("+i+")' name='hapusfoto' id='hapusfoto"+i+"'><i class='glyphicon glyphicon-trash'></i></a></td><td style='padding-left:5px;'><a onclick='hapusgambar("+i+")'><i class='btn btn-danger glyphicon glyphicon-remove row-remove'></i></a></td></tr></table></td></tr></tbody></table></div>");

        $('#jumlahgambar').val(i);
        setTimeout(ambilfoto(i),200);
        i++;
    });

    $('#simpan').click(function(){

        $('#simpan').attr( 'data-loading', '' );
        setTimeout(function(){ $('#simpan').attr('disabled', 'disabled'); },100);

        return true;

    });
  });

  function ambilfoto(i){
      $('#gambar_'+i).click();
      return false;
  }

  function hapusgambar(i){
      $("#addr_gambar"+i).remove();
      i--;
      $('#jumlahgambar').val(i);
      return false;
  }

  function gantifoto(input,i) {

      if (input.files && input.files[0]) {
          var reader = new FileReader();

          reader.onload = function (e) {
              $('#displayfoto'+i).attr('src', e.target.result);
          }

          reader.readAsDataURL(input.files[0]);
          $('#hapusfoto'+i).removeAttr('disabled');
          $('#simpan').trigger('click');
      }
  }

  function hapusfoto(i) {
      $('#gambar_'+i).val('');
      $('#displayfoto'+i).attr('src', '{{ url('foto/slideshow/0-0') }}');
      $('#hapusfoto'+i).attr('disabled', 'disabled');
      return false;
  }

  function hapusgambardb(i,idslideshow,id,foto){
      alertConfirmNotClose("{{ trans('all.apakahanadayakinakanmenghapusfoto') }}",
          function() //jika confim
          {
              window.location.href='{{ url('hapusfoto/slideshow') }}/'+id+'-'+idslideshow+'-'+foto;
              return false;
          }
      );
  }
  </script>
  <style>
  .blueimp-gallery {
      background: rgba(0, 0, 0, 0.8);
  }

  .animated {
      -webkit-animation-fill-mode: none;
      animation-fill-mode: none;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.slideshow').' ('.$slideshow.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
          <li>{{ trans('all.slideshow') }}</li>
        <li class="active"><strong>{{ trans('all.detail') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          {{-- <button class="btn btn-primary" onclick="return ke('../../slideshow')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button><p></p> --}}
          <div class="ibox float-e-margins">
              <div class="ibox-content">
                  <form method="POST" id='myform' enctype="multipart/form-data" action="../{{ $idslideshow.'/detail' }}">
                      {{ csrf_field() }}
                      <input type="hidden" value="{{ $idslideshow }}" name="idslideshow">
                      <div class="row">
                          <div id='kumpulangambar' class="lightBoxGallery">
                              @if($totaldata != 0)
                                  <?php $i=0; ?>
                                  @foreach($data as $key)
                                      <div class='col-xs-2' style='width:120px;margin:10px' id='addr_gambar{{ $i }}'>
                                          <table id="tab_gambar" style="margin-bottom:0px !important">
                                              <tbody>
                                              <tr>
                                                  <td>
                                                      <a href="{{ url('foto/slideshow/'.$idslideshow.'-'.$key->filename) }}" title="{{ trans('all.slideshow').' ('.$slideshow.')' }}" data-gallery="">
                                                        <img id='displayfoto{{ $i }}' src='{{ url('foto/slideshow/'.$idslideshow.'-'.$key->filename) }}' width='120px' height='120px'><p></p>
                                                      </a>
                                                      <table style='display:none'>
                                                          <tr>
                                                              <td>
                                                                  <input onchange='gantifoto(this,{{ $i }})' type='file' class='filestyle' id='gambar_{{ $i }}' data-badge='false' data-input='false' name='slideshow[]'>
                                                              </td>
                                                              <td style='padding-left:5px;'>
                                                                  <a title='{{ trans('all.hapusfoto') }}' disabled class='btn btn-default' onclick='hapusfoto({{ $i }})' name='hapusfoto' id='hapusfoto{{ $i }}'><i class='glyphicon glyphicon-trash'></i></a>
                                                              </td>
                                                          </tr>
                                                      </table>
                                                  </td>
                                              </tr>
                                              @if(strpos(Session::get('hakakses_perusahaan')->slideshow, 'h') !== false)
                                                <tr>
                                                    <td><a onclick='hapusgambardb({{ $i }},{{ $idslideshow }},{{ $key->id }},"{{ $key->filename }}")'><i class='btn btn-danger glyphicon glyphicon-remove row-remove'></i></a></td>
                                                </tr>
                                              @endif
                                              </tbody>
                                          </table>
                                      </div>
                                      <?php $i++; ?>
                                  @endforeach
                              @endif
                          </div>
                          @if(strpos(Session::get('hakakses_perusahaan')->slideshow, 't') !== false)
                            <center class='col-xs-2' style='width:110px;margin:30px'>
                                <i id='tambah_gambar' class='fa fa-plus-circle' style='font-size: 72px;cursor: pointer;color:#1ab394'></i>
                            </center>
                          @endif
                      </div>
                      @if(strpos(Session::get('hakakses_perusahaan')->slideshow, 't') !== false)
                        <button id="simpan" type="submit" style='margin-top:5px;' class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-floppy-o'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                      @endif
                      <button type="button" style='margin-top:5px;' class="btn btn-primary" onclick="return ke('../../slideshow')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button><p></p>
                  </form>
              </div>
          </div>
      </div>
    </div>
  </div>
@stop