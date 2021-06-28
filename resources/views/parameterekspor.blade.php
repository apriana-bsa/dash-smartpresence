@extends('layouts/master')
@section('title', trans('all.parameterekspor'))
@section('content')
  
  <!-- Switchery -->
  <link href="{{ asset('lib/css/plugins/switchery/switchery.css') }}" rel="stylesheet">
  <script src="{{ asset('lib/js/plugins/switchery/switchery.js') }}"></script>
  <link href="{{ asset('lib/css/patternLock.css') }}"  rel="stylesheet" type="text/css" />
  <script src="{{ asset('lib/js/patternLock.min.js') }}"></script>
  <script>
  $(function(){

    @if(Session::get('message'))
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 4000,
                      extendedTimeOut: 4000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    @endif

    var elem = document.querySelector('.js-switch');
    new Switchery(elem, { color: '#1AB394' });

    var elem_2 = document.querySelector('.js-switch_2');
    new Switchery(elem_2, { color: '#1AB394' });

    var elem_3 = document.querySelector('.js-switch_3');
    new Switchery(elem_3, { color: '#1AB394' });

    var elem_4 = document.querySelector('.js-switch_4');
    new Switchery(elem_4, { color: '#1AB394' });

    $('.jam').inputmask( 'h:s' );
  });

  function ubahgunakanpwd(){
      var gunakanpwd = $('#gunakanpwd').val();
      if(gunakanpwd == 'y'){
          $('.flagpwd').css('display','');
      }else{
          $('.flagpwd').css('display','none');
      }
      return false;
  }

  function validasi(){
      var gunakanpwd = $('#gunakanpwd').val();
      var pwd = $('#pwd').val();
      if(gunakanpwd == 'y'){
          if (pwd == "") {
              alertWarning("{{ trans('all.katasandikosong') }}",
                      function () {
                          aktifkanTombol();
                          setTimeout(function(){ $('#pwd').focus(); },200);
                      });
              return false;
          }
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
        <h2>{{ trans('all.parameterekspor') }}</h2>
        <ol class="breadcrumb">
          <li>{{ trans('all.pengaturan') }}</li>
          <li class="active"><strong>{{ trans('all.parameterekspor') }}</strong></li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            <form action="{{ url('pengaturan/parameterekspor') }}" enctype="multipart/form-data" method="post" onsubmit="return validasi()">
                {{ csrf_field() }}
                <input type="hidden" value="others" name="dari">
                <table>
                    <tr>
                        <td>{{ trans('all.gunakanpassword') }}</td>
                        <td>
                            <select id="gunakanpwd" name="gunakanpwd" class="form-control" onchange="return ubahgunakanpwd()">
                                <option value="y" @if($data['gunakanpwd'] == 'y') selected @endif>{{ trans('all.ya') }}</option>
                                <option value="t" @if($data['gunakanpwd'] == 't') selected @endif>{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                        <td class="flagpwd" @if($data['gunakanpwd'] == 't') style="display:none" @endif>{{ trans('all.katasandi') }}</td>
                        <td class="flagpwd" @if($data['gunakanpwd'] == 't') style="display:none" @endif>
                            <input type="text" name="pwd" value="{{ $data['pwd'] }}" id="pwd" class="form-control">
                        </td>
                        <td>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>
                        </td>
                    </tr>
                </table>
                <br>
                <table border="1" style="border-color:#c1c1c1;" width="100%">
                  <tr>
                    <td width="150px">
                        <center>
                            @if($data['logokiri'] == '')
                                <div style="width:120px;height:120px;border-radius:50%;background: #CCC;line-height: 115px;">{{ trans('all.logo') }}</div>
                            @else
                                <img src="{{ url('foto/logoparameterekspor/'.$data['logokiri']) }}" width="120px" height="120px" style="border-radius:50%">
                            @endif
                        </center>
                        <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right"  data-toggle="modal" data-target="#modallogo"></i>
                    </td>
                    <td>
                        @if($data['header_1_teks'] != '' or $data['header_2_teks'] != '' or $data['header_3_teks'] != '' or $data['header_4_teks'] != '' or $data['header_5_teks'] != '')
                            @for($i=1;$i<=5;$i++)
                                <center>
                                    @if($data['header_'.$i.'_fontstyle'] == 'normal') {{ $data['header_'.$i.'_teks'] }} @endif
                                    @if($data['header_'.$i.'_fontstyle'] == 'bold') <b>{{ $data['header_'.$i.'_teks'] }}</b> @endif
                                    @if($data['header_'.$i.'_fontstyle'] == 'italic') <i>{{ $data['header_'.$i.'_teks'] }}</i> @endif
                                    @if($data['header_'.$i.'_fontstyle'] == 'underline') <span style="text-decoration: underline">{{ $data['header_'.$i.'_teks'] }}</span> @endif
                                </center>
                            @endfor
                        @else
                            <center style="height:120px;line-height: 145px;">{{ trans('all.header5baris') }}</center>
                        @endif
                        <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right" data-toggle="modal" data-target="#modalheader5baris"></i>
                    </td>
                    <td width="150px">
                      <center>
                          @if($data['logokanan'] == '')
                              <div style="width:120px;height:120px;border-radius:50%;background: #CCC;line-height: 115px;">{{ trans('all.logo') }}</div>
                          @else
                              <img src="{{ url('foto/logoparameterekspor/'.$data['logokanan']) }}" width="120px" height="120px" style="border-radius:50%">
                          @endif
                      </center>
                      <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right" data-toggle="modal" data-target="#modallogo"></i>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <center>
                        <div style="height:320px">
                            <hr width="70%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="80%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="70%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="85%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="80%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="60%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="50%" style="padding:10px;border-top:1px solid #c1c1c1">
                            <hr width="70%" style="padding:10px;border-top:1px solid #c1c1c1">
                        </div>
                      </center>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:0px" colspan="3">
                      <table border="1" style="border-color:#c1c1c1;border-top:0;border-bottom:0;border-right:0;border-left:0;" width="100%">
                        <tr>
                          <td style="padding:0px;border-right:1px solid #c1c1c1;" width="350px">
                              @if($data['footerkiri_1_teks'] != '' or $data['footerkiri_2_teks'] != '' or $data['footerkiri_3_teks'] != '' or $data['footerkiri_5_teks'] != '' or $data['footerkiri_6_teks'] != '')
                                  @for($i=1;$i<=6;$i++)
                                      @if($i == 4)
                                          @for($z=1;$z<$data['footerkiri_4_separator'];$z++)
                                              <br>
                                          @endfor
                                      @else
                                          <center>
                                              @if($data['footerkiri_'.$i.'_fontstyle'] == 'normal') {{ $data['footerkiri_'.$i.'_teks'] }} @endif
                                              @if($data['footerkiri_'.$i.'_fontstyle'] == 'bold') <b>{{ $data['footerkiri_'.$i.'_teks'] }}</b> @endif
                                              @if($data['footerkiri_'.$i.'_fontstyle'] == 'italic') <i>{{ $data['footerkiri_'.$i.'_teks'] }}</i> @endif
                                              @if($data['footerkiri_'.$i.'_fontstyle'] == 'underline') <span style="text-decoration: underline">{{ $data['footerkiri_'.$i.'_teks'] }}</span> @endif
                                          </center>
                                      @endif
                                  @endfor
                              @else
                                  <center><div style="width:120px;height:120px;line-height: 145px;">{{ trans('all.footer') }} 1</div></center>
                              @endif
                              <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right" data-toggle="modal" data-target="#modalfooter"></i>
                          </td>
                          <td style="padding:0px;border-right:1px;border-color:#c1c1c1;">
                              @if($data['footertengah_1_teks'] != '' or $data['footertengah_2_teks'] != '' or $data['footertengah_3_teks'] != '' or $data['footertengah_5_teks'] != '' or $data['footertengah_6_teks'] != '')
                                  @for($i=1;$i<=6;$i++)
                                      @if($i == 4)
                                          @for($z=1;$z<$data['footertengah_4_separator'];$z++)
                                              <br>
                                          @endfor
                                      @else
                                          <center>
                                              @if($data['footertengah_'.$i.'_fontstyle'] == 'normal') {{ $data['footertengah_'.$i.'_teks'] }} @endif
                                              @if($data['footertengah_'.$i.'_fontstyle'] == 'bold') <b>{{ $data['footertengah_'.$i.'_teks'] }}</b> @endif
                                              @if($data['footertengah_'.$i.'_fontstyle'] == 'italic') <i>{{ $data['footertengah_'.$i.'_teks'] }}</i> @endif
                                              @if($data['footertengah_'.$i.'_fontstyle'] == 'underline') <span style="text-decoration: underline">{{ $data['footertengah_'.$i.'_teks'] }}</span> @endif
                                          </center>
                                      @endif
                                  @endfor
                              @else
                                  <center><div style="width:120px;height:120px;line-height: 145px;">{{ trans('all.footer') }} 2</div></center>
                              @endif
                              <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right" data-toggle="modal" data-target="#modalfooter"></i>
                          </td>
                          <td style="padding:0px;border-left:1px solid #c1c1c1;" width="350px">
                              @if($data['footerkanan_1_teks'] != '' or $data['footerkanan_2_teks'] != '' or $data['footerkanan_3_teks'] != '' or $data['footerkanan_5_teks'] != '' or $data['footerkanan_6_teks'] != '')
                                  @for($i=1;$i<=6;$i++)
                                      @if($i == 4)
                                          @for($z=1;$z<$data['footerkanan_4_separator'];$z++)
                                              <br>
                                          @endfor
                                      @else
                                          <center>
                                              @if($data['footerkanan_'.$i.'_fontstyle'] == 'normal') {{ $data['footerkanan_'.$i.'_teks'] }} @endif
                                              @if($data['footerkanan_'.$i.'_fontstyle'] == 'bold') <b>{{ $data['footerkanan_'.$i.'_teks'] }}</b> @endif
                                              @if($data['footerkanan_'.$i.'_fontstyle'] == 'italic') <i>{{ $data['footerkanan_'.$i.'_teks'] }}</i> @endif
                                              @if($data['footerkanan_'.$i.'_fontstyle'] == 'underline') <span style="text-decoration: underline">{{ $data['footerkanan_'.$i.'_teks'] }}</span> @endif
                                          </center>
                                      @endif
                                  @endfor
                              @else
                                  <center><div style="width:120px;height:120px;line-height: 145px;">{{ trans('all.footer') }} 3</div></center>
                              @endif
                              <i style="cursor:pointer" class="fa fa-pencil fa-2x pull-right" data-toggle="modal" data-target="#modalfooter"></i>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal header 5 baris-->
  <div class="modal fade" id="modalheader5baris" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.header5baris') }}</h4>
              </div>
              <form action="{{ url('pengaturan/parameterekspor') }}" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" value="header5baris" name="dari">
                  <div class="modal-body" style="max-height:460px;overflow: auto;">
                      <table width="100%">
                          @for($i=1;$i<=5;$i++)
                              <tr>
                                  <td>
                                      <input type="text" class="form-control" value="{{ $data['header_'.$i.'_teks'] }}" placeholder="{{ trans('all.masukkanbaris') }} {{ $i }}" id="header_{{ $i }}_teks" name="header_{{ $i }}_teks" maxlength="255">
                                  </td>
                                  <td width="120px">
                                      <select class="form-control" id="header_{{ $i }}_fontstyle" name="header_{{ $i }}_fontstyle">
                                          <option value="normal" @if($data['header_'.$i.'_fontstyle'] == 'normal') selected @endif>{{ trans('all.normal') }}</option>
                                          <option value="bold" @if($data['header_'.$i.'_fontstyle'] == 'bold') selected @endif>{{ trans('all.bold') }}</option>
                                          <option value="italic" @if($data['header_'.$i.'_fontstyle'] == 'italic') selected @endif>{{ trans('all.italic') }}</option>
                                          <option value="underline" @if($data['header_'.$i.'_fontstyle'] == 'underline') selected @endif>{{ trans('all.underline') }}</option>
                                      </select>
                                  </td>
                              </tr>
                          @endfor
                      </table>
                  </div>
                  <div class="modal-footer">
                      <table>
                          <tr>
                              <td style="padding:0px;">
                                  <button class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                                  <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                              </td>
                          </tr>
                      </table>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- Modal hedaer 5 baris-->

  <!-- Modal footer-->
  <div class="modal fade" id="modalfooter" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.header5baris') }}</h4>
              </div>
              <form action="{{ url('pengaturan/parameterekspor') }}" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" value="footer" name="dari">
                  <div class="modal-body" style="max-height:460px;overflow: auto;">
                      <ul class="nav nav-tabs">
                          <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.footer') }} 1</a></li>
                          <li class=""><a data-toggle="tab" href="#tab-2">{{ trans('all.footer') }} 2</a></li>
                          <li class=""><a data-toggle="tab" href="#tab-3">{{ trans('all.footer') }} 3</a></li>
                      </ul>
                      <div class="tab-content">
                          <div id="tab-1" class="tab-pane active">
                              <div class="panel-body">
                                  <table width="100%">
                                      @for($i=1;$i<=6;$i++)
                                          @if($i == 4)
                                              <tr>
                                                  <td>{{ trans('all.separator') }}</td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footerkiri_{{ $i }}_separator" name="footerkiri_{{ $i }}_separator">
                                                          @for($s=1;$s<=10;$s++)
                                                            <option value="{{ $s }}" @if($data['footerkiri_4_separator'] == $s) selected @endif>{{ $s }}</option>
                                                          @endfor
                                                      </select>
                                                  </td>
                                              </tr>
                                          @else
                                              <tr>
                                                  <td>
                                                      <input type="text" class="form-control" value="{{ $data['footerkiri_'.$i.'_teks'] }}" placeholder="{{ trans('all.masukkanbaris') }} {{ $i }}" id="footerkiri_{{ $i }}_teks" name="footerkiri_{{ $i }}_teks" maxlength="255">
                                                  </td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footerkiri_{{ $i }}_fontstyle" name="footerkiri_{{ $i }}_fontstyle">
                                                          <option value="normal" @if($data['footerkiri_'.$i.'_fontstyle'] == 'normal') selected @endif>{{ trans('all.normal') }}</option>
                                                          <option value="bold" @if($data['footerkiri_'.$i.'_fontstyle'] == 'bold') selected @endif>{{ trans('all.bold') }}</option>
                                                          <option value="italic" @if($data['footerkiri_'.$i.'_fontstyle'] == 'italic') selected @endif>{{ trans('all.italic') }}</option>
                                                          <option value="underline" @if($data['footerkiri_'.$i.'_fontstyle'] == 'underline') selected @endif>{{ trans('all.underline') }}</option>
                                                      </select>
                                                  </td>
                                              </tr>
                                          @endif
                                      @endfor
                                  </table>
                              </div>
                          </div>
                          <div id="tab-2" class="tab-pane">
                              <div class="panel-body">
                                  <table width="100%">
                                      @for($i=1;$i<=6;$i++)
                                          @if($i == 4)
                                              <tr>
                                                  <td>{{ trans('all.separator') }}</td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footertengah_{{ $i }}_separator" name="footertengah_{{ $i }}_separator">
                                                          @for($s=1;$s<=10;$s++)
                                                              <option value="{{ $s }}" @if($data['footertengah_4_separator'] == $s) selected @endif>{{ $s }}</option>
                                                          @endfor
                                                      </select>
                                                  </td>
                                              </tr>
                                          @else
                                              <tr>
                                                  <td>
                                                      <input type="text" class="form-control" value="{{ $data['footertengah_'.$i.'_teks'] }}" placeholder="{{ trans('all.masukkanbaris') }} {{ $i }}" id="footertengah_{{ $i }}_teks" name="footertengah_{{ $i }}_teks" maxlength="255">
                                                  </td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footertengah_{{ $i }}_fontstyle" name="footertengah_{{ $i }}_fontstyle">
                                                          <option value="normal" @if($data['footertengah_'.$i.'_fontstyle'] == 'normal') selected @endif>{{ trans('all.normal') }}</option>
                                                          <option value="bold" @if($data['footertengah_'.$i.'_fontstyle'] == 'bold') selected @endif>{{ trans('all.bold') }}</option>
                                                          <option value="italic" @if($data['footertengah_'.$i.'_fontstyle'] == 'italic') selected @endif>{{ trans('all.italic') }}</option>
                                                          <option value="underline" @if($data['footertengah_'.$i.'_fontstyle'] == 'underline') selected @endif>{{ trans('all.underline') }}</option>
                                                      </select>
                                                  </td>
                                              </tr>
                                          @endif
                                      @endfor
                                  </table>
                              </div>
                          </div>
                          <div id="tab-3" class="tab-pane">
                              <div class="panel-body">
                                  <table width="100%">
                                      @for($i=1;$i<=6;$i++)
                                          @if($i == 4)
                                              <tr>
                                                  <td>{{ trans('all.separator') }}</td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footerkanan_{{ $i }}_separator" name="footerkanan_{{ $i }}_separator">
                                                          @for($s=1;$s<=10;$s++)
                                                              <option value="{{ $s }}" @if($data['footerkanan_4_separator'] == $s) selected @endif>{{ $s }}</option>
                                                          @endfor
                                                      </select>
                                                  </td>
                                              </tr>
                                          @else
                                              <tr>
                                                  <td>
                                                      <input type="text" class="form-control" value="{{ $data['footerkanan_'.$i.'_teks'] }}" placeholder="{{ trans('all.masukkanbaris') }} {{ $i }}" id="footerkanan_{{ $i }}_teks" name="footerkanan_{{ $i }}_teks" maxlength="255">
                                                  </td>
                                                  <td width="120px">
                                                      <select class="form-control" id="footerkanan_{{ $i }}_fontstyle" name="footerkanan_{{ $i }}_fontstyle">
                                                          <option value="normal" @if($data['footerkanan_'.$i.'_fontstyle'] == 'normal') selected @endif>{{ trans('all.normal') }}</option>
                                                          <option value="bold" @if($data['footerkanan_'.$i.'_fontstyle'] == 'bold') selected @endif>{{ trans('all.bold') }}</option>
                                                          <option value="italic" @if($data['footerkanan_'.$i.'_fontstyle'] == 'italic') selected @endif>{{ trans('all.italic') }}</option>
                                                          <option value="underline" @if($data['footerkanan_'.$i.'_fontstyle'] == 'underline') selected @endif>{{ trans('all.underline') }}</option>
                                                      </select>
                                                  </td>
                                              </tr>
                                          @endif
                                      @endfor
                                  </table>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <table>
                          <tr>
                              <td style="padding:0px;">
                                  <button class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                                  <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                              </td>
                          </tr>
                      </table>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- Modal footer-->

  <!-- Modal logo kiri kanan-->
  <div class="modal fade" id="modallogo" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.logo') }}</h4>
              </div>
              <form action="{{ url('pengaturan/parameterekspor') }}" enctype="multipart/form-data" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" value="logo" name="dari">
                  <div class="modal-body">
                      <div class="row">
                          <div class="col-md-6">
                              <table width="100%">
                                  <tr>
                                      <td colspan="2">{{ trans('all.kiri') }}</td>
                                  </tr>
                                  <tr>
                                      <td>
                                          @if($data['logokiri'] == '')
                                            <img id="fotologokiri" src="{{ url('foto/logoparameterekspor/0') }}" width="120px" height="120px" style="border-radius:50%">
                                          @else
                                            <img id="fotologokiri" src="{{ url('foto/logoparameterekspor/'.$data['logokiri']) }}" width="120px" height="120px" style="border-radius:50%">
                                          @endif
                                      </td>
                                      <td>
                                          <table>
                                              <tr>
                                                  <td><input type='file' name='logokiri' id='logokiri' class="filestyle"  data-badge="false" data-input="false"></td>
                                                  <td style='padding-left:5px;'><i @if($data['logokiri'] == '') disabled @endif class='glyphicon glyphicon-trash btn btn-default' filelogokiri="{{ $data['logokiri'] }}" title='{{ trans("all.hapusfoto")}}' name='hapusfotologokiri' id='hapusfotologokiri'></i></td>
                                              </tr>
                                          </table>
                                      </td>
                                  </tr>
                              </table>
                          </div>
                          <div class="col-md-6">
                              <table width="100%">
                                  <tr>
                                      <td colspan="2">{{ trans('all.kanan') }}</td>
                                  </tr>
                                  <tr>
                                      <td>
                                          @if($data['logokanan'] == '')
                                              <img id="fotologokanan" src="{{ url('foto/logoparameterekspor/0') }}" width="120px" height="120px" style="border-radius:50%">
                                          @else
                                              <img id="fotologokanan" src="{{ url('foto/logoparameterekspor/'.$data['logokanan']) }}" width="120px" height="120px" style="border-radius:50%">
                                          @endif
                                      </td>
                                      <td>
                                          <table>
                                              <tr>
                                                  <td><input type='file' name='logokanan' id='logokanan' class="filestyle"  data-badge="false" data-input="false"></td>
                                                  <td style='padding-left:5px;'><i @if($data['logokanan'] == '') disabled @endif class='glyphicon glyphicon-trash btn btn-default' filelogokanan="{{ $data['logokanan'] }}" title='{{ trans("all.hapusfoto")}}' name='hapusfotologokanan' id='hapusfotologokanan'></i></td>
                                              </tr>
                                          </table>
                                      </td>
                                  </tr>
                              </table>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <table>
                          <tr>
                              <td style="padding:0px;">
                                  <button class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                                  <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                              </td>
                          </tr>
                      </table>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- Modal logo kiri kanan-->

    <script>
    function readURL(input,param) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#fotologo'+param).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
            $('#hapusfotologo'+param).removeAttr('disabled');
        }
    }

    $(function(){
        $("#logokiri").change(function(){
            readURL(this,'kiri');
        });

        $('#hapusfotologokiri').click(function(){

            var filelogokiri = $(this).attr('filelogokiri');
            if(filelogokiri == ''){
                $('#logokiri').val('');
                $('#fotologokiri').attr('src', '{{ url("foto/logoparameterekspor/0") }}');
                $(this).attr('disabled', 'disabled');
            }else{
                window.location.href='../hapusfoto/logoparameterekspor/kiri-'+filelogokiri;
            }
            return false;
        });

        $("#logokanan").change(function(){
            readURL(this,'kanan');
        });

        $('#hapusfotologokanan').click(function(){

            var filelogokanan = $(this).attr('filelogokanan');
            if(filelogokanan == ''){
                $('#logokanan').val('');
                $('#fotologokanan').attr('src', '{{ url("foto/logoparameterekspor/0") }}');
                $(this).attr('disabled', 'disabled');
            }else{
                window.location.href='../hapusfoto/logoparameterekspor/kanan-'+filelogokanan;
            }
            return false;
        });
    });
    </script>

@stop