@extends('layouts.master')
@section('title', trans('all.facesample'))
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
                  toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    });
  @endif
  
  $(function(){
      window.modalFacesample=(function(id){
          $("#showmodalfacesample").attr("href", "");
          $("#showmodalfacesample").attr("href", "{{ url('datainduk/pegawai/facesample/all') }}/"+id);
          $('#showmodalfacesample').trigger('click');
          return false;
      });
      
      $('body').on('hidden.bs.modal', '.modalfacesample', function () {
          $(this).removeData('bs.modal');
          $("#" + $(this).attr("id") + " .modal-content").empty();
          $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
      });
  });
      
  function loadmore(startfrom){
      $('#facesampleloadmore').attr( 'data-loading', '' );
      $('#facesampleloadmore').attr('disabled', 'disabled');
      $.ajax({
          type: "GET",
          url: "{{ url('facesample/loadmore') }}/"+startfrom,
          data: '',
          cache: false,
          success: function(html){
              $('#more').remove();
              $('#facesamplebox').append(html);
          }
      });
  }

  function hapussample(id){
      alertConfirm("{{ trans('all.apakahyakinakanmenghapusfacesampleini') }} ?",
          function(){
              window.location.href="{{ url('facesample/delete') }}/"+id+'/facesample';
          }
      );
  }
  </script>
  <style>
  td{
      padding:5px;
  }
      
  .body-modal{
      padding:10px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.facesample') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.facesample') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content row">
                    <form method="post" action="">
                        {{ csrf_field() }}
                        <input type="hidden" value="pencarian" name="submitdari">
                        <div class="col-md-8" style="margin-top:9px">
                            <button type="button" data-toggle="modal" data-target="#modalFilter" class="btn btn-primary"><i class="fa fa-bars"></i>&nbsp;&nbsp;{{ trans('all.filter') }}</button>&nbsp;&nbsp;
                            <button type="button" onclick="ke('{{ url('datainduk/pegawai/facesample/export/excel') }}')" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                            &nbsp;&nbsp;
                            {{--@if($filter != '')--}}
                                {{--<button type="button" onclick="return ke('{{ url('datainduk/pegawai/facesample/setulang') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>--}}
                            {{--@endif--}}
                        </div>
                        <div class="col-md-4">
                            <div class="search-form">
                                <div class="input-group">
                                    <input type="text" style="display:inline-block" autocomplete="off" name="pencarian" value="{{ Session::has('facesample_pencarian') ? Session::get('facesample_pencarian') : '' }}" placeholder="{{ trans('all.pencarian') }}" class="form-control">
                                    <div class="input-group-btn">
                                        <button style="margin-left:-5px;" type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @if($filter != '')
                        <div class="col-md-12">
                            <br>
                            {!! $filter !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div id="facesamplebox">
            @if(count($data) == 0)
                <div class="col-md-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <center>{{ trans('all.nodata') }}</center>
                        </div>
                    </div>
                </div>
            @else
                @foreach($data as $key)
                    <div class="col-md-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-content">
                                <center @if($key->totalfacesample > 0) onmouseover="hoverFoto('ya','delfoto{{ $key->idpegawai }}')" onmouseleave="hoverFoto('tidak','delfoto{{ $key->idpegawai }}')" @endif>
                                    <table>
                                        <tr>
                                            <td colspan="2" style="padding-bottom:15px"><center title="{!! $key->namalengkap !!}" style="max-width: 300px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><b>{!! $key->nama !!}</b></center></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namalengkap }}" data-gallery="">
                                                    <img width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$key->idpegawai) }}">
                                                </a>
                                                <center style="margin-top:10px;">{{ trans('all.fotoprofil') }}</center>
                                            </td>
                                            <td style="padding-left:25px">
                                                @if(strpos(Session::get('hakakses_perusahaan')->facesample, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->facesample, 'm') !== false)
                                                    @if($key->totalfacesample > 0)
                                                        <center><span id="delfoto{{ $key->idpegawai }}" style="position: absolute;margin-left:70px;display:none;" onclick="return hapussample({{ $key->idfacesample }})" title="{{ trans('all.hapus') }}"><i style="cursor: pointer;font-size:18px;" class="fa fa-trash"></i></span></center>
                                                    @endif
                                                @endif
                                                <a href="{{ url('getfacesample/'.$key->idfacesample) }}" title="{{ $key->namalengkap }}" data-gallery="">
                                                    @if($key->idfacesample != '')
                                                        <img width=120 style='border-radius: 50%;' height=120 src="{{ url('getfacesample/'.$key->idfacesample.'/_thumb') }}">
                                                    @else
                                                        <img width=120 style='border-radius: 50%;' height=120 src="{{ url('getfacesample') }}">
                                                    @endif
                                                </a>
                                                <center style="margin-top:10px;">{{ trans('all.facesample') }}</center>
                                            </td>
                                        </tr>
                                    </table>
                                </center>
                                <br>
                                @if($key->totalfacesample > 1)
                                    <span class="pull-right"><a href="#" onclick="return modalFacesample({{ $key->idpegawai }})">{{ trans('all.selengkapnya').' '.$key->totalfacesample }}</a></span>
                                @endif
                                <p></p>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if($totaldata > config('consts.LIMIT_FOTO'))
                    <div class="col-md-12" id="more">
                        <center style="padding-bottom: 15px;">
                            <button id="facesampleloadmore" type="button" class="ladda-button btn btn-primary slide-left" onclick="return loadmore('{{ $key->startfrom }}')"><span class="label2">{{ trans('all.muatselebihnya') }}</span> <span class="spinner"></span></button>
                        </center>
                    </div>
                @endif
            @endif
        </div>
    </div>
  </div>

  <!-- Modal facesample-->
  <a href="" id="showmodalfacesample" data-toggle="modal" data-target="#modalFacesample" style="display:none"></a>
  <div class="modal modalfacesample fade" id="modalFacesample" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-lg">
        
          <!-- Modal content-->
          <div class="modal-content">
              
          </div>
      </div>
  </div>
  <!-- Modal facesample-->

  <!-- Modal filter-->
  <div class="modal fade" id="modalFilter" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-lg">
        
          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
{{--                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.atribut') }}</h4>--}}
                  <h4 class="modal-title">{{ trans('all.filter') }}</h4>
              </div>
{{--              <form id="form_modalfilter" method="post" action="{{ url('datainduk/pegawai/facesample') }}" onsubmit="return validasi()">--}}
              <form id="form_modalfilter" method="post" action="">
                  <input type="hidden" value="filter" name="submitdari">
                  <div class="modal-body body-modal" style="max-height:460px;overflow: auto;">
                      {{ csrf_field() }}
                      <table id="tabAtribut">
                          <tr>
                              <td colspan="2">
                                  <table>
                                      <tr>
                                          <td>{{ trans('all.berdasarkan') }}</td>
                                          <td>
                                              <select class="form-control" name="jenisfilter">
                                                  <option value="">{{ trans('all.semua') }}</option>
                                                  <option value="punyafacesample" @if(Session::get('facesample_jenisfilter') == 'punyafacesample') selected @endif>{{ trans('all.sudahsampel') }}</option>
                                                  <option value="belumpunyafacesample" @if(Session::get('facesample_jenisfilter') == 'belumpunyafacesample') selected @endif>{{ trans('all.belumsampel') }}</option>
                                              </select>
                                          </td>
                                      </tr>
                                  </table>
                              </td>
                          </tr>
                      </table>
                      @if(count($dataatribut) > 0)
                          <p><b>{{ trans('all.atribut') }}</b></p>
                      @endif
                      @foreach($dataatribut as $key)
                          @if(count($key->atributnilai) > 0)
                              <div class="col-md-4">
                                  <input type="checkbox" id="semuaatribut_{{ $key->id }}" onclick="checkboxallclick('semuaatribut_{{ $key->id }}','attr_{{ $key->id }}')">&nbsp;&nbsp;
                                  <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $key->id }}','attr_{{ $key->id }}')">{{ $key->atribut }}</span>
                                  <br>
                                  @foreach($key->atributnilai as $keynilai)
                                      @if(Session::has('facesample_atributfilter'))
                                          {{ $checked = false }}
                                          @for($i=0;$i<count(Session::get('facesample_atributfilter'));$i++)
                                              @if($keynilai->id == Session::get('facesample_atributfilter')[$i])
                                                  <span style="display:none">{{ $checked = true }}</span>
                                              @endif
                                          @endfor
                                          @if($checked == true)
                                              <script>
                                                  $(function(){
                                                      checkAllAttr('attr_{{ $key->id }}','semuaatribut_{{ $key->id }}');
                                                  });
                                              </script>
                                          @endif
                                          <div style="padding-left:15px">
                                              <table>
                                                  <tr>
                                                      <td valign="top" style="width:10px;">
                                                          <input type="checkbox" class="attr_{{ $key->id }}" onchange="checkAllAttr('attr_{{ $key->id }}','semuaatribut_{{ $key->id }}')" id="atributnilai_{{ $keynilai->id }}" @if($checked == true) checked @endif name="atributfilter[]" value="{{ $keynilai->id }}">
                                                      </td>
                                                      <td valign="top">
                                                          <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $keynilai->id }}')">{{ $keynilai->nilai }}</span>
                                                      </td>
                                                  </tr>
                                              </table>
                                          </div>
                                      @else
                                          <div style="padding-left:15px">
                                              <table>
                                                  <tr>
                                                      <td valign="top" style="width:10px;">
                                                          <input type="checkbox" class="attr_{{ $key->id }}" onchange="checkAllAttr('attr_{{ $key->id }}','semuaatribut_{{ $key->id }}')" id="atributnilai_{{ $keynilai->id }}" name="atributfilter[]" value="{{ $keynilai->id }}">
                                                      </td>
                                                      <td valign="top">
                                                          <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $keynilai->id }}')">{{ $keynilai->nilai }}</span>
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
                  <div class="modal-footer">
                      <table width="100%">
                          <tr>
                              <td style="padding:0px;align:right">
                                  <button class="btn btn-primary" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.terapkan') }}</button>
                                  <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                              </td>
                          </tr>
                      </table>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- Modal filter-->

  <!-- Modal pegawai-->
  <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
  <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal pegawai-->
    
    <script>
        window.detailpegawai=(function(idpegawai){
            $("#showmodalpegawai").attr("href", "");
            $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
            $('#showmodalpegawai').trigger('click');
            return false;
        });

        $('body').on('hidden.bs.modal', '.modalpegawai', function () {
            $(this).removeData('bs.modal');
            $("#" + $(this).attr("id") + " .modal-content").empty();
            $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
        });
    </script>

@stop