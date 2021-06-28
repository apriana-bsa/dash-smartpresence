@extends('layouts/master')
@section('title', trans('all.setulangkatasandipegawai'))
@section('content')

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
  });

  function checkboxSemuaPegawai(){
    if($("#semuapegawai").prop('checked')){
      $("#semuapegawai").prop('checked', true);
      $('._atribut').css('display','none');
      $('._atribut').css('display','none');
    }else{
      $("#semuapegawai").prop('checked', false);
      $('._atribut').css('display', '');
      $('._atribut').css('display', '');
    }
  }

  function spanSemuaPegawai(){
    if($("#semuapegawai").prop('checked')){
      $("#semuapegawai").prop('checked', false);
      $('._atribut').css('display','');
      $('._atribut').css('display','');
    }else{
      $("#semuapegawai").prop('checked', true);
      $('._atribut').css('display', 'none');
      $('._atribut').css('display', 'none');
    }
  }

  function checkChecked(formname) {
    var anyBoxesChecked = false;
    $('#' + formname + ' input[type="checkbox"]').each(function() {
      if ($(this).is(":checked")) {
          anyBoxesChecked = true;
      }
    });
 
    if (anyBoxesChecked == false) {
      $('#loading-saver').css('display', 'none');
      alertWarning("{{ trans('all.andabelummemilih') }}",
                  function(){
                    aktifkanTombol();
                  });
      return false;
    } 
  }

  function validasi(){
    $('#submit').attr( 'data-loading', '' );
    $('#submit').attr('disabled', 'disabled');
    $('#loading-saver').css('display', '');
    
    return checkChecked("myform");
  }
  </script>
  <style>
  span{
    cursor: pointer;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.setulangkatasandipegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li class="active"><strong>{{ trans('all.setulangkatasandipegawai') }}</strong></li>
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
            <form method="post" id='myform' action="{{ url('datainduk/lainlain/setulangkatasandipegawai') }}" onsubmit="return validasi()">
              {{ csrf_field() }}
              <table>
                <tr>
                  <td><input type="checkbox" id="hanyablmadapwd" name="hanyablmadapwd">&nbsp;&nbsp;<span onclick="spanClick('hanyablmadapwd')">{{ trans('all.hanyayangbelummemilikikatasandi') }}</span></td>
                </tr>
                <tr>
                  <td><input type="checkbox" id="semuapegawai" name="semuapegawai" onclick="return checkboxSemuaPegawai()">&nbsp;&nbsp;<span onclick="return spanSemuaPegawai()">{{ trans('all.semuapegawai') }}</span></td>
                </tr>
              </table>
              <hr>
              <div class="row">
                <div class="col-lg-12">
                  @foreach($atributs as $atribut)
                    @if(count($atribut->atributnilai) > 0)
                      <div class="col-md-4 _atribut" style="padding-left:0">
                        <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" name="atribut[]" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')"><b>{{ $atribut->atribut }}</b></span>
                        <br>
                        @foreach($atribut->atributnilai as $atributnilai)
                          @if(Session::has('jadwalshift_atribut'))
                            {{ $checked = false }}
                            @for($i=0;$i<count(Session::get('jadwalshift_atribut'));$i++)
                              @if($atributnilai->id == Session::get('jadwalshift_atribut')[$i])
                                <span style="display:none">{{ $checked = true }}</span>
                              @endif
                            @endfor
                            <div style="padding-left:15px">
                              <table>
                                <tr>
                                  <td valign="top" style="width:10px;padding:2px;">
                                    <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                  </td>
                                  <td valign="top" style="padding:2px">
                                    <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                  </td>
                                </tr>
                              </table>
                            </div>
                          @else
                            <div style="padding-left:15px">
                              <table>
                                <tr>
                                  <td valign="top" style="width:10px;padding:2px;">
                                    <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                  </td>
                                  <td valign="top" style="padding:2px;">
                                    <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                  </td>
                                </tr>
                              </table>
                            </div>
                          @endif
                        @endforeach
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
              <br>
              <table>
                <tr>
                  <td>
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</span> <span class="spinner"></span></button>
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